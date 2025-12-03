<?php

namespace App\Http\Controllers;

use App\Models\BorrowCart;
use App\Models\BorrowCartItem;
use App\Models\Book;
use App\Models\Reader;
use App\Models\Inventory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BorrowCartController extends Controller
{
    /**
     * Tính phí thuê và tiền cọc cho một item
     */
    private function calculateItemFees($book, $borrowDays)
    {
        $reader = auth()->user()->reader ?? null;
        $hasCard = $reader ? true : false;
        
        // Lấy inventory thực tế để tính chính xác
        $inventory = Inventory::where('book_id', $book->id)
            ->where('status', 'Co san')
            ->first();
        
        if (!$inventory) {
            // Nếu không có inventory, tạo sample với condition mặc định
            $inventory = new Inventory();
            $inventory->condition = 'Trung binh';
            $inventory->status = 'Co san';
        }
        
        $ngayMuon = now();
        $ngayHenTra = now()->addDays($borrowDays);
        
        return \App\Services\PricingService::calculateFees(
            $book,
            $inventory,
            $ngayMuon,
            $ngayHenTra,
            $hasCard
        );
    }

    /**
     * Lấy hoặc tạo giỏ sách cho user
     */
    private function getOrCreateCart()
    {
        $user = auth()->user();
        if (!$user) {
            return null;
        }

        $cart = BorrowCart::where('user_id', $user->id)->first();
        
        if (!$cart) {
            $reader = Reader::where('user_id', $user->id)->first();
            $cart = BorrowCart::create([
                'user_id' => $user->id,
                'reader_id' => $reader?->id,
                'total_items' => 0,
            ]);
        }

        return $cart;
    }

    /**
     * Hiển thị giỏ sách
     */
    public function index()
    {
        if (!auth()->check()) {
            return redirect()->route('login')->with('error', 'Vui lòng đăng nhập để xem giỏ sách');
        }

        $cart = $this->getOrCreateCart();
        
        if (!$cart) {
            $cart = new BorrowCart();
            $cart->items = collect();
        } else {
            $cart->load(['items.book', 'items.book.category']);
        }

        return view('borrow-cart.index', compact('cart'));
    }

    /**
     * Thêm sách vào giỏ sách
     */
    public function add(Request $request)
    {
        $request->validate([
            'book_id' => 'required|exists:books,id',
            'quantity' => 'nullable|integer|min:1|max:10',
            'borrow_days' => 'nullable|integer|min:1|max:30',
            'distance' => 'nullable|numeric|min:0',
            'note' => 'nullable|string|max:1000',
        ]);

        if (!auth()->check()) {
            return response()->json([
                'success' => false,
                'message' => 'Vui lòng đăng nhập để thêm sách vào giỏ sách'
            ], 401);
        }

        $book = Book::findOrFail($request->book_id);
        
        // Kiểm tra số lượng có sẵn
        $availableCopies = Inventory::where('book_id', $book->id)
            ->where('status', 'Co san')
            ->count();
        
        $quantity = (int) ($request->input('quantity', 1));
        $borrowDays = (int) ($request->input('borrow_days', 14));
        $distance = floatval($request->input('distance', 0));
        $note = $request->input('note', '');

        if ($quantity > $availableCopies) {
            return response()->json([
                'success' => false,
                'message' => "Chỉ còn {$availableCopies} cuốn sách có sẵn. Vui lòng chọn lại số lượng."
            ], 400);
        }

        try {
            $cart = $this->getOrCreateCart();
            
            // Tính phí thuê và tiền cọc
            $fees = $this->calculateItemFees($book, $borrowDays);
            
            // Kiểm tra xem sách đã có trong giỏ sách chưa
            $existingItem = $cart->items()->where('book_id', $book->id)->first();
            
            if ($existingItem) {
                // Cập nhật số lượng nếu đã có
                $newQuantity = $existingItem->quantity + $quantity;
                if ($newQuantity > $availableCopies) {
                    return response()->json([
                        'success' => false,
                        'message' => "Số lượng tối đa có thể mượn là {$availableCopies} cuốn."
                    ], 400);
                }
                $existingItem->update([
                    'quantity' => $newQuantity,
                    'borrow_days' => $borrowDays,
                    'distance' => $distance,
                    'note' => $note ?: $existingItem->note,
                    'tien_coc' => $fees['tien_coc'],
                    'tien_thue' => $fees['tien_thue'],
                ]);
                $item = $existingItem;
            } else {
                // Tạo mới item
                $item = $cart->items()->create([
                    'book_id' => $book->id,
                    'quantity' => $quantity,
                    'borrow_days' => $borrowDays,
                    'distance' => $distance,
                    'note' => $note,
                    'tien_coc' => $fees['tien_coc'],
                    'tien_thue' => $fees['tien_thue'],
                ]);
            }

            // Cập nhật tổng số items
            $cart->update(['total_items' => $cart->getTotalItemsAttribute()]);

            return response()->json([
                'success' => true,
                'message' => 'Đã thêm sách vào giỏ sách',
                'cart_count' => $cart->getTotalItemsAttribute(),
                'data' => [
                    'item_id' => $item->id,
                    'book_id' => $book->id,
                    'quantity' => $item->quantity,
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error adding to borrow cart', [
                'message' => $e->getMessage(),
                'book_id' => $book->id,
                'user_id' => auth()->id(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi thêm sách vào giỏ sách: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Cập nhật item trong giỏ sách
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'quantity' => 'nullable|integer|min:1|max:10',
            'borrow_days' => 'nullable|integer|min:1|max:30',
            'distance' => 'nullable|numeric|min:0',
            'note' => 'nullable|string|max:1000',
            'is_selected' => 'nullable|boolean',
        ]);

        if (!auth()->check()) {
            return response()->json([
                'success' => false,
                'message' => 'Vui lòng đăng nhập'
            ], 401);
        }

        $item = BorrowCartItem::whereHas('cart', function($query) {
            $query->where('user_id', auth()->id());
        })->findOrFail($id);

        $book = $item->book;
        $availableCopies = Inventory::where('book_id', $book->id)
            ->where('status', 'Co san')
            ->count();

        $needRecalculateFees = false;
        
        if ($request->has('quantity')) {
            $quantity = (int) $request->input('quantity');
            if ($quantity > $availableCopies) {
                return response()->json([
                    'success' => false,
                    'message' => "Chỉ còn {$availableCopies} cuốn sách có sẵn."
                ], 400);
            }
            $item->quantity = $quantity;
        }

        if ($request->has('borrow_days')) {
            $item->borrow_days = (int) $request->input('borrow_days');
            $needRecalculateFees = true;
        }

        if ($request->has('distance')) {
            $item->distance = floatval($request->input('distance'));
        }

        if ($request->has('note')) {
            $item->note = $request->input('note');
        }

        if ($request->has('is_selected')) {
            $item->is_selected = $request->input('is_selected');
        }

        // Tính lại phí nếu borrow_days thay đổi
        if ($needRecalculateFees) {
            $fees = $this->calculateItemFees($book, $item->borrow_days);
            $item->tien_coc = $fees['tien_coc'];
            $item->tien_thue = $fees['tien_thue'];
        }

        $item->save();

        // Cập nhật tổng số items
        $cart = $item->cart;
        $cart->update(['total_items' => $cart->getTotalItemsAttribute()]);

        return response()->json([
            'success' => true,
            'message' => 'Đã cập nhật giỏ sách',
            'cart_count' => $cart->getTotalItemsAttribute(),
        ]);
    }

    /**
     * Xóa item khỏi giỏ sách
     */
    public function remove($id)
    {
        if (!auth()->check()) {
            return response()->json([
                'success' => false,
                'message' => 'Vui lòng đăng nhập'
            ], 401);
        }

        $item = BorrowCartItem::whereHas('cart', function($query) {
            $query->where('user_id', auth()->id());
        })->findOrFail($id);

        $cart = $item->cart;
        $item->delete();

        // Cập nhật tổng số items
        $cart->update(['total_items' => $cart->getTotalItemsAttribute()]);

        return response()->json([
            'success' => true,
            'message' => 'Đã xóa sách khỏi giỏ sách',
            'cart_count' => $cart->getTotalItemsAttribute(),
        ]);
    }

    /**
     * Xóa toàn bộ giỏ sách
     */
    public function clear()
    {
        if (!auth()->check()) {
            return response()->json([
                'success' => false,
                'message' => 'Vui lòng đăng nhập'
            ], 401);
        }

        $cart = $this->getOrCreateCart();
        if ($cart) {
            $cart->items()->delete();
            $cart->update(['total_items' => 0]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Đã xóa toàn bộ giỏ sách',
        ]);
    }

    /**
     * Lấy số lượng items trong giỏ sách (API)
     */
    public function count()
    {
        if (!auth()->check()) {
            return response()->json(['count' => 0]);
        }

        $cart = $this->getOrCreateCart();
        return response()->json([
            'count' => $cart ? $cart->getTotalItemsAttribute() : 0
        ]);
    }

    /**
     * Hiển thị trang checkout cho mượn sách
     */
    public function showCheckout()
    {
        if (!auth()->check()) {
            return redirect()->route('login')->with('error', 'Vui lòng đăng nhập để đặt mượn sách');
        }

        $reader = Reader::where('user_id', auth()->id())->first();
        if (!$reader) {
            return redirect()->route('register.reader.form')
                ->with('error', 'Bạn chưa có thẻ độc giả. Vui lòng đăng ký thẻ độc giả trước khi mượn sách.');
        }

        if ($reader->trang_thai !== 'Hoat dong') {
            return redirect()->back()->with('error', 'Thẻ độc giả của bạn đã bị khóa hoặc tạm dừng. Vui lòng liên hệ thư viện.');
        }

        if ($reader->ngay_het_han < now()->toDateString()) {
            return redirect()->back()->with('error', 'Thẻ độc giả của bạn đã hết hạn. Vui lòng gia hạn thẻ.');
        }

        $cart = $this->getOrCreateCart();
        if (!$cart || $cart->items()->count() === 0) {
            return redirect()->route('borrow-cart.index')->with('error', 'Giỏ sách của bạn đang trống');
        }

        // Tự động chọn tất cả items nếu chưa có item nào được chọn
        $selectedCount = $cart->items()->where('is_selected', true)->count();
        if ($selectedCount === 0) {
            $cart->items()->update(['is_selected' => true]);
        }

        // Load items với book - CHỈ LẤY CÁC ITEM ĐÃ ĐƯỢC CHỌN
        $cart->load(['items' => function($query) {
            $query->where('is_selected', true);
        }, 'items.book']);

        // Kiểm tra có item nào được chọn không (sau khi auto-select)
        if ($cart->items->count() === 0) {
            return redirect()->route('borrow-cart.index')->with('error', 'Giỏ sách của bạn đang trống');
        }

        // Tính toán các khoản phí từ giá đã lưu trong cart items
        $totalTienCoc = 0;
        $totalTienThue = 0;
        $totalTienShip = 0;
        $shipFeeCalculated = false;

        foreach ($cart->items as $cartItem) {
            $quantity = $cartItem->quantity;
            $distance = $cartItem->distance;

            // Tính tiền ship (chỉ tính 1 lần cho đơn hàng)
            if (!$shipFeeCalculated && $distance > 5) {
                $extraKm = $distance - 5;
                $totalTienShip = (int) ($extraKm * 5000);
                $shipFeeCalculated = true;
            }

            // Sử dụng giá đã lưu trong database (giữ nguyên từ giỏ sách)
            $totalTienCoc += ($cartItem->tien_coc ?? 0) * $quantity;
            $totalTienThue += ($cartItem->tien_thue ?? 0) * $quantity;
        }

        $tongTien = $totalTienCoc + $totalTienThue + $totalTienShip;

        return view('borrow-cart.checkout', compact('cart', 'reader', 'totalTienCoc', 'totalTienThue', 'totalTienShip', 'tongTien'));
    }

    /**
     * Process Checkout - Tạo yêu cầu mượn từ giỏ sách
     */
    public function processCheckout(Request $request)
    {
        // Validate input
        $request->validate([
            'reader_name' => 'required|string|max:255',
            'reader_phone' => 'required|string|max:20',
            'reader_email' => 'required|email',
            'payment_method' => 'required|in:bank_transfer,vnpay,wallet,cod',
            'tinh_thanh' => 'nullable|string|max:100',
            'huyen' => 'nullable|string|max:100',
            'xa' => 'nullable|string|max:100',
            'so_nha' => 'nullable|string|max:100',
            'notes' => 'nullable|string|max:1000',
        ]);

        if (!auth()->check()) {
            return response()->json([
                'success' => false,
                'message' => 'Vui lòng đăng nhập để đặt mượn sách'
            ], 401);
        }

        $reader = Reader::where('user_id', auth()->id())->first();
        if (!$reader) {
            return response()->json([
                'success' => false,
                'message' => 'Bạn chưa có thẻ độc giả. Vui lòng đăng ký thẻ độc giả trước khi mượn sách.',
                'redirect' => route('register.reader.form')
            ], 400);
        }

        if ($reader->trang_thai !== 'Hoat dong') {
            return response()->json([
                'success' => false,
                'message' => 'Thẻ độc giả của bạn đã bị khóa hoặc tạm dừng. Vui lòng liên hệ thư viện.'
            ], 400);
        }

        if ($reader->ngay_het_han < now()->toDateString()) {
            return response()->json([
                'success' => false,
                'message' => 'Thẻ độc giả của bạn đã hết hạn. Vui lòng gia hạn thẻ.'
            ], 400);
        }

        $cart = $this->getOrCreateCart();
        if (!$cart || $cart->items()->count() === 0) {
            return response()->json([
                'success' => false,
                'message' => 'Giỏ sách của bạn đang trống'
            ], 400);
        }

        try {
            DB::beginTransaction();

            // CHỈ LẤY CÁC ITEM ĐÃ ĐƯỢC CHỌN
            $items = $cart->items()->where('is_selected', true)->with('book')->get();
            
            if ($items->count() === 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Vui lòng chọn ít nhất một cuốn sách để đặt mượn'
                ], 400);
            }
            $allBorrowItems = [];
            $totalTienCoc = 0;
            $totalTienThue = 0;
            $totalTienShip = 0;

            $ngayMuon = now()->toDateString();
            
            // Lấy thông tin thanh toán và địa chỉ từ form
            $paymentMethod = $request->input('payment_method');
            $tinhThanh = $request->input('tinh_thanh', '');
            $huyen = $request->input('huyen', '');
            $xa = $request->input('xa', '');
            $soNha = $request->input('so_nha', '');
            $notes = $request->input('notes', '');

            // Tạo một Borrow cho tất cả items
            $borrow = \App\Models\Borrow::create([
                'reader_id' => $reader->id,
                'ten_nguoi_muon' => $request->input('reader_name'),
                'so_dien_thoai' => $request->input('reader_phone'),
                'tinh_thanh' => $tinhThanh,
                'huyen' => $huyen,
                'xa' => $xa,
                'so_nha' => $soNha,
                'ngay_muon' => $ngayMuon,
                'trang_thai' => 'Dang muon',
                'tien_coc' => 0,
                'tien_thue' => 0,
                'tien_ship' => 0,
                'tong_tien' => 0,
                'ghi_chu' => $notes ?: 'Đặt mượn từ giỏ sách',
            ]);

            $hasCard = true;
            $shipFeeCalculated = false;

            foreach ($items as $cartItem) {
                $book = $cartItem->book;
                $quantity = $cartItem->quantity;
                $borrowDays = $cartItem->borrow_days;
                $distance = $cartItem->distance;
                $note = $cartItem->note;
                $ngayHenTra = now()->addDays($borrowDays)->toDateString();

                // Kiểm tra số lượng có sẵn
                $availableInventories = Inventory::where('book_id', $book->id)
                    ->where('status', 'Co san')
                    ->limit($quantity)
                    ->get();

                if ($availableInventories->count() < $quantity) {
                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'message' => "Sách '{$book->ten_sach}' chỉ còn {$availableInventories->count()} cuốn có sẵn. Vui lòng cập nhật giỏ sách."
                    ], 400);
                }

                // Tính tiền ship (chỉ tính 1 lần cho đơn hàng)
                $tienShip = 0;
                if (!$shipFeeCalculated && $distance > 5) {
                    $extraKm = $distance - 5;
                    $tienShip = (int) ($extraKm * 5000);
                    $totalTienShip = $tienShip;
                    $shipFeeCalculated = true;
                }

                // Tạo BorrowItem cho từng inventory
                foreach ($availableInventories as $index => $inventory) {
                    $fees = \App\Services\PricingService::calculateFees(
                        $book,
                        $inventory,
                        $ngayMuon,
                        $ngayHenTra,
                        $hasCard
                    );

                    $itemShipFee = ($index === 0 && !$shipFeeCalculated) ? $tienShip : 0;
                    if ($itemShipFee > 0) {
                        $shipFeeCalculated = true;
                    }

                    $borrowItem = \App\Models\BorrowItem::create([
                        'borrow_id' => $borrow->id,
                        'book_id' => $book->id,
                        'inventorie_id' => $inventory->id,
                        'ngay_muon' => $ngayMuon,
                        'ngay_hen_tra' => $ngayHenTra,
                        'trang_thai' => 'Cho duyet',
                        'tien_coc' => $fees['tien_coc'],
                        'tien_thue' => $fees['tien_thue'],
                        'tien_ship' => $itemShipFee,
                        'ghi_chu' => $note ?: "Yêu cầu mượn từ giỏ sách - {$quantity} cuốn - {$borrowDays} ngày" . ($distance > 0 ? " - Khoảng cách: {$distance}km" : ''),
                    ]);

                    $allBorrowItems[] = $borrowItem;
                    $totalTienCoc += $fees['tien_coc'];
                    $totalTienThue += $fees['tien_thue'];
                }
            }

            // Cập nhật tổng tiền của Borrow
            $borrow->update([
                'tien_coc' => $totalTienCoc,
                'tien_thue' => $totalTienThue,
                'tien_ship' => $totalTienShip,
                'tong_tien' => $totalTienCoc + $totalTienThue + $totalTienShip,
            ]);

            // Map payment_method từ form sang ENUM của database
            $paymentMethodEnum = ($paymentMethod === 'cod') ? 'offline' : 'online';
            
            // Tạo mã giao dịch unique
            $transactionCode = 'BRW' . $borrow->id . '_' . time();
            
            // Tạo bản ghi thanh toán
            $payment = \App\Models\BorrowPayment::create([
                'borrow_id' => $borrow->id,
                'amount' => $totalTienCoc + $totalTienThue + $totalTienShip,
                'payment_type' => 'deposit', // Thanh toán tiền cọc và phí
                'payment_method' => $paymentMethodEnum,
                'payment_status' => 'pending', // Chờ thanh toán
                'transaction_code' => $transactionCode,
                'note' => 'Thanh toán tiền cọc, phí thuê và phí vận chuyển - ' . $paymentMethod,
            ]);

            // Xóa chỉ những items đã được chọn và checkout
            $cart->items()->where('is_selected', true)->delete();
            $cart->update(['total_items' => $cart->getTotalItemsAttribute()]);

            DB::commit();

            // Ghi log
            \App\Services\AuditService::logBorrow($borrow, "Borrow request from cart created by {$reader->ho_ten}");

            Log::info('Borrow created from cart successfully', [
                'borrow_id' => $borrow->id,
                'borrow_item_ids' => collect($allBorrowItems)->pluck('id')->toArray(),
                'reader_id' => $reader->id,
                'total_items' => count($allBorrowItems),
            ]);

            // Nếu chọn VnPay, tạo URL thanh toán và redirect
            if ($paymentMethod === 'vnpay') {
                $vnpayService = app(\App\Services\VnPayService::class);
                
                $paymentData = [
                    'amount' => $totalTienCoc + $totalTienThue + $totalTienShip,
                    'order_info' => "Thanh toán phiếu mượn #{$borrow->id} - {$reader->ho_ten}",
                    'order_id' => $transactionCode,
                    'order_type' => 'billpayment'
                ];
                
                $paymentUrl = $vnpayService->createPaymentUrl($paymentData, $request);
                
                // Kiểm tra nếu là AJAX request thì trả JSON, không thì redirect trực tiếp
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json([
                        'success' => true,
                        'payment_required' => true,
                        'payment_url' => $paymentUrl,
                        'message' => 'Đang chuyển đến trang thanh toán VnPay...'
                    ]);
                }
                
                // Nếu không phải AJAX, redirect trực tiếp đến VNPay
                return redirect()->away($paymentUrl);
            }

            return response()->json([
                'success' => true,
                'message' => 'Đã tạo yêu cầu mượn sách thành công. Vui lòng chờ quản trị viên duyệt.',
                'data' => [
                    'borrow_id' => $borrow->id,
                    'borrow_item_ids' => collect($allBorrowItems)->pluck('id')->toArray(),
                    'total_items' => count($allBorrowItems),
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error checking out borrow cart', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => auth()->id(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi tạo yêu cầu mượn: ' . (config('app.debug') ? $e->getMessage() : 'Vui lòng thử lại sau')
            ], 500);
        }
    }
}
