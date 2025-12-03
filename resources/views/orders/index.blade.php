<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <title>Lịch sử mua hàng - Nhà Xuất Bản Xây Dựng</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <link rel="stylesheet" href="{{ asset('css/account.css') }}">
    <style>
        /* Styles cho bảng lịch sử mua hàng */
        .purchase-history-section {
            background-color: #fff;
            padding: 30px;
            border-radius: 4px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }

        .purchase-history-title {
            font-size: 24px;
            font-weight: 600;
            color: #333;
            margin-bottom: 25px;
        }

        .purchase-history-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        .purchase-history-table thead {
            background-color: #f5f5f5;
        }

        .purchase-history-table th {
            padding: 12px 15px;
            text-align: left;
            font-weight: 600;
            color: #333;
            border-bottom: 2px solid #ddd;
            font-size: 14px;
        }

        .purchase-history-table td {
            padding: 15px;
            border-bottom: 1px solid #eee;
            font-size: 14px;
            color: #555;
        }

        .purchase-history-table tbody tr:hover {
            background-color: #f9f9f9;
        }

        .order-code {
            font-weight: 600;
            color: #333;
        }

        .order-date {
            color: #666;
        }

        .order-amount {
            font-weight: 600;
            color: #d82329;
        }

        .status-buttons {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }

        .status-btn {
            padding: 6px 12px;
            border: none;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 500;
            cursor: default;
        }

        .status-btn.cancelled {
            background-color: #dc3545;
            color: #fff;
        }

        .status-btn.unpaid {
            background-color: #6c757d;
            color: #fff;
        }

        .status-btn.paid {
            background-color: #28a745;
            color: #fff;
        }

        .status-btn.processing {
            background-color: #17a2b8;
            color: #fff;
        }

        .view-btn {
            background-color: #28a745;
            color: #fff;
            padding: 8px 15px;
            border: none;
            border-radius: 4px;
            font-size: 14px;
            font-weight: 500;
            text-decoration: none;
            display: inline-block;
            transition: background-color 0.2s;
        }

        .view-btn:hover {
            background-color: #218838;
            color: #fff;
            text-decoration: none;
        }

        .cancel-btn {
            background-color: #dc3545;
            color: #fff;
            padding: 8px 15px;
            border: none;
            border-radius: 4px;
            font-size: 14px;
            font-weight: 500;
            text-decoration: none;
            display: inline-block;
            transition: background-color 0.2s;
            cursor: pointer;
            margin-left: 5px;
        }

        .cancel-btn:hover {
            background-color: #c82333;
            color: #fff;
            text-decoration: none;
        }

        .action-buttons {
            display: flex;
            gap: 8px;
            align-items: center;
            flex-wrap: wrap;
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
        }

        .empty-icon {
            font-size: 80px;
            margin-bottom: 20px;
        }

        .empty-state h4 {
            font-size: 20px;
            color: #333;
            margin-bottom: 10px;
        }

        .empty-state p {
            font-size: 14px;
            color: #777;
            margin-bottom: 30px;
        }

        .pagination-wrapper {
            margin-top: 30px;
            display: flex;
            justify-content: center;
        }
    </style>
</head>
<body>
    <header class="main-header">
        <div class="header-top">
            <div class="logo-section">
                <img src="{{ asset('favicon.ico') }}" alt="Logo" class="logo-img">
                <div class="logo-text">
                    <span class="logo-part1">NHÀ XUẤT BẢN</span>
                    <span class="logo-part2">XÂY DỰNG</span>
                </div>
            </div>
            <div class="hotline-section">
                <div class="hotline-item">
                    <span class="hotline-label">Hotline khách lẻ:</span>
                    <a href="tel:0327888669" class="hotline-number">0327888669</a>
                </div>
                <div class="hotline-item">
                    <span class="hotline-label">Hotline khách sỉ:</span>
                    <a href="tel:02439741791" class="hotline-number">02439741791 - 0327888669</a>
                </div>
            </div>
            <div class="user-actions">
                @auth
                    <div class="user-menu-dropdown" style="position: relative;">
                        <a href="#" class="auth-link user-menu-toggle">
                            <span class="user-icon">👤</span>
                            <span>{{ auth()->user()->name }}</span>
                        </a>
                        <div class="user-dropdown-menu">
                            <div class="dropdown-header" style="padding: 12px 15px; border-bottom: 1px solid #eee; font-weight: 600; color: #333;">
                                <span class="user-icon">👤</span>
                                {{ auth()->user()->name }}
                            </div>
                            @if(auth()->user()->reader)
                            <a href="{{ route('account.borrowed-books') }}" class="dropdown-item">
                                <span>📚</span> Sách đang mượn
                            </a>
                            <a href="{{ route('account.reader-info') }}" class="dropdown-item">
                                <span>👥</span> Thông tin độc giả
                            </a>
                            @endif
                            <a href="{{ route('account') }}" class="dropdown-item">
                                <span>👤</span> Thông tin tài khoản
                            </a>
                            <a href="{{ route('account.change-password') }}" class="dropdown-item">
                                <span>🔒</span> Đổi mật khẩu
                            </a>
                            <a href="{{ route('orders.index') }}" class="dropdown-item">
                                <span>⏰</span> Lịch sử mua hàng
                            </a>
                            @if(auth()->user()->role === 'admin' || auth()->user()->role === 'staff')
                            <div style="border-top: 1px solid #eee; margin-top: 5px;"></div>
                            <a href="{{ route('dashboard') }}" class="dropdown-item">
                                <span>📊</span> Dashboard
                            </a>
                            @endif
                            <form action="{{ route('logout') }}" method="POST" style="margin: 0;">
                                @csrf
                                <button type="submit" class="dropdown-item logout-btn">
                                    <span>➡️</span> Đăng xuất
                                </button>
                            </form>
                        </div>
                    </div>
                    <style>
                        .user-menu-dropdown {
                            position: relative;
                        }
                        .user-menu-dropdown .user-dropdown-menu {
                            display: none;
                            position: absolute;
                            top: calc(100% + 5px);
                            right: 0;
                            background: white;
                            border: 1px solid #ddd;
                            border-radius: 8px;
                            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
                            min-width: 220px;
                            z-index: 1000;
                            overflow: hidden;
                        }
                        .user-menu-dropdown:hover .user-dropdown-menu {
                            display: block;
                        }
                        .user-menu-dropdown .dropdown-item {
                            display: block;
                            padding: 10px 15px;
                            color: #333;
                            text-decoration: none;
                            border-bottom: 1px solid #eee;
                            transition: background-color 0.2s;
                            cursor: pointer;
                        }
                        .user-menu-dropdown .dropdown-item:hover {
                            background-color: #f5f5f5;
                        }
                        .user-menu-dropdown .dropdown-item.logout-btn {
                            border: none;
                            background: none;
                            width: 100%;
                            text-align: left;
                            color: #d32f2f;
                            border-top: 1px solid #eee;
                            margin-top: 5px;
                        }
                        .user-menu-dropdown .dropdown-item.logout-btn:hover {
                            background-color: #ffebee;
                        }
                        .user-menu-dropdown .dropdown-item span {
                            margin-right: 8px;
                        }
                    </style>
                @else
                    <a href="{{ route('login') }}" class="auth-link">Đăng nhập</a>
                @endauth
            </div>
        </div>
        <div class="header-nav">
            <div class="search-bar">
                <form action="{{ route('books.public') }}" method="GET" class="search-form">
                    <input type="text" name="keyword" placeholder="Tìm sách, tác giả, sản phẩm mong muốn..." value="{{ request('keyword') }}" class="search-input">
                    <button type="submit" class="search-button">🔍 Tìm kiếm</button>
                </form>
            </div>
        </div>
    </header>

    <!-- Breadcrumb Navigation -->
    <nav class="breadcrumb-nav">
        <div class="breadcrumb-container">
            <a href="{{ route('home') }}" class="breadcrumb-link">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
                    <polyline points="9 22 9 12 15 12 15 22"></polyline>
                </svg>
            </a>
            <span class="breadcrumb-separator">/</span>
            <span class="breadcrumb-current">Lịch sử mua hàng</span>
        </div>
    </nav>

    <main class="account-container">
        <aside class="account-sidebar">
            <div class="user-profile">
                <div class="user-avatar">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</div>
                <div class="username">{{ auth()->user()->name }}</div>
            </div>
            <nav class="account-nav">
                <ul>
                    @if(auth()->user()->reader)
                    <li><a href="{{ route('account.borrowed-books') }}"><span class="icon">📚</span> Sách đang mượn</a></li>
                    @endif
                    <li><a href="{{ route('account') }}"><span class="icon">👤</span> Thông tin khách hàng</a></li>
                    <li><a href="{{ route('account.change-password') }}"><span class="icon">🔒</span> Đổi mật khẩu</a></li>
                    <li class="active"><a href="{{ route('orders.index') }}"><span class="icon">🛒</span> Lịch sử mua hàng</a></li>
                    @if(!auth()->user()->reader)
                    <li><a href="{{ route('account.register-reader') }}"><span class="icon">📝</span> Đăng kí độc giả</a></li>
                    @endif
                    <li><a href="#" class="logout-link" onclick="event.preventDefault(); document.getElementById('logout-form').submit();"><span class="icon">➡️</span> Đăng xuất</a></li>
                </ul>
            </nav>
            <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                @csrf
            </form>
        </aside>

        <section class="account-content">
            <div class="purchase-history-section">
                <h2 class="purchase-history-title">Lịch sử mua hàng</h2>
                
                @if($orders->count() > 0)
                <table class="purchase-history-table">
                    <thead>
                        <tr>
                            <th>STT</th>
                            <th>Mã đơn</th>
                            <th>Ngày đặt</th>
                            <th>Số tiền</th>
                            <th>Phương thức thanh toán</th>
                            <th>Trạng thái</th>
                            <th>Xử lý</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($orders as $index => $order)
                        <tr>
                            <td>{{ $orders->firstItem() + $index }}</td>
                            <td>
                                <span class="order-code">#{{ $order->order_number }}</span>
                            </td>
                            <td>
                                <span class="order-date">{{ $order->created_at->format('d/m/Y H:i') }}</span>
                            </td>
                            <td>
                                <span class="order-amount">{{ number_format($order->total_amount, 0, ',', '.') }}₫</span>
                            </td>
                            <td>
                                @if($order->payment_method === 'cash_on_delivery')
                                    <span style="color: #28a745; font-weight: 500;">💳 Thanh toán khi nhận hàng</span>
                                @elseif($order->payment_method === 'bank_transfer')
                                    <span style="color: #17a2b8; font-weight: 500;">🏦 Chuyển khoản ngân hàng</span>
                                @else
                                    <span style="color: #6c757d;">Chưa xác định</span>
                                @endif
                            </td>
                            <td>
                                <div class="status-buttons">
                                    @if($order->status === 'cancelled')
                                        <span class="status-btn cancelled">Đã huỷ</span>
                                    @elseif($order->status === 'pending')
                                        <span class="status-btn" style="background-color: #ffc107; color: #000;">Chờ xử lý</span>
                                    @elseif($order->status === 'processing')
                                        <span class="status-btn processing">Đang xử lý</span>
                                    @elseif($order->status === 'shipped')
                                        <span class="status-btn" style="background-color: #17a2b8; color: #fff;">Đã giao hàng</span>
                                    @elseif($order->status === 'delivered')
                                        <span class="status-btn paid">Đã hoàn thành</span>
                                    @endif
                                    @if($order->payment_status === 'pending')
                                        <span class="status-btn unpaid">Chưa thanh toán</span>
                                    @elseif($order->payment_status === 'paid')
                                        <span class="status-btn paid">Đã thanh toán</span>
                                    @elseif($order->payment_status === 'failed')
                                        <span class="status-btn cancelled">Thanh toán thất bại</span>
                                    @elseif($order->payment_status === 'refunded')
                                        <span class="status-btn" style="background-color: #6c757d; color: #fff;">Đã hoàn tiền</span>
                                    @endif
                                </div>
                            </td>
                            <td>
                                <div class="action-buttons">
                                    <a href="{{ route('orders.show', $order->id) }}" class="view-btn">Xem</a>
                                    @if($order->canBeCancelled())
                                    <button type="button" class="cancel-btn" onclick="cancelOrder({{ $order->id }})">
                                        Huỷ
                                    </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>

                <!-- Phân trang -->
                @if($orders->hasPages())
                <div class="pagination-wrapper">
                    {{ $orders->links() }}
                </div>
                @endif

                @else
                <div class="empty-state">
                    <div class="empty-icon">🛒</div>
                    <h4>Bạn chưa có đơn hàng nào</h4>
                    <p>Hãy bắt đầu mua sắm để tạo đơn hàng đầu tiên của bạn!</p>
                    <a href="{{ route('books.public') }}" class="btn-primary">
                        Mua sắm ngay
                    </a>
                </div>
                @endif
            </div>
        </section>
    </main>

    @include('components.footer')

    <!-- Modal xác nhận hủy đơn hàng (Bước 1: Xác nhận) -->
    <div class="modal fade" id="confirmCancelModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" style="border: none; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.15);">
                <div class="modal-body text-center" style="padding: 40px 30px;">
                    <!-- Icon -->
                    <div style="width: 80px; height: 80px; margin: 0 auto 20px; background-color: #fff3cd; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                        <span style="font-size: 48px; color: #ffc107; font-weight: bold;">?</span>
                    </div>
                    
                    <!-- Title -->
                    <h4 class="modal-title mb-3" style="font-weight: 600; color: #333; font-size: 22px;">
                        Xác nhận hủy đơn hàng
                    </h4>
                    
                    <!-- Message -->
                    <p style="color: #666; font-size: 16px; margin-bottom: 30px;">
                        Vui lòng xác nhận để hủy đơn hàng
                    </p>
                    
                    <!-- Buttons -->
                    <div class="d-flex gap-3 justify-content-center">
                        <button type="button" class="btn btn-secondary" id="cancelConfirmBtn" style="padding: 10px 30px; border-radius: 8px; font-weight: 500;">
                            Huỷ
                        </button>
                        <button type="button" class="btn btn-primary" id="proceedCancelBtn" style="padding: 10px 30px; border-radius: 8px; font-weight: 500; background-color: #0d6efd;">
                            Xác nhận
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal nhập lý do hủy (Bước 2: Nhập lý do) -->
    <div class="modal fade" id="cancelOrderModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" style="border: none; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.15);">
                <div class="modal-header" style="border-bottom: 1px solid #e9ecef; padding: 20px 30px;">
                    <h5 class="modal-title" style="font-weight: 600; color: #333;">Nhập lý do hủy đơn hàng</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" style="padding: 30px;">
                    <div class="alert alert-warning mb-3">
                        <i class="fas fa-exclamation-triangle"></i>
                        <strong>Lưu ý:</strong> Hành động này không thể hoàn tác.
                    </div>
                    <div class="mb-3">
                        <label for="cancellation_reason" class="form-label">
                            <strong>Lý do hủy đơn hàng <span class="text-danger">*</span></strong>
                        </label>
                        <textarea 
                            class="form-control" 
                            id="cancellation_reason" 
                            name="cancellation_reason" 
                            rows="4" 
                            placeholder="Vui lòng nhập lý do hủy đơn hàng (tối thiểu 10 ký tự)"
                            required
                            minlength="10"
                            maxlength="500"
                            style="border-radius: 8px;"
                        ></textarea>
                        <div class="form-text">
                            <span id="charCount">0</span>/500 ký tự (tối thiểu 10 ký tự)
                        </div>
                        <div class="invalid-feedback" id="reasonError"></div>
                    </div>
                </div>
                <div class="modal-footer" style="border-top: 1px solid #e9ecef; padding: 20px 30px;">
                    <button type="button" class="btn btn-secondary" id="backToConfirmBtn" style="border-radius: 8px;">
                        <i class="fas fa-arrow-left"></i> Quay lại
                    </button>
                    <button type="button" class="btn btn-danger" id="confirmCancelBtn" style="border-radius: 8px;">
                        <i class="fas fa-times"></i> Xác nhận hủy
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Toast thông báo -->
    <div class="toast-container position-fixed bottom-0 end-0 p-3">
        <div id="orderToast" class="toast" role="alert">
            <div class="toast-header">
                <i class="fas fa-shopping-cart text-success me-2"></i>
                <strong class="me-auto">Thông báo</strong>
                <button type="button" class="btn-close" data-bs-dismiss="toast"></button>
            </div>
            <div class="toast-body" id="toastMessage">
                <!-- Nội dung thông báo sẽ được thêm vào đây -->
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    // Khai báo biến global
    let currentOrderId = null;
    let confirmCancelModal = null;
    let cancelOrderModal = null;
    let orderToast = null;

    // Hàm hủy đơn hàng - Hiển thị modal xác nhận đầu tiên
    window.cancelOrder = function(orderId) {
        console.log('=== cancelOrder function called ===');
        console.log('Order ID:', orderId);
        
        if (!orderId) {
            console.error('Order ID is required');
            alert('Lỗi: Không có mã đơn hàng');
            return false;
        }
        
        currentOrderId = orderId;
        
        // Tìm modal xác nhận
        const confirmModalElement = document.getElementById('confirmCancelModal');
        if (!confirmModalElement) {
            console.error('Confirm modal element not found in DOM');
            alert('Không thể tải form hủy đơn hàng. Vui lòng tải lại trang.');
            return false;
        }
        
        console.log('Confirm modal element found');
        
        // Khởi tạo modal xác nhận nếu chưa có
        if (!confirmCancelModal) {
            try {
                if (typeof bootstrap === 'undefined') {
                    console.error('Bootstrap is not loaded');
                    alert('Lỗi: Bootstrap chưa được tải. Vui lòng tải lại trang.');
                    return false;
                }
                
                confirmCancelModal = new bootstrap.Modal(confirmModalElement, {
                    backdrop: 'static',
                    keyboard: false
                });
                console.log('Confirm modal initialized successfully');
            } catch (error) {
                console.error('Error initializing confirm modal:', error);
                alert('Lỗi khởi tạo form: ' + error.message);
                return false;
            }
        }
        
        // Hiển thị modal xác nhận
        try {
            confirmCancelModal.show();
            console.log('Confirm modal shown successfully');
            return true;
        } catch (error) {
            console.error('Error showing confirm modal:', error);
            alert('Lỗi hiển thị form: ' + error.message);
            return false;
        }
    };

    // Khởi tạo tất cả sau khi DOM đã load
    document.addEventListener('DOMContentLoaded', function() {
        console.log('DOM loaded, initializing cancel order functionality...');
        
        // Khởi tạo modal xác nhận
        const confirmModalElement = document.getElementById('confirmCancelModal');
        if (confirmModalElement && typeof bootstrap !== 'undefined') {
            confirmCancelModal = new bootstrap.Modal(confirmModalElement, {
                backdrop: 'static',
                keyboard: false
            });
            console.log('Confirm modal initialized');
        }
        
        // Khởi tạo modal nhập lý do
        const modalElement = document.getElementById('cancelOrderModal');
        if (modalElement && typeof bootstrap !== 'undefined') {
            cancelOrderModal = new bootstrap.Modal(modalElement, {
                backdrop: 'static',
                keyboard: false
            });
            console.log('Cancel order modal initialized');
        }
        
        // Khởi tạo toast
        const toastElement = document.getElementById('orderToast');
        if (toastElement && typeof bootstrap !== 'undefined') {
            orderToast = new bootstrap.Toast(toastElement);
            console.log('Toast initialized');
        }
        
        // Xử lý nút "Huỷ" trong modal xác nhận
        const cancelConfirmBtn = document.getElementById('cancelConfirmBtn');
        if (cancelConfirmBtn && confirmCancelModal) {
            cancelConfirmBtn.addEventListener('click', function() {
                confirmCancelModal.hide();
            });
        }
        
        // Xử lý nút "Xác nhận" trong modal xác nhận - chuyển sang modal nhập lý do
        const proceedCancelBtn = document.getElementById('proceedCancelBtn');
        if (proceedCancelBtn && confirmCancelModal && cancelOrderModal) {
            proceedCancelBtn.addEventListener('click', function() {
                confirmCancelModal.hide();
                setTimeout(function() {
                    // Reset form trước khi hiển thị
                    const cancellationReasonInput = document.getElementById('cancellation_reason');
                    const charCountSpan = document.getElementById('charCount');
                    const reasonError = document.getElementById('reasonError');
                    if (cancellationReasonInput) {
                        cancellationReasonInput.value = '';
                        cancellationReasonInput.classList.remove('is-invalid');
                    }
                    if (charCountSpan) {
                        charCountSpan.textContent = '0';
                        charCountSpan.classList.remove('text-success', 'text-warning', 'text-danger');
                    }
                    if (reasonError) {
                        reasonError.textContent = '';
                    }
                    
                    cancelOrderModal.show();
                    
                    setTimeout(function() {
                        if (cancellationReasonInput) {
                            cancellationReasonInput.focus();
                        }
                    }, 100);
                }, 300);
            });
        }
        
        // Xử lý nút "Quay lại" trong modal nhập lý do
        const backToConfirmBtn = document.getElementById('backToConfirmBtn');
        if (backToConfirmBtn && cancelOrderModal && confirmCancelModal) {
            backToConfirmBtn.addEventListener('click', function() {
                cancelOrderModal.hide();
                const cancellationReasonInput = document.getElementById('cancellation_reason');
                const charCountSpan = document.getElementById('charCount');
                const reasonError = document.getElementById('reasonError');
                if (cancellationReasonInput) {
                    cancellationReasonInput.value = '';
                    cancellationReasonInput.classList.remove('is-invalid');
                }
                if (charCountSpan) {
                    charCountSpan.textContent = '0';
                    charCountSpan.classList.remove('text-success', 'text-warning', 'text-danger');
                }
                if (reasonError) {
                    reasonError.textContent = '';
                }
                setTimeout(function() {
                    confirmCancelModal.show();
                }, 300);
            });
        }
        
        // Đếm ký tự trong textarea lý do hủy
        const cancellationReasonInput = document.getElementById('cancellation_reason');
        const charCountSpan = document.getElementById('charCount');

        if (cancellationReasonInput && charCountSpan) {
            cancellationReasonInput.addEventListener('input', function() {
                const charCount = this.value.length;
                charCountSpan.textContent = charCount;
                
                if (charCount < 10) {
                    charCountSpan.classList.add('text-warning');
                    charCountSpan.classList.remove('text-success', 'text-danger');
                } else if (charCount > 500) {
                    charCountSpan.classList.add('text-danger');
                    charCountSpan.classList.remove('text-success', 'text-warning');
                } else {
                    charCountSpan.classList.add('text-success');
                    charCountSpan.classList.remove('text-warning', 'text-danger');
                }
            });
        }
        
        // Xác nhận hủy đơn hàng
        const confirmCancelBtn = document.getElementById('confirmCancelBtn');
        if (confirmCancelBtn) {
            confirmCancelBtn.addEventListener('click', function() {
                if (!currentOrderId) {
                    console.error('No order ID set');
                    return;
                }
                
                const cancellationReasonInput = document.getElementById('cancellation_reason');
                const cancellationReason = cancellationReasonInput ? cancellationReasonInput.value.trim() : '';
                const reasonError = document.getElementById('reasonError');
                
                // Validate lý do hủy
                if (!cancellationReason) {
                    if (reasonError && cancellationReasonInput) {
                        reasonError.textContent = 'Vui lòng nhập lý do hủy đơn hàng';
                        cancellationReasonInput.classList.add('is-invalid');
                    }
                    return;
                }
                
                if (cancellationReason.length < 10) {
                    if (reasonError && cancellationReasonInput) {
                        reasonError.textContent = 'Lý do hủy đơn hàng phải có ít nhất 10 ký tự';
                        cancellationReasonInput.classList.add('is-invalid');
                    }
                    return;
                }
                
                if (cancellationReason.length > 500) {
                    if (reasonError && cancellationReasonInput) {
                        reasonError.textContent = 'Lý do hủy đơn hàng không được vượt quá 500 ký tự';
                        cancellationReasonInput.classList.add('is-invalid');
                    }
                    return;
                }
                
                if (reasonError && cancellationReasonInput) {
                    reasonError.textContent = '';
                    cancellationReasonInput.classList.remove('is-invalid');
                }
                
                const button = this;
                const originalText = button.innerHTML;
                
                button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang xử lý...';
                button.disabled = true;
                
                // Lấy CSRF token
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
                
                if (!csrfToken) {
                    alert('Lỗi: Không tìm thấy CSRF token. Vui lòng tải lại trang.');
                    button.innerHTML = originalText;
                    button.disabled = false;
                    return;
                }
                
                console.log('Sending cancel request for order:', currentOrderId);
                
                fetch(`/orders/${currentOrderId}/cancel`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        cancellation_reason: cancellationReason
                    })
                })
                .then(response => {
                    console.log('Response status:', response.status);
                    
                    const contentType = response.headers.get('content-type') || '';
                    if (!contentType.includes('application/json')) {
                        return response.text().then(text => {
                            console.error('Non-JSON response:', text);
                            if (!response.ok) {
                                throw new Error(`Lỗi ${response.status}: ${text || 'Server error'}`);
                            }
                            throw new Error('Server trả về dữ liệu không đúng định dạng');
                        });
                    }
                    
                    return response.json().then(data => {
                        if (!response.ok) {
                            return Promise.reject({ ...data, status: response.status });
                        }
                        return data;
                    });
                })
                .then(data => {
                    console.log('Response data:', data);
                    
                    if (data.success) {
                        showToast('success', data.message || 'Đơn hàng đã được hủy thành công');
                        
                        if (cancelOrderModal) {
                            try {
                                cancelOrderModal.hide();
                            } catch (e) {
                                console.error('Error hiding modal:', e);
                                const modalElement = document.getElementById('cancelOrderModal');
                                if (modalElement) {
                                    modalElement.classList.remove('show');
                                    modalElement.style.display = 'none';
                                    document.body.classList.remove('modal-open');
                                    const backdrop = document.getElementById('modalBackdrop');
                                    if (backdrop) backdrop.remove();
                                }
                            }
                        }
                        
                        setTimeout(() => {
                            console.log('Reloading page...');
                            window.location.reload();
                        }, 1500);
                    } else {
                        const errorMessage = data.message || data.error || 'Có lỗi xảy ra khi hủy đơn hàng';
                        showToast('error', errorMessage);
                        
                        if (data.errors && data.errors.cancellation_reason) {
                            const reasonError = document.getElementById('reasonError');
                            const cancellationReasonInput = document.getElementById('cancellation_reason');
                            if (reasonError && cancellationReasonInput) {
                                reasonError.textContent = data.errors.cancellation_reason[0];
                                cancellationReasonInput.classList.add('is-invalid');
                            }
                        }
                    }
                })
                .catch(error => {
                    console.error('Error details:', error);
                    
                    if (error && typeof error === 'object' && 'success' in error) {
                        const errorMessage = error.message || 'Có lỗi xảy ra khi hủy đơn hàng';
                        showToast('error', errorMessage);
                        
                        if (error.errors && error.errors.cancellation_reason) {
                            const reasonError = document.getElementById('reasonError');
                            const cancellationReasonInput = document.getElementById('cancellation_reason');
                            if (reasonError && cancellationReasonInput) {
                                reasonError.textContent = error.errors.cancellation_reason[0];
                                cancellationReasonInput.classList.add('is-invalid');
                            }
                        }
                    } else {
                        let errorMessage = 'Có lỗi xảy ra, vui lòng thử lại';
                        if (error && error.message) {
                            errorMessage = error.message;
                        }
                        console.error('Error message:', errorMessage);
                        showToast('error', errorMessage);
                    }
                })
                .finally(() => {
                    button.innerHTML = originalText;
                    button.disabled = false;
                });
            });
        }
    });

    // Hàm hiển thị toast
    function showToast(type, message) {
        const toastElement = document.getElementById('orderToast');
        const toastMessage = document.getElementById('toastMessage');
        
        if (!toastElement || !toastMessage) {
            console.error('Toast elements not found');
            alert(message);
            return;
        }
        
        toastMessage.textContent = message;
        
        const toastHeader = toastElement.querySelector('.toast-header');
        const icon = toastHeader.querySelector('i');
        
        if (type === 'success') {
            icon.className = 'fas fa-check-circle text-success me-2';
            toastElement.classList.remove('bg-danger');
        } else {
            icon.className = 'fas fa-exclamation-circle text-danger me-2';
            toastElement.classList.add('bg-danger');
        }
        
        if (orderToast) {
            orderToast.show();
        }
    }
    </script>
</body>
</html>
