@extends('layouts.admin')

@section('title', 'Chi tiết Đơn giao #' . $log->id)

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3><i class="bi bi-truck me-2"></i>Chi tiết Đơn giao #{{ $log->id }}</h3>


    </div>

    <div class="row gx-3 gy-3">
        <div class="col-md-6">
            <div class="card mb-3">
                <div class="card-header">Thông tin đơn giao</div>
                <div class="card-body">
                    <p><strong>Trạng thái:</strong>
@php
    $statusLabels = [
        'cho_xu_ly' => 'Chờ xử lý',
        'dang_giao' => 'Đang giao',
        'da_giao'  => 'Đã giao',
        'khong_nhan'=> 'Không nhận',
        'hoan_hang'=> 'Hoàn hàng',
    ];
@endphp

<span class="badge bg-info text-dark">
    {{ $statusLabels[$log->status] ?? $log->status }}
</span>
                    </p>
                    <p><strong>Shipper:</strong> {{ $log->shipper_name ?? '—' }} ({{ $log->shipper_phone ?? '—' }})</p>
                    <p><strong>Ghi chú Shipper:</strong><br>{{ $log->shipper_note ?? '—' }}</p>
                    <p><strong>Ghi chú Người nhận:</strong><br>{{ $log->receiver_note ?? '—' }}</p>
                    <p><strong>Thời gian cập nhật:</strong> {{ $log->updated_at->format('d/m/Y H:i') }}</p>

@if($log->proof_image && file_exists(storage_path('storage/' . $log->proof_image)))
    <div class="mt-2">
        <strong>Ảnh minh chứng:</strong><br>
         <img src="{{ asset('storage/' . $log->proof_image) }}" 
                     class="img-fluid rounded" alt="">
    </div>
@else
    <div class="mt-2 text-danger">
        Chưa có ảnh
    </div>
@endif

                </div>
            </div>

            <div class="card">
                <div class="card-header">Hành động</div>
                <div class="card-body">
                    {{-- Form cập nhật trạng thái --}}
                    <form action="{{ route('admin.shipping_logs.update_status', $log->id) }}" method="POST" class="mb-3" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-2">
                            <label class="form-label">Cập nhật trạng thái</label>
                            <select name="status" class="form-select">
                                <option value="cho_xu_ly" {{ $log->status === 'cho_xu_ly' ? 'selected' : '' }}>Chờ xử lý</option>
                                <option value="dang_giao" {{ $log->status === 'dang_giao' ? 'selected' : '' }}>Đang giao</option>
                                <option value="da_giao" {{ $log->status === 'da_giao' ? 'selected' : '' }}>Giao thành công</option>
                                <option value="khong_nhan" {{ $log->status === 'khong_nhan' ? 'selected' : '' }}>Không nhận</option>
                                <option value="hoan_hang" {{ $log->status === 'hoan_hang' ? 'selected' : '' }}>Hoàn hàng</option>
                            </select>
                        </div>

                        <div class="mb-2">
                            <label class="form-label">Ghi chú người nhận</label>
                            <textarea name="receiver_note" class="form-control" rows="2">{{ old('receiver_note', $log->receiver_note) }}</textarea>
                        </div>

                        <div class="mb-2">
                            <label class="form-label">Thời gian giao (nếu có)</label>
                            <input type="datetime-local" name="delivered_at" class="form-control"
                                   value="{{ optional($log->delivered_at)->format('Y-m-d\TH:i') ?? '' }}">
                        </div>
                         <div class="mb-2">
                            <label class="form-label">Tải ảnh minh chứng</label>
                            <input type="file" name="proof_image" accept="image/*" class="form-control">
                        </div>

                        <button class="btn btn-primary">Cập nhật</button>
                    </form>

    
                </div>
            </div>
        </div>

        {{-- Thông tin phiếu mượn và danh sách sách --}}
        <div class="col-md-6">
            <div class="card mb-3">
                <div class="card-header">Thông tin phiếu mượn (#{{ $log->borrow->id }})</div>
                <div class="card-body">
                    <p><strong>Người mượn:</strong> {{ $log->borrow->ten_nguoi_muon ?? ($log->borrow->reader->ho_ten ?? '—') }}</p>
                    <p><strong>SĐT:</strong> {{ $log->borrow->so_dien_thoai ?? ($log->borrow->reader->sdt ?? '—') }}</p>
                    <p><strong>Địa chỉ:</strong><br>
                        {{ $log->borrow->so_nha ?? '' }} {{ $log->borrow->diachi ?? '' }}<br>
                        {{ $log->borrow->xa ?? '' }} {{ $log->borrow->huyen ?? '' }} {{ $log->borrow->tinh ?? '' }}
                    </p>
                    <p><strong>Ngày mượn:</strong> {{ optional($log->borrow->ngay_muon)->format('d/m/Y') ?? '—' }}</p>
                    <p><strong>Tổng tiền:</strong> {{ number_format($log->borrow->tong_tien ?? 0) }} đ</p>
                </div>
            </div>

            <div class="card">
                <div class="card-header">Danh sách sách trong phiếu ({{ $log->borrow->items->count() }} cuốn)</div>
                <div class="card-body p-0">
                    <table class="table mb-0">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Sách</th>
                                <th>Ngày hẹn trả</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($log->borrow->items as $i => $item)
                                <tr>
                                    <td>{{ $i + 1 }}</td>
                                    <td>{{ $item->book->ten_sach ?? '—' }}</td>
                                    <td>{{ optional($item->ngay_hen_tra)->format('d/m/Y') ?? '—' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Thanh toán (nếu có) --}}
            <div class="card mt-3">
                <div class="card-header">Thanh toán</div>
                <div class="card-body">
                    @if($log->borrow->payments && $log->borrow->payments->count())
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Số tiền</th>
                                    <th>Phương thức</th>
                                    <th>Trạng thái</th>
                                    <th>Thời gian</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($log->borrow->payments as $p)
                                    <tr>
                                        <td>{{ $p->id }}</td>
                                        <td>{{ number_format($p->amount) }}</td>
                                        <td>{{ $p->method }}</td>
                                        <td>{{ $p->status }}</td>
                                        <td>{{ optional($p->created_at)->format('d/m/Y H:i') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <p>Chưa có bản ghi thanh toán.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
            <div>
            <a href="{{ route('admin.shipping_logs.index') }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Quay lại
            </a>
            
        </div>
</div>
@endsection
