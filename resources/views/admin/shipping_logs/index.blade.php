@extends('layouts.admin')

@section('title', 'Danh sách Shipping Logs')

@section('content')
<div class="container py-4">

    <h3 class="mb-3">
        <i class="bi bi-truck me-2"></i>Danh sách giao hàng
    </h3>

    <div class="card shadow-sm">
        <div class="card-body table-responsive">

            <table class="table table-bordered align-middle">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>Phiếu mượn</th>
                        <th>Địa chỉ giao</th>
                        <th>Số lượng & Tổng tiền</th>
                        <th>Trạng thái</th>
                        <th>Shipper</th>
                        <th>Ngày cập nhật</th>
                        <th></th>
                    </tr>
                </thead>

                <tbody>
                    @foreach($logs as $log)
                    <tr>
                        {{-- ID --}}
                        <td>{{ $log->id }}</td>

                        {{-- Phiếu mượn --}}
                        <td>#{{ $log->borrow->id ?? '—' }}</td>

                        {{-- Địa chỉ giao --}}
                        <td>
                            {{ $log->borrow->so_nha ?? '' }},
                            {{ $log->borrow->xa ?? '' }},
                            {{ $log->borrow->huyen ?? '' }},
                            {{ $log->borrow->tinh_thanh ?? '' }}
                        </td>

                        {{-- Sách --}}

                        {{-- Số lượng & Tổng tiền --}}
                        <td>
                            {{ $log->borrow->items->count() ?? 0 }} cuốn <br>
                            Tổng tiền: {{ number_format($log->borrow->tong_tien ?? 0) }} đ
                        </td>

                        {{-- Trạng thái (map sang tiếng Việt) --}}
                        <td>
                            @php
                                $statusMap = [
                                    'cho_xu_ly' => 'Chờ xử lý',
                                    'dang_giao' => 'Đang giao',
                                    'da_giao' => 'Đã giao',
                                    'khong_nhan' => 'Không nhận',
                                    'hoan_hang' => 'Hoàn hàng',
                                ];
                            @endphp
                            <span class="badge bg-info text-dark">
                                {{ $statusMap[$log->status] ?? $log->status }}
                            </span>
                        </td>

                        {{-- Shipper --}}
                        <td>{{ $log->shipper_name ?? '—' }}</td>

                        {{-- Ngày cập nhật --}}
                        <td>{{ $log->updated_at->format('d/m/Y H:i') }}</td>

                        {{-- Hành động --}}
                        <td class="text-end">
                            <a href="{{ route('admin.shipping_logs.show', $log->id) }}" class="btn btn-sm btn-primary">
                                <i class="bi bi-eye"></i> xem
                            </a>
                            <form action="{{ route('admin.shipping_logs.destroy', $log->id) }}" method="POST"
                                  onsubmit="return confirm('Xóa log này?')"
                                  style="display:inline-block;">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-sm btn-danger">
                                    <i class="bi bi-trash"></i> xoá
                                </button>
                            </form>
                        </td>

                    </tr>
                    @endforeach
                </tbody>

            </table>

            {{-- Phân trang --}}
            <div class="mt-3">
                {{ $logs->links() }}
            </div>

        </div>
    </div>

</div>
@endsection
