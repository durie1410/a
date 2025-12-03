<?php

namespace App\Http\Controllers;

use App\Models\ShippingLog;
use App\Models\Borrow;
use App\Models\BorrowItem;
use App\Services\FileUploadService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ShippingLogController extends Controller
{
    /**
     * Hiển thị tất cả shipping logs
     */
 public function index()
{
$logs = ShippingLog::with(['borrow.reader', 'item.book'])
    ->orderBy('id', 'desc')
    ->paginate(20);


    return view('admin.shipping_logs.index', compact('logs'));
}

    /**
     * Hiển thị shipping logs theo 1 Borrow
     */
    public function showByBorrow($borrowId)
    {
        $borrow = Borrow::with([
            'items.book',
            'shippingLogs.item.book'
        ])->findOrFail($borrowId);

        return view('admin.shipping_logs.by_borrow', compact('borrow'));
    }

    /**
     * Hiển thị shipping logs theo 1 BorrowItem
     */
public function show($id)
{
    // load shipping log cùng với borrow -> reader, items -> book, payments
    $log = \App\Models\ShippingLog::with([
        'borrow.reader',
        'borrow.items.book',
        'borrow.payments'           // nếu Borrow có payments relation
    ])->findOrFail($id);

    return view('admin.shipping_logs.show', compact('log'));
}

    /**
     * Thêm Shipping Log mới
     */
    public function store(Request $request)
    {
        $request->validate([
            'borrow_id'      => 'required|exists:borrows,id',
            'borrow_item_id' => 'required|exists:borrow_items,id',
            'status'         => 'required|string',
            'shipper_note'   => 'nullable|string',
            'receiver_note'  => 'nullable|string',
            'proof_image'    => 'nullable|image|max:2048'
        ]);

        $data = $request->all();

        // Lưu ảnh minh chứng
        if ($request->hasFile('proof_image')) {
            try {
                $result = FileUploadService::uploadImage(
                    $request->file('proof_image'),
                    'shipping_proofs', // Directory name
                    [
                        'max_size' => 2048, // 2MB
                        'resize' => true,
                        'width' => 800,
                        'height' => 800,
                        'disk' => 'public',
                    ]
                );
                $data['proof_image'] = $result['path'];
            } catch (\Exception $e) {
                \Log::error('Upload proof image error:', ['message' => $e->getMessage()]);
                return redirect()->back()
                    ->withErrors(['proof_image' => $e->getMessage()])
                    ->withInput();
            }
        }

        ShippingLog::create($data);

        return back()->with('success', 'Đã thêm log giao hàng.');
    }

    /**
     * Cập nhật trạng thái giao hàng
     */
public function updateStatus(Request $request, $id)
{
    $log = ShippingLog::findOrFail($id);

    // Validate chung
    $request->validate([
        'status'        => 'nullable|string|in:cho_xu_ly,dang_giao,da_giao,khong_nhan,hoan_hang',
        'receiver_note' => 'nullable|string',
        'delivered_at'  => 'nullable|date',
        'proof_image'   => 'nullable|image|max:2048',
    ]);

    // Nếu trạng thái là 'da_giao', phải có file proof_image
    if ($request->status === 'da_giao' && !$request->hasFile('proof_image')) {
        return back()->withErrors(['proof_image' => 'Phải tải ảnh minh chứng trước khi đánh dấu đã giao.']);
    }

    // Nếu có file, lưu vào storage
    if ($request->hasFile('proof_image')) {
        try {
            // Xóa ảnh cũ nếu có
            if ($log->proof_image && Storage::disk('public')->exists($log->proof_image)) {
                FileUploadService::deleteFile($log->proof_image, 'public');
            }
            
            // Upload ảnh mới sử dụng FileUploadService
            $result = FileUploadService::uploadImage(
                $request->file('proof_image'),
                'shipping_proofs', // Directory name - không được rỗng
                [
                    'max_size' => 2048, // 2MB
                    'resize' => true,
                    'width' => 800,
                    'height' => 800,
                    'disk' => 'public',
                ]
            );
            $log->proof_image = $result['path'];
        } catch (\Exception $e) {
            \Log::error('Upload proof image error:', ['message' => $e->getMessage()]);
            return redirect()->back()
                ->withErrors(['proof_image' => $e->getMessage()])
                ->withInput();
        }
    }

    // Cập nhật các trường còn lại
    $log->status = $request->status;
    $log->receiver_note = $request->receiver_note;
    $log->delivered_at = $request->delivered_at;
    $log->save();

    // Nếu trạng thái là đã giao, cập nhật tất cả sách trong borrow_items
    if ($request->status === 'da_giao') {
        $borrow = $log->borrow; // giả sử ShippingLog có quan hệ borrow()
        if ($borrow) {
            $borrow->items()->update(['trang_thai' => 'Dang muon']);
        }
    }

    return back()->with('success', 'Cập nhật trạng thái thành công!');
}



    /**
     * Xoá log
     */
    public function destroy($id)
    {
        $log = ShippingLog::findOrFail($id);
        $log->delete();

        return back()->with('success', 'Đã xoá shipping log.');
    }
}
