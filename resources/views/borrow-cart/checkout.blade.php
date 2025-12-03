@extends('layouts.frontend')

@section('title', 'Xác nhận mượn sách - Thư Viện Online')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/checkout.css') }}?v={{ time() }}">
<style>
    /* Force background gradient for checkout page */
    html, body {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 25%, #f093fb 50%, #4facfe 75%, #00f2fe 100%) !important;
        background-size: 400% 400% !important;
        animation: gradientShift 15s ease infinite !important;
        min-height: 100vh !important;
    }
    
    @keyframes gradientShift {
        0% { background-position: 0% 50%; }
        50% { background-position: 100% 50%; }
        100% { background-position: 0% 50%; }
    }
    
    body::before {
        content: '';
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(248, 250, 252, 0.75);
        z-index: 0;
        pointer-events: none;
    }
    
    .checkout-page {
        position: relative;
        z-index: 1;
    }
    
    /* Payment Option Styles */
    .payment-option {
        position: relative;
    }
    
    .payment-option input[type="radio"] {
        position: absolute;
        opacity: 0;
        pointer-events: none;
    }
    
    .payment-card {
        display: flex;
        align-items: center;
        padding: 18px;
        background: #ffffff;
        border: 2px solid #e2e8f0;
        border-radius: 12px;
        cursor: pointer;
        transition: all 0.3s ease;
        position: relative;
        min-height: 90px;
    }
    
    .payment-card:hover {
        border-color: #3b82f6;
        box-shadow: 0 4px 12px rgba(59, 130, 246, 0.15);
        transform: translateY(-2px);
    }
    
    .payment-option input[type="radio"]:checked + .payment-card {
        border-color: #10b981;
        background: #f0fdf4;
        box-shadow: 0 4px 12px rgba(16, 185, 129, 0.2);
    }
    
    .payment-icon {
        flex-shrink: 0;
        width: 50px;
        height: 50px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #f8fafc;
        border-radius: 10px;
        margin-right: 15px;
    }
    
    .payment-info {
        flex: 1;
    }
    
    .payment-info h6 {
        font-size: 0.95rem;
        font-weight: 600;
        margin: 0;
    }
    
    .payment-info small {
        font-size: 0.8rem;
    }
    
    .payment-check {
        position: absolute;
        top: 12px;
        right: 12px;
        width: 24px;
        height: 24px;
        display: flex;
        align-items: center;
        justify-content: center;
        opacity: 0;
        transition: all 0.3s ease;
    }
    
    .payment-check i {
        color: #10b981;
        font-size: 1.3rem;
    }
    
    .payment-option input[type="radio"]:checked + .payment-card .payment-check {
        opacity: 1;
    }
    
    /* Input with icon */
    .input-icon-wrapper {
        position: relative;
    }
    
    .input-icon {
        position: absolute;
        left: 15px;
        top: 50%;
        transform: translateY(-50%);
        color: #64748b;
        font-size: 0.95rem;
        z-index: 1;
    }
    
    .input-icon-wrapper .form-control {
        padding-left: 45px !important;
    }
    
    /* Summary Styles */
    .summary-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 12px;
    }
    
    .summary-label {
        color: var(--checkout-text);
        font-size: 0.95rem;
    }
    
    .summary-value {
        color: var(--checkout-text);
        font-weight: 500;
        font-size: 0.95rem;
    }
    
    .summary-value.text-primary {
        color: #3b82f6 !important;
        font-weight: 600;
    }
    
    /* Discount Section */
    .discount-section .input-group-text {
        background-color: #ffffff;
        border: 1.5px solid #e2e8f0;
        border-right: 0;
        border-radius: 8px 0 0 8px;
    }
    
    .discount-section .form-control {
        background-color: #f8fafc;
        border: 1.5px solid #e2e8f0;
        border-left: 0;
        border-right: 0;
        color: var(--checkout-text);
    }
    
    .discount-section .form-control::placeholder {
        color: #94a3b8;
    }
    
    .discount-section .btn-success {
        background: #10b981;
        border: none;
        border-radius: 0 8px 8px 0;
        padding: 0 20px;
        font-weight: 500;
        color: #ffffff;
        transition: all 0.3s ease;
    }
    
    .discount-section .btn-success:hover {
        background: #059669;
        transform: translateY(-1px);
    }
    
    /* Shipping Info Box */
    .shipping-info-box {
        background: #fef3c7;
        border: 1.5px dashed #fbbf24;
        border-radius: 8px;
        padding: 12px 15px;
    }
    
    /* Total Payment */
    .total-payment {
        padding-top: 15px;
        border-top: 2px solid #e2e8f0;
        margin-top: 5px;
    }
    
    /* Checkout Button */
    .btn-checkout {
        background: linear-gradient(135deg, #f97316 0%, #ea580c 100%) !important;
        color: #ffffff !important;
        border: none !important;
        padding: 14px 28px !important;
        border-radius: 10px !important;
        font-weight: 600 !important;
        font-size: 1rem !important;
        box-shadow: 0 4px 15px rgba(249, 115, 22, 0.3) !important;
        transition: all 0.3s ease !important;
    }
    
    .btn-checkout:hover {
        transform: translateY(-2px) !important;
        box-shadow: 0 6px 20px rgba(249, 115, 22, 0.4) !important;
        background: linear-gradient(135deg, #ea580c 0%, #c2410c 100%) !important;
    }
    
    .btn-checkout:disabled {
        opacity: 0.7 !important;
        cursor: not-allowed !important;
        transform: none !important;
    }
</style>
@endpush

@section('content')
<div class="container py-5 checkout-page">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2><i class="fas fa-book-reader text-primary"></i> Xác nhận mượn sách</h2>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">Trang chủ</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('borrow-cart.index') }}">Giỏ sách</a></li>
                        <li class="breadcrumb-item active">Xác nhận mượn sách</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>

    <form id="borrowCheckoutForm" method="POST" action="{{ route('borrow-cart.process-checkout') }}" novalidate>
        @csrf
        <div class="row">
            <!-- Thông tin người mượn -->
            <div class="col-lg-7">
                <!-- Phương thức thanh toán -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-credit-card"></i> Chọn phương thức thanh toán</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <!-- Thanh toán chuyển khoản -->
                            <div class="col-md-6">
                                <div class="payment-option" data-payment="bank_transfer">
                                    <input type="radio" name="payment_method" id="payment_bank" value="bank_transfer" checked>
                                    <label for="payment_bank" class="payment-card">
                                        <div class="payment-icon">
                                            <i class="fas fa-university" style="color: #3b82f6; font-size: 2rem;"></i>
                                        </div>
                                        <div class="payment-info">
                                            <h6 style="color: var(--checkout-text); margin-bottom: 4px;">Thanh toán chuyển khoản</h6>
                                            <small class="text-muted">Thanh toán bằng ứng dụng ngân hàng</small>
                                        </div>
                                        <div class="payment-check">
                                            <i class="fas fa-check-circle"></i>
                                        </div>
                                    </label>
                                </div>
                            </div>

                            <!-- Thanh toán VNPAY -->
                            <div class="col-md-6">
                                <div class="payment-option" data-payment="vnpay">
                                    <input type="radio" name="payment_method" id="payment_vnpay" value="vnpay">
                                    <label for="payment_vnpay" class="payment-card">
                                        <div class="payment-icon">
                                            <i class="fas fa-qrcode" style="color: #ef4444; font-size: 2rem;"></i>
                                        </div>
                                        <div class="payment-info">
                                            <h6 style="color: var(--checkout-text); margin-bottom: 4px;">Thanh toán qua VNPAY</h6>
                                            <small class="text-muted">Cổng thanh toán VNPAY-QR</small>
                                        </div>
                                        <div class="payment-check">
                                            <i class="fas fa-check-circle"></i>
                                        </div>
                                    </label>
                                </div>
                            </div>

                            <!-- Thanh toán bằng ví -->
                            <div class="col-md-6">
                                <div class="payment-option" data-payment="wallet">
                                    <input type="radio" name="payment_method" id="payment_wallet" value="wallet">
                                    <label for="payment_wallet" class="payment-card">
                                        <div class="payment-icon">
                                            <i class="fas fa-wallet" style="color: #8b5cf6; font-size: 2rem;"></i>
                                        </div>
                                        <div class="payment-info">
                                            <h6 style="color: var(--checkout-text); margin-bottom: 4px;">Thanh toán bằng ví</h6>
                                            <small class="text-muted">Số dư không đủ 0đ <span style="color: #ef4444;">Nạp thêm</span></small>
                                        </div>
                                        <div class="payment-check">
                                            <i class="fas fa-check-circle"></i>
                                        </div>
                                    </label>
                                </div>
                            </div>

                            <!-- Thanh toán khi nhận hàng -->
                            <div class="col-md-6">
                                <div class="payment-option" data-payment="cod">
                                    <input type="radio" name="payment_method" id="payment_cod" value="cod">
                                    <label for="payment_cod" class="payment-card">
                                        <div class="payment-icon">
                                            <i class="fas fa-money-bill-wave" style="color: #10b981; font-size: 2rem;"></i>
                                        </div>
                                        <div class="payment-info">
                                            <h6 style="color: var(--checkout-text); margin-bottom: 4px;">Thanh toán khi nhận hàng</h6>
                                            <small class="text-muted">Thanh toán khi nhận được hàng</small>
                                        </div>
                                        <div class="payment-check">
                                            <i class="fas fa-check-circle"></i>
                                        </div>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Thông tin người nhận -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-user"></i> Thông tin người nhận</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <div class="input-icon-wrapper">
                                        <i class="fas fa-user input-icon"></i>
                                        <input type="text" class="form-control ps-5" id="reader_name" name="reader_name"
                                               value="{{ $reader->ho_ten }}" placeholder="Họ và tên">
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <div class="input-icon-wrapper">
                                        <i class="fas fa-phone input-icon"></i>
                                        <input type="tel" class="form-control ps-5" id="reader_phone" name="reader_phone"
                                               value="{{ $reader->so_dien_thoai ?? '' }}" placeholder="Số điện thoại">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <div class="input-icon-wrapper">
                                <i class="fas fa-envelope input-icon"></i>
                                <input type="email" class="form-control ps-5" id="reader_email" name="reader_email"
                                       value="{{ auth()->user()->email ?? '' }}" placeholder="Email">
                            </div>
                        </div>
                        <div class="row">
                            @php
                                $addressParts = explode(',', $reader->dia_chi ?? '');
                                $tinh = count($addressParts) > 2 ? trim($addressParts[count($addressParts) - 1]) : 'Hà Nội';
                                $huyen = count($addressParts) > 1 ? trim($addressParts[count($addressParts) - 2]) : '';
                                $xa = count($addressParts) > 0 ? trim($addressParts[0]) : '';
                            @endphp
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <select class="form-select" id="tinh_thanh" name="tinh_thanh">
                                        <option value="Hà Nội" {{ $tinh == 'Hà Nội' ? 'selected' : '' }}>Hà Nội</option>
                                        <option value="Hồ Chí Minh" {{ $tinh == 'Hồ Chí Minh' ? 'selected' : '' }}>Hồ Chí Minh</option>
                                        <option value="Đà Nẵng" {{ $tinh == 'Đà Nẵng' ? 'selected' : '' }}>Đà Nẵng</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <input type="text" class="form-control" id="huyen" name="huyen" 
                                           value="{{ $huyen }}" placeholder="Quận/Huyện">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <input type="text" class="form-control" id="xa" name="xa" 
                                           value="{{ $xa }}" placeholder="Phường/Xã">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <input type="text" class="form-control" id="so_nha" name="so_nha" 
                                           placeholder="Số nhà">
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <textarea class="form-control" id="notes" name="notes" rows="2" 
                                      placeholder="Nhập ghi chú (không bắt buộc)"></textarea>
                        </div>
                    </div>
                </div>

                <!-- Danh sách sách mượn -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-list"></i> Danh sách sách mượn ({{ $cart->items->count() }} sách)</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-borderless">
                                <thead>
                                    <tr>
                                        <th style="color: var(--checkout-text);">Sách</th>
                                        <th style="color: var(--checkout-text);" class="text-center">Số lượng</th>
                                        <th style="color: var(--checkout-text);" class="text-center">Số ngày</th>
                                        <th style="color: var(--checkout-text);" class="text-end">Tiền cọc</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($cart->items as $item)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                @if($item->book->hinh_anh)
                                                <img src="{{ asset('storage/books/' . $item->book->hinh_anh) }}" 
                                                     alt="{{ $item->book->ten_sach }}" 
                                                     style="width: 50px; height: 70px; object-fit: cover; border-radius: 4px; margin-right: 15px;">
                                                @else
                                                <div style="width: 50px; height: 70px; background: #e2e8f0; border-radius: 4px; margin-right: 15px; display: flex; align-items: center; justify-content: center;">
                                                    <i class="fas fa-book" style="color: #94a3b8;"></i>
                                                </div>
                                                @endif
                                                <div>
                                                    <h6 class="mb-0" style="color: var(--checkout-text);">{{ $item->book->ten_sach }}</h6>
                                                    <small class="text-muted">{{ $item->book->tac_gia ?? 'Không rõ tác giả' }}</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="text-center" style="color: var(--checkout-text); vertical-align: middle;">
                                            {{ $item->quantity }} cuốn
                                        </td>
                                        <td class="text-center" style="color: var(--checkout-text); vertical-align: middle;">
                                            {{ $item->borrow_days }} ngày
                                        </td>
                                        <td class="text-end" style="color: var(--checkout-text); vertical-align: middle;">
                                            @php
                                                // Sử dụng giá cọc đã lưu trong database (giữ nguyên từ giỏ sách)
                                                $depositPerBook = $item->tien_coc ?? 0;
                                                $totalDeposit = $depositPerBook * $item->quantity;
                                            @endphp
                                            {{ number_format($totalDeposit, 0, ',', '.') }}₫
                                        </td>
                                    </tr>
                                    @if(!$loop->last)
                                    <tr><td colspan="4"><hr style="margin: 10px 0;"></td></tr>
                                    @endif
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tóm tắt chi phí mượn sách -->
            <div class="col-lg-5">
                <div class="card mb-4" style="position: sticky; top: 100px;">
                    <div class="card-header">
                        <h5 class="mb-0">Tóm tắt đơn mượn</h5>
                    </div>
                    <div class="card-body">
                        <!-- Mã đơn và thông tin cơ bản -->
                        <div class="summary-row">
                            <span class="summary-label">Mã đơn:</span>
                            <span class="summary-value" style="color: #64748b;">#{{ now()->format('ymdHis') }}{{ auth()->id() }}</span>
                        </div>
                        
                        <div class="summary-row">
                            <span class="summary-label">Tiền cọc:</span>
                            <span class="summary-value text-primary">{{ number_format($totalTienCoc, 0, ',', '.') }}₫</span>
                        </div>
                        
                        <div class="summary-row">
                            <span class="summary-label">Tiền thuê:</span>
                            <span class="summary-value text-primary">{{ number_format($totalTienThue, 0, ',', '.') }}₫</span>
                        </div>
                        
                        <div class="summary-row mb-3" style="padding-bottom: 12px; border-bottom: 1px dashed #e2e8f0;">
                            <span class="summary-label">Giảm giá:</span>
                            <span class="summary-value">-0₫</span>
                        </div>

                        <!-- Mã giảm giá -->
                        <div class="discount-section mb-3">
                            <div class="input-group">
                                <span class="input-group-text bg-white" style="border-right: 0;">
                                    <i class="fas fa-tag" style="color: #64748b;"></i>
                                </span>
                                <input type="text" class="form-control" id="discount_code" name="discount_code" 
                                       placeholder="Nhập mã giảm giá" style="border-left: 0;">
                                <button class="btn btn-success" type="button" onclick="applyDiscount()">Áp dụng</button>
                            </div>
                        </div>

                        <!-- Tạm tính -->
                        <div class="summary-row">
                            <span class="summary-label">Tạm tính:</span>
                            <span class="summary-value text-primary">{{ number_format($totalTienCoc + $totalTienThue, 0, ',', '.') }}₫</span>
                        </div>
                        
                        <div class="summary-row">
                            <span class="summary-label">Giảm giá đơn:</span>
                            <span class="summary-value">-0₫</span>
                        </div>
                        
                        <div class="summary-row mb-3">
                            <span class="summary-label">Phí vận chuyển:</span>
                            <span class="summary-value">
                                @if($totalTienShip > 0)
                                    {{ number_format($totalTienShip, 0, ',', '.') }}₫
                                @else
                                    0₫
                                @endif
                            </span>
                        </div>

                        <!-- Thông tin phí ship -->
                        <div class="shipping-info-box mb-3">
                            <small style="color: #92400e; line-height: 1.5;">
                                <i class="fas fa-info-circle me-1"></i>
                                Phí ship tính theo đơn (không giới hạn số lượng sách) cứ sau 5km mỗi 1km tăng thêm 5 nghìn.
                            </small>
                        </div>

                        <!-- Tổng thanh toán -->
                        <div class="summary-row total-payment mb-4">
                            <span class="summary-label" style="font-size: 1.1rem; font-weight: 600;">Thanh toán:</span>
                            <span class="summary-value" style="font-size: 1.4rem; font-weight: 700; color: #ef4444;">
                                {{ number_format($tongTien, 0, ',', '.') }}₫
                            </span>
                        </div>

                        <!-- Nút hành động -->
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-checkout" id="confirmBorrowBtn">
                                <i class="fas fa-shopping-cart me-2"></i> Mượn sách
                            </button>
                            <a href="{{ route('borrow-cart.index') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-arrow-left"></i> Quay lại giỏ sách
                            </a>
                        </div>

                        <!-- Lưu ý -->
                        <div class="mt-3 pt-3" style="border-top: 1px solid #e2e8f0;">
                            <small style="color: #64748b; line-height: 1.6;">
                                <i class="fas fa-info-circle me-1"></i>
                                Bằng việc tiến hành đặt mượn sách, bạn đồng ý với điều khoản của Thư Viện Online
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<!-- Toast thông báo -->
<div class="toast-container position-fixed bottom-0 end-0 p-3">
    <div id="borrowToast" class="toast" role="alert">
        <div class="toast-header">
            <i class="fas fa-book-reader text-success me-2"></i>
            <strong class="me-auto">Thông báo</strong>
            <button type="button" class="btn-close" data-bs-dismiss="toast"></button>
        </div>
        <div class="toast-body" id="toastMessage">
            <!-- Nội dung thông báo sẽ được thêm vào đây -->
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function() {
    function initBorrowCheckout() {
        const borrowCheckoutForm = document.getElementById('borrowCheckoutForm');
        const confirmBorrowBtn = document.getElementById('confirmBorrowBtn');
        
        if (!borrowCheckoutForm || !confirmBorrowBtn) {
            return false;
        }
        
        // Xử lý click payment cards
        const paymentCards = document.querySelectorAll('.payment-card');
        paymentCards.forEach(card => {
            card.addEventListener('click', function() {
                const radio = this.previousElementSibling;
                if (radio && radio.type === 'radio') {
                    radio.checked = true;
                }
            });
        });
        
        // Khởi tạo toast
        let borrowToast;
        try {
            const toastElement = document.getElementById('borrowToast');
            if (toastElement) {
                borrowToast = new bootstrap.Toast(toastElement);
            }
        } catch (e) {
            console.error('Error initializing toast:', e);
        }

        // Xử lý submit form
        borrowCheckoutForm.addEventListener('submit', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            const button = confirmBorrowBtn;
            const originalText = button.innerHTML;
            
            // Validate thông tin
            const readerName = document.getElementById('reader_name').value.trim();
            const readerPhone = document.getElementById('reader_phone').value.trim();
            const readerEmail = document.getElementById('reader_email').value.trim();
            const paymentMethod = document.querySelector('input[name="payment_method"]:checked');
            
            if (!readerName) {
                showToast('error', 'Vui lòng nhập họ và tên');
                return;
            }
            
            if (!readerPhone) {
                showToast('error', 'Vui lòng nhập số điện thoại');
                return;
            }
            
            if (!readerEmail) {
                showToast('error', 'Vui lòng nhập email');
                return;
            }
            
            if (!paymentMethod) {
                showToast('error', 'Vui lòng chọn phương thức thanh toán');
                return;
            }
            
            if (!confirm('Bạn có chắc chắn muốn mượn tất cả các sách này?')) {
                return;
            }
            
            // Hiển thị loading
            button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang xử lý...';
            button.disabled = true;
            
            // Lấy CSRF token
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || 
                              document.querySelector('input[name="_token"]')?.value;
            
            if (!csrfToken) {
                showToast('error', 'Không tìm thấy token bảo mật. Vui lòng tải lại trang.');
                button.innerHTML = originalText;
                button.disabled = false;
                return;
            }
            
            // Lấy dữ liệu form
            const formData = new FormData(this);
            
            // Gửi request
            fetch('{{ route("borrow-cart.process-checkout") }}', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(async response => {
                const contentType = response.headers.get('content-type');
                
                // Xử lý response
                if (!contentType || !contentType.includes('application/json')) {
                    // Có thể server đã redirect, check nếu response là HTML của trang redirect
                    const text = await response.text();
                    
                    // Nếu response chứa URL VNPay, cố gắng extract và redirect
                    if (text.includes('vnpayment.vn')) {
                        try {
                            const urlMatch = text.match(/https:\/\/sandbox\.vnpayment\.vn[^"'\s]*/);
                            if (urlMatch && urlMatch[0]) {
                                showToast('success', 'Đang chuyển đến trang thanh toán VnPay...');
                                setTimeout(() => {
                                    window.location.href = urlMatch[0];
                                }, 1000);
                                return;
                            }
                        } catch (e) {
                            console.error('Error parsing VNPay URL:', e);
                        }
                    }
                    
                    console.error('Response is not JSON:', text);
                    showToast('error', 'Phản hồi từ server không đúng định dạng. Vui lòng thử lại.');
                    button.innerHTML = originalText;
                    button.disabled = false;
                    return;
                }
                
                const data = await response.json();
                
                if (!response.ok) {
                    let errorMessage = data.message || 'Có lỗi xảy ra';
                    
                    if (data.errors) {
                        const errorList = Object.values(data.errors).flat().join(', ');
                        errorMessage = errorList || errorMessage;
                    }
                    
                    showToast('error', errorMessage);
                    
                    if (data.redirect) {
                        setTimeout(() => {
                            window.location.href = data.redirect;
                        }, 2000);
                    } else {
                        button.innerHTML = originalText;
                        button.disabled = false;
                    }
                    return;
                }
                
                if (data.success) {
                    // Kiểm tra nếu cần thanh toán VnPay
                    if (data.payment_required && data.payment_url) {
                        showToast('success', 'Đang chuyển đến trang thanh toán VnPay...');
                        setTimeout(() => {
                            window.location.href = data.payment_url;
                        }, 1000);
                    } else {
                        showToast('success', data.message || 'Đã tạo yêu cầu mượn sách thành công!');
                        
                        setTimeout(() => {
                            window.location.href = '{{ route("account.borrowed-books") }}';
                        }, 1500);
                    }
                } else {
                    showToast('error', data.message || 'Có lỗi xảy ra khi tạo yêu cầu mượn sách');
                    button.innerHTML = originalText;
                    button.disabled = false;
                }
            })
            .catch(error => {
                console.error('Fetch Error:', error);
                showToast('error', 'Có lỗi xảy ra khi kết nối đến server: ' + error.message);
                button.innerHTML = originalText;
                button.disabled = false;
            });
        });

        // Hàm áp dụng mã giảm giá
        window.applyDiscount = function() {
            const discountInput = document.getElementById('discount_code');
            const code = discountInput.value.trim();
            
            if (!code) {
                showToast('error', 'Vui lòng nhập mã giảm giá');
                return;
            }
            
            // TODO: Implement discount code validation API call
            // For now, show a message that this feature is coming soon
            showToast('error', 'Tính năng mã giảm giá đang được phát triển');
        };

        // Hàm hiển thị toast
        function showToast(type, message) {
            try {
                const toastElement = document.getElementById('borrowToast');
                const toastMessage = document.getElementById('toastMessage');
                
                if (!toastElement || !toastMessage) {
                    alert(message);
                    return;
                }
                
                toastMessage.textContent = message;
                
                const toastHeader = toastElement.querySelector('.toast-header');
                if (toastHeader) {
                    const icon = toastHeader.querySelector('i');
                    if (icon) {
                        if (type === 'success') {
                            icon.className = 'fas fa-check-circle text-success me-2';
                            toastElement.classList.remove('bg-danger');
                        } else {
                            icon.className = 'fas fa-exclamation-circle text-danger me-2';
                            toastElement.classList.add('bg-danger');
                        }
                    }
                }
                
                if (borrowToast) {
                    borrowToast.show();
                } else {
                    alert(message);
                }
            } catch (error) {
                console.error('Error showing toast:', error);
                alert(message);
            }
        }
        
        return true;
    }
    
    // Thử khởi tạo
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() {
            if (!initBorrowCheckout()) {
                setTimeout(function() {
                    initBorrowCheckout();
                }, 100);
            }
        });
    } else {
        if (!initBorrowCheckout()) {
            setTimeout(function() {
                initBorrowCheckout();
            }, 100);
        }
    }
})();
</script>
@endpush

