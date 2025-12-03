<?php

namespace App\Http\Controllers;

use App\Models\BorrowItem;
use App\Models\Book;
use App\Models\Fine;
use Carbon\Carbon;
use Illuminate\Http\Request;

class BorrowItemController extends Controller
{
    /**
     * Hiển thị form chỉnh sửa chi tiết mượn sách
     */
    public function edit($id)
    {
        $item = BorrowItem::with(['book', 'borrow.reader'])->findOrFail($id);
        $books = Book::all();

        return view('admin.borrowsitem.edit', compact('item', 'books'));
    }

    /**
     * Cập nhật chi tiết mượn sách
     */
public function update(Request $request, $id)
{
    $item = BorrowItem::findOrFail($id);

    // Validate dữ liệu, bỏ 'trang_thai' khỏi form để tránh ghi đè
    $validated = $request->except('trang_thai');
    $validated += $request->validate([
        'book_id' => 'required|exists:books,id',
        'ngay_muon' => 'required|date',
        'ngay_hen_tra' => 'required|date|after_or_equal:ngay_muon',
        'tien_coc' => 'required|numeric|min:0',
        'tien_thue' => 'nullable|numeric|min:0',
        'tien_ship' => 'required|numeric|min:0',
        'trang_thai_coc' => 'required|in:cho_xu_ly,da_thu,da_hoan,tru_vao_phat',
        'so_lan_gia_han' => 'nullable|integer|min:0',
        'ghi_chu' => 'nullable|string',
    ]);

    // 1️⃣ Cập nhật BorrowItem
    $item->update($validated);

    // 2️⃣ Tự động đánh dấu quá hạn
    if ($item->ngay_hen_tra && now()->diffInDays($item->ngay_hen_tra, false) < 0) {
        $item->trang_thai = 'Qua han';
        $item->save();
    }

    // 3️⃣ Cập nhật tổng thông tin phiếu mượn
    $borrow = $item->borrow;
    if ($borrow) {
        $borrow->tien_thue = $borrow->borrowItems()->sum('tien_thue');
        $borrow->tien_coc  = $borrow->borrowItems()->sum('tien_coc');
        $borrow->tien_ship = $borrow->borrowItems()->sum('tien_ship');
        $borrow->tong_tien = $borrow->tien_thue + $borrow->tien_coc + $borrow->tien_ship;

        // Cập nhật trạng thái phiếu dựa trên borrow_items
        $statuses = $borrow->borrowItems()->pluck('trang_thai')->toArray();
        if (in_array('Mat sach', $statuses)) {
            $borrow->trang_thai = 'Mat sach';
        } elseif (in_array('Qua han', $statuses)) {
            $borrow->trang_thai = 'Qua han';
        } elseif (in_array('Cho duyet', $statuses) || in_array('Chua nhan', $statuses) || in_array('Dang muon', $statuses)) {
            $borrow->trang_thai = 'chua_hoan_tat';
        } else {
            $borrow->trang_thai = 'Da tra';
        }

        $borrow->save();
    }

    return redirect()
        ->route('admin.borrowitems.show', $item->id)
        ->with('success', 'Cập nhật chi tiết mượn sách thành công.');
}


    /**
     * Hiển thị thông tin chi tiết 1 BorrowItem
     */
   public function show($id)
{
    $borrowItem = BorrowItem::with(['borrow.reader', 'book'])->findOrFail($id);

    $today = Carbon::today(); // giờ = 00:00:00
    $borrowDate = $borrowItem->ngay_muon->copy()->startOfDay();
    $dueDate = $borrowItem->ngay_hen_tra->copy()->startOfDay();

    // Số ngày còn lại (dương = còn hạn, 0 = hết hạn hôm nay, âm = quá hạn)
    $borrowItem->days_remaining = $dueDate->diffInDays($today, false) * -1;

    return view('admin.borrowsitem.show', compact('borrowItem', 'borrowDate', 'dueDate'));
}

public function approve($id)
{
    try {
        $item = \App\Models\BorrowItem::with('borrow', 'inventory')->findOrFail($id);

        if ($item->trang_thai !== 'Cho duyet') {
            return back()->with('error', 'Trạng thái không hợp lệ để duyệt. Trạng thái hiện tại: ' . $item->trang_thai);
        }

        // Sử dụng DB transaction để đảm bảo tính nhất quán
        \DB::beginTransaction();

        // Cập nhật trạng thái item bằng update() để đảm bảo lưu vào database
        $updated = $item->update([
            'trang_thai' => 'Chua nhan'
        ]);

        if (!$updated) {
            \DB::rollBack();
            return back()->with('error', 'Không thể cập nhật trạng thái item.');
        }

        // Reload lại item để đảm bảo có dữ liệu mới nhất
        $item->refresh();

        // Cập nhật trạng thái inventory
        if ($item->inventory) {
            $item->inventory->update([
                'status' => 'Dang muon'
            ]);
        }

        // Cập nhật trạng thái của Borrow dựa trên các items
        $borrow = $item->borrow;
        if ($borrow) {
            // Reload lại relationship để lấy dữ liệu mới nhất
            $borrow->refresh();
            $borrow->load('items');
            $statuses = $borrow->items->pluck('trang_thai')->toArray();
            
            // Xác định trạng thái của Borrow dựa trên tất cả items
            if (in_array('Mat sach', $statuses)) {
                $borrow->trang_thai = 'Mat sach';
            } elseif (in_array('Qua han', $statuses)) {
                $borrow->trang_thai = 'Qua han';
            } elseif (in_array('Cho duyet', $statuses) || in_array('Chua nhan', $statuses)) {
                // Nếu còn item chờ duyệt hoặc chưa nhận, giữ trạng thái chua_hoan_tat
                $borrow->trang_thai = 'chua_hoan_tat';
            } elseif (in_array('Dang muon', $statuses)) {
                // Nếu có ít nhất 1 item đang mượn (và không còn item chờ duyệt/chưa nhận), chuyển sang Dang muon
                $borrow->trang_thai = 'Dang muon';
            } else {
                // Tất cả items đã trả
                $borrow->trang_thai = 'Da tra';
            }
            $borrow->save();
        }

        \DB::commit();

        // Kiểm tra xem request đến từ trang nào để redirect đúng
        $referer = request()->header('referer');
        if ($referer && str_contains($referer, '/admin/borrows/') && str_contains($referer, '/edit')) {
            // Nếu đến từ trang edit, redirect về trang edit
            return redirect()->route('admin.borrows.edit', $borrow->id)
                ->with('success', 'Đã duyệt và chuyển sách sang trạng thái chưa nhận!');
        } else {
            // Nếu đến từ trang index hoặc trang khác, redirect về trang index
            return redirect()->route('admin.borrows.index')
                ->with('success', 'Đã duyệt và chuyển sách sang trạng thái chưa nhận!');
        }
    } catch (\Exception $e) {
        \DB::rollBack();
        \Log::error('Lỗi khi duyệt borrow item: ' . $e->getMessage(), [
            'item_id' => $id,
            'trace' => $e->getTraceAsString()
        ]);
        return back()->with('error', 'Có lỗi xảy ra khi duyệt: ' . $e->getMessage());
    }
}
public function changeStatus($id)
{
    try {
        $item = BorrowItem::with('borrow', 'inventory')->findOrFail($id);

        // Kiểm tra trạng thái hiện tại phải là 'Chua nhan' mới được chuyển
        if ($item->trang_thai !== 'Chua nhan') {
            return back()->with('error', 'Chỉ có thể chuyển từ trạng thái "Chưa nhận" sang "Đang mượn". Trạng thái hiện tại: ' . $item->trang_thai);
        }

        // Sử dụng DB transaction để đảm bảo tính nhất quán
        \DB::beginTransaction();

        // Chuyển trạng thái sang "Dang muon"
        $updated = $item->update([
            'trang_thai' => 'Dang muon',
        ]);

        if (!$updated) {
            \DB::rollBack();
            return back()->with('error', 'Không thể cập nhật trạng thái item.');
        }

        // Reload lại item để đảm bảo có dữ liệu mới nhất
        $item->refresh();

        // Cập nhật trạng thái inventory nếu có
        if ($item->inventory && $item->inventory->status !== 'Dang muon') {
            $item->inventory->update([
                'status' => 'Dang muon'
            ]);
        }

        // Cập nhật trạng thái của Borrow dựa trên các items
        $borrow = $item->borrow;
        if ($borrow) {
            // Reload lại relationship để lấy dữ liệu mới nhất
            $borrow->refresh();
            $borrow->load('items');
            $statuses = $borrow->items->pluck('trang_thai')->toArray();
            
            // Xác định trạng thái của Borrow dựa trên tất cả items
            if (in_array('Mat sach', $statuses)) {
                $borrow->trang_thai = 'Mat sach';
            } elseif (in_array('Qua han', $statuses)) {
                $borrow->trang_thai = 'Qua han';
            } elseif (in_array('Cho duyet', $statuses) || in_array('Chua nhan', $statuses)) {
                // Nếu còn item chờ duyệt hoặc chưa nhận, giữ trạng thái chua_hoan_tat
                $borrow->trang_thai = 'chua_hoan_tat';
            } elseif (in_array('Dang muon', $statuses)) {
                // Nếu có ít nhất 1 item đang mượn, chuyển sang Dang muon
                $borrow->trang_thai = 'Dang muon';
            } else {
                // Tất cả items đã trả
                $borrow->trang_thai = 'Da tra';
            }
            $borrow->save();
        }

        \DB::commit();

        return back()->with('success', 'Đã chuyển sang trạng thái "Đang mượn" thành công!');
    } catch (\Exception $e) {
        \DB::rollBack();
        \Log::error('Lỗi khi chuyển trạng thái borrow item: ' . $e->getMessage(), [
            'item_id' => $id,
            'trace' => $e->getTraceAsString()
        ]);
        return back()->with('error', 'Có lỗi xảy ra khi chuyển trạng thái: ' . $e->getMessage());
    }
}











public function markLost($id)
{
    $item = BorrowItem::findOrFail($id);
    $book = $item->book;
    $inventory = $item->inventory;

    $gia = $book->gia;
    $loai = $book->loai_sach;              // binh_thuong | quy | tham_khao
    $tinhTrang = $inventory->condition;    // Moi, Tot, Trung binh, Cu, Hong
    $tienCoc = $item->tien_coc;            // cọc đã thu lúc mượn

    /** ---- 1. TÍNH TIỀN PHẠT BAN ĐẦU ---- **/
    if ($loai === 'quy') {
        // Sách quý phạt 100%
        $phatGoc = $gia;
    } else {
        switch ($tinhTrang) {
            case 'Moi':
            case 'Tot':
                $phatGoc = round($gia * 0.8);
                break;

            case 'Trung binh':
            case 'Cu':
            case 'Hong':
                $phatGoc = round($gia * 0.7);
                break;

            default:
                $phatGoc = round($gia * 0.7);
        }
    }

    /** ---- 2. Cập nhật BorrowItem ---- **/
    $item->update([
        'trang_thai' => 'Mat sach',
        'tien_phat'  => $phatGoc
    ]);

    /** ---- 4. Cập nhật inventory ---- **/
    if ($inventory) {
        $inventory->update([
            'status' => 'Mat'
        ]);
    }

    /** ---- 5. Trừ số lượng sách ---- **/
    if ($book->so_luong > 0) {
        $book->decrement('so_luong');
    }

    /** ---- 6. Ghi vào bảng fines ---- **/
    Fine::create([
        'borrow_id'      => $item->borrow_id,
        'borrow_item_id' => $item->id,          // <-- thêm dòng này
        'reader_id'      => $item->borrow->reader_id,
        'amount'         => $phatGoc,
        'type'           => 'lost_book',
        'description'    => 'Mất sách: '.$book->ten_sach.', tình trạng: '.$tinhTrang,
        'status'         => 'pending',
        'due_date'       => now()->addDays(7),
        'created_by'     => auth()->id()
    ]);

    return back()->with(
        'success',
        "Báo mất sách thành công! Phạt: " . number_format($phatGoc) . "đ"
    );
}









public function reportDamage($id)
{
    $item = BorrowItem::with(['book', 'inventory', 'borrow'])->findOrFail($id);

    $book = $item->book;
    $inventory = $item->inventory;

    $gia = $book->gia;
    $loai = $book->loai_sach;           // bình_thuong | quy | tham_khao
    $tinhTrangKhiMuon = $inventory->condition;   // Moi | Tot | Trung binh | Cu | Hong
    $tienCoc = $item->tien_coc;

    /**
     * ----------------------------
     * 1. TÍNH TIỀN PHẠT
     * ----------------------------
     * Bạn yêu cầu:
     *  - Sách bình thường: 80% nếu mới/tốt, 70% nếu cũ
     *  - Sách quý: 100%
     *  - Sách tham khảo: dùng 80%
     */

    if ($loai === 'quy') {
        $phatGoc = $gia; // 100%
    } else {
        switch ($tinhTrangKhiMuon) {
            case 'Moi':
            case 'Tot':
                $phatGoc = round($gia * 0.8);
                break;

            case 'Trung binh':
            case 'Cu':
            case 'Hong':
                $phatGoc = round($gia * 0.7);
                break;

            default:
                $phatGoc = round($gia * 0.7);
        }
    }


    /**
     * ----------------------------
     * 3. CẬP NHẬT borrow_items
     * ----------------------------
     */
    $item->update([
        'trang_thai' => 'Hong',
        'tien_phat' => $phatGoc,
    ]);

    /**
     * ----------------------------
     * 4. CẬP NHẬT inventory
     * ----------------------------
     */
    if ($inventory) {
        $inventory->update([
            'status' => 'Hong',
        ]);
    }

    /**
     * ----------------------------
     * 5. LƯU VÀO fines
     * ----------------------------
     */
    Fine::create([
        'borrow_id'      => $item->borrow_id,
        'borrow_item_id' => $item->id,
        'reader_id'      => $item->borrow->reader_id,
        'amount'         => $phatGoc,
        'type'           => 'damaged_book',
        'description'    => 'Sách hỏng: '.$book->ten_sach.', tình trạng khi mượn: '.$tinhTrangKhiMuon,
        'status'         => 'pending',
        'due_date'       => now()->addDays(7),
        'created_by'     => auth()->id(),
    ]);

    return back()->with(
        'success',
        "Đã báo hỏng sách! Tiền phạt: " . number_format($phatGoc) . "đ"
    );
}


}
