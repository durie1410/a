<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Giỏ sách - Nhà Xuất Bản Xây Dựng</title>
    <link rel="stylesheet" href="{{ asset('css/style.css') }}?v={{ time() }}">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --cart-primary: #2563eb;
            --cart-bg: #f8fafc;
            --cart-border: #e2e8f0;
            --cart-text: #1e293b;
            --cart-muted: #64748b;
            --cart-danger: #ef4444;
            --cart-success: #10b981;
            --cart-warning: #f59e0b;
            --radius-xl: 16px;
            --radius-lg: 12px;
            --radius-md: 8px;
        }

        body {
            background-color: var(--cart-bg);
            color: var(--cart-text);
        }

        .cart-page-container {
            max-width: 1300px;
            margin: 0 auto;
            padding: 40px 20px;
        }

        /* Breadcrumb & Title */
        .page-header {
            margin-bottom: 32px;
        }

        .breadcrumb {
            display: flex;
            align-items: center;
            gap: 8px;
            color: var(--cart-muted);
            font-size: 14px;
            margin-bottom: 12px;
        }

        .breadcrumb a {
            color: var(--cart-primary);
            text-decoration: none;
            transition: opacity 0.2s;
        }

        .breadcrumb a:hover {
            opacity: 0.8;
        }

        .page-title {
            font-size: 28px;
            font-weight: 800;
            color: var(--cart-text);
            margin: 0;
        }

        /* Layout Grid */
        .cart-content-wrapper {
            display: grid;
            grid-template-columns: 1fr 380px;
            gap: 32px;
            align-items: start;
        }

        /* Left Column: Items List */
        .cart-main-content {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        /* Selection Bar */
        .selection-bar {
            background: white;
            padding: 16px 24px;
            border-radius: var(--radius-lg);
            border: 1px solid var(--cart-border);
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        }

        .select-all-wrapper {
            display: flex;
            align-items: center;
            gap: 12px;
            font-weight: 600;
        }

        .custom-checkbox {
            width: 20px;
            height: 20px;
            cursor: pointer;
            accent-color: var(--cart-primary);
        }

        .btn-clear-cart {
            color: var(--cart-danger);
            background: none;
            border: none;
            font-weight: 600;
            font-size: 14px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 6px;
            padding: 8px 12px;
            border-radius: var(--radius-md);
            transition: all 0.2s;
        }

        .btn-clear-cart:hover {
            background: #fef2f2;
        }

        /* Cart Item Card */
        .cart-item {
            background: white;
            border-radius: var(--radius-lg);
            border: 1px solid var(--cart-border);
            padding: 24px;
            display: grid;
            grid-template-columns: auto 120px 1fr auto;
            gap: 24px;
            align-items: center;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .cart-item:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        }

        .cart-item-image-box {
            width: 110px;
            height: 154px;
            border-radius: var(--radius-md);
            overflow: hidden;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }

        .cart-item-image-box img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .cart-item-details {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .item-title {
            font-size: 18px;
            font-weight: 700;
            margin: 0;
        }

        .item-title a {
            color: var(--cart-text);
            text-decoration: none;
        }

        .item-meta {
            font-size: 14px;
            color: var(--cart-muted);
            display: flex;
            gap: 16px;
        }

        .item-price-tag {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: #eff6ff;
            color: var(--cart-primary);
            padding: 4px 12px;
            border-radius: 50px;
            font-weight: 700;
            font-size: 14px;
        }

        /* Controls Column */
        .item-controls-grid {
            display: grid;
            grid-template-columns: 140px 140px;
            gap: 24px;
            align-items: center;
        }

        .control-group {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .control-label {
            font-size: 12px;
            font-weight: 600;
            color: var(--cart-muted);
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        /* Quantity Inputs */
        .modern-quantity {
            display: flex;
            align-items: center;
            border: 1px solid var(--cart-border);
            border-radius: var(--radius-md);
            overflow: hidden;
            width: fit-content;
        }

        .qty-btn {
            width: 36px;
            height: 36px;
            background: white;
            border: none;
            cursor: pointer;
            font-size: 18px;
            color: var(--cart-text);
            transition: background 0.2s;
        }

        .qty-btn:hover { background: #f1f5f9; }

        .qty-input {
            width: 48px;
            height: 36px;
            border: none;
            border-left: 1px solid var(--cart-border);
            border-right: 1px solid var(--cart-border);
            text-align: center;
            font-weight: 700;
            -moz-appearance: textfield;
        }

        .qty-input::-webkit-outer-spin-button,
        .qty-input::-webkit-inner-spin-button { -webkit-appearance: none; margin: 0; }

        /* Right Column: Summary */
        .cart-sidebar {
            position: sticky;
            top: 100px;
            display: flex;
            flex-direction: column;
            gap: 24px;
        }

        .summary-card {
            background: white;
            border-radius: var(--radius-xl);
            border: 1px solid var(--cart-border);
            padding: 24px;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        }

        .summary-title {
            font-size: 20px;
            font-weight: 800;
            margin-bottom: 20px;
            padding-bottom: 16px;
            border-bottom: 1px solid var(--cart-border);
        }

        /* Shipping Estimate Module */
        .shipping-estimate {
            background: #f8fafc;
            border-radius: var(--radius-lg);
            padding: 16px;
            margin-bottom: 24px;
            border: 1px solid var(--cart-border);
        }

        .estimate-header {
            display: flex;
            align-items: center;
            gap: 8px;
            font-weight: 700;
            font-size: 14px;
            margin-bottom: 12px;
            color: var(--cart-primary);
        }

        .estimate-grid {
            display: grid;
            gap: 12px;
        }

        .compact-input {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid var(--cart-border);
            border-radius: var(--radius-md);
            font-size: 13px;
            outline: none;
            transition: border-color 0.2s;
        }

        .compact-input:focus { border-color: var(--cart-primary); }

        .btn-estimate {
            width: 100%;
            padding: 10px;
            background: white;
            border: 1px solid var(--cart-primary);
            color: var(--cart-primary);
            border-radius: var(--radius-md);
            font-weight: 700;
            font-size: 13px;
            cursor: pointer;
            transition: all 0.2s;
        }

        .btn-estimate:hover {
            background: var(--cart-primary);
            color: white;
        }

        /* Summary Breakdown */
        .breakdown-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 12px;
            font-size: 14px;
            color: var(--cart-muted);
        }

        .breakdown-row.total {
            border-top: 1px solid var(--cart-border);
            margin-top: 16px;
            padding-top: 16px;
            color: var(--cart-text);
            font-weight: 800;
            font-size: 18px;
        }

        .final-price {
            color: var(--cart-danger);
        }

        .btn-checkout-primary {
            width: 100%;
            background: var(--cart-primary);
            color: white;
            padding: 16px;
            border-radius: 50px;
            border: none;
            font-weight: 800;
            font-size: 16px;
            cursor: pointer;
            margin-top: 24px;
            box-shadow: 0 4px 14px 0 rgba(37, 99, 235, 0.39);
            transition: all 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .btn-checkout-primary:hover {
            transform: scale(1.02);
            background: #1d4ed8;
        }

        /* Empty State */
        .empty-cart-view {
            text-align: center;
            padding: 80px 40px;
            background: white;
            border-radius: var(--radius-xl);
            border: 1px solid var(--cart-border);
        }

        .empty-icon {
            font-size: 80px;
            margin-bottom: 24px;
            opacity: 0.2;
        }

        .btn-back-home {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 12px 24px;
            background: var(--cart-primary);
            color: white;
            text-decoration: none;
            border-radius: 50px;
            font-weight: 700;
            margin-top: 32px;
        }

        /* Toasts */
        .toast-container {
            position: fixed;
            top: 24px;
            right: 24px;
            z-index: 9999;
        }

        @media (max-width: 1024px) {
            .cart-content-wrapper { grid-template-columns: 1fr; }
            .cart-sidebar { position: static; }
        }

        @media (max-width: 768px) {
            .cart-item {
                grid-template-columns: auto 1fr;
                padding: 16px;
            }
            .cart-item-checkbox-box { grid-row: 1 / 3; }
            .item-controls-grid { 
                grid-template-columns: 1fr;
                grid-column: 1 / -1; 
            }
        }
    </style>
</head>
<body>
    @include('account._header')
    
    <div class="cart-page-container">
        <div class="page-header">
            <div class="breadcrumb">
                <a href="{{ route('home') }}">Trang chủ</a>
                <i class="fas fa-chevron-right" style="font-size: 10px;"></i>
                <span>Giỏ sách</span>
            </div>
            <h1 class="page-title">Giỏ sách của bạn</h1>
        </div>
        
        @if(!$cart || $cart->items->count() === 0)
            <div class="empty-cart-view">
                <div class="empty-icon"><i class="fas fa-shopping-basket"></i></div>
                <h2 style="font-size: 24px; font-weight: 800; margin-bottom: 8px;">Giỏ sách trống trơn!</h2>
                <p style="color: var(--cart-muted);">Có vẻ như bạn chưa chọn cuốn sách nào để mượn.</p>
                <a href="{{ route('books.public') }}" class="btn-back-home">
                    <i class="fas fa-book"></i> Khám phá kho sách
                </a>
            </div>
        @else
            <div class="cart-content-wrapper">
                <div class="cart-main-content">
                    <div class="selection-bar">
                        <div class="select-all-wrapper">
                            <input type="checkbox" id="select-all-items" class="custom-checkbox" onchange="toggleSelectAllItems()">
                            <label for="select-all-items">Chọn tất cả ({{ $cart->getTotalItemsAttribute() }} sách)</label>
                        </div>
                        <button type="button" class="btn-clear-cart" onclick="clearCart()">
                            <i class="fas fa-trash-alt"></i> Xóa tất cả
                        </button>
                    </div>

                    @foreach($cart->items as $item)
                        @php
                            $book = $item->book;
                            if (!$book) continue;
                            $availableCopies = \App\Models\Inventory::where('book_id', $book->id)
                                ->where('status', 'Co san')
                                ->count();
                        @endphp
                        <div class="cart-item" data-item-id="{{ $item->id }}"
                             data-tien-thue="{{ ($item->tien_thue ?? 0) * $item->quantity }}"
                             data-tien-coc="{{ ($item->tien_coc ?? 0) * $item->quantity }}">
                            
                            <div class="cart-item-checkbox-box">
                                <input type="checkbox" class="item-checkbox custom-checkbox" 
                                       data-item-id="{{ $item->id }}" 
                                       {{ $item->is_selected ? 'checked' : '' }} 
                                       onchange="handleCheckboxChange(this)">
                            </div>

                            <div class="cart-item-image-box">
                                @if($book->hinh_anh)
                                    <img src="{{ asset('storage/' . $book->hinh_anh) }}" alt="{{ $book->ten_sach }}">
                                @else
                                    <div class="book-placeholder" style="height: 100%; display: flex; align-items: center; justify-content: center; font-size: 40px; background: #e2e8f0;">📖</div>
                                @endif
                            </div>

                            <div class="cart-item-details">
                                <h3 class="item-title">
                                    <a href="{{ route('books.show', $book->id) }}">{{ $book->ten_sach }}</a>
                                </h3>
                                <div class="item-meta">
                                    <span>Tác giả: <strong>{{ $book->tac_gia ?? 'N/A' }}</strong></span>
                                    <span>Thể loại: <strong>{{ $book->category->ten_the_loai ?? 'N/A' }}</strong></span>
                                </div>
                                <div style="margin-top: 12px; display: flex; gap: 12px; align-items: center;">
                                    <div class="item-price-tag" title="Giá trị sách">
                                        <i class="fas fa-tag"></i> {{ number_format($book->gia ?? 0, 0, ',', '.') }}₫
                                    </div>
                                    <span style="font-size: 12px; color: var(--cart-muted);">* Phí cọc = 100% giá trị sách</span>
                                </div>
                            </div>

                            <div class="item-controls-grid">
                                <div class="control-group">
                                    <span class="control-label">Số lượng</span>
                                    <div class="modern-quantity">
                                        <button type="button" class="qty-btn" onclick="updateQuantity({{ $item->id }}, -1)">-</button>
                                        <input type="number" id="quantity-{{ $item->id }}" value="{{ $item->quantity }}" 
                                               min="1" max="{{ $availableCopies }}" class="qty-input"
                                               onchange="updateQuantityInput({{ $item->id }})">
                                        <button type="button" class="qty-btn" onclick="updateQuantity({{ $item->id }}, 1)">+</button>
                                    </div>
                                    <span style="font-size: 11px; color: var(--cart-muted);">Sẵn có: {{ $availableCopies }}</span>
                                </div>
                                
                                <div class="control-group">
                                    <span class="control-label">Thành tiền</span>
                                    @php
                                        $itemTotal = (($item->tien_thue ?? 0) + ($item->tien_coc ?? 0)) * $item->quantity;
                                    @endphp
                                    <div style="font-weight: 800; color: var(--cart-danger); font-size: 16px;" id="subtotal-{{ $item->id }}">
                                        {{ number_format($itemTotal, 0, ',', '.') }}₫
                                    </div>
                                    <button type="button" style="background: none; border: none; color: var(--cart-muted); font-size: 12px; text-align: left; cursor: pointer; padding: 0;" onclick="removeItem({{ $item->id }})">
                                        <i class="fas fa-times-circle"></i> Loại bỏ
                                    </button>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="cart-sidebar">
                    <div class="summary-card">
                        <h3 class="summary-title">Tóm tắt đơn hàng</h3>
                        
                        <div class="shipping-estimate">
                            <div class="estimate-header">
                                <i class="fas fa-truck-fast"></i>
                                <span>Giao hàng (Chỉ áp dụng Hà Nội)</span>
                            </div>
                            <div class="estimate-grid">
                                <select class="compact-input" id="shipping-xa-cart" required>
                                    <option value="">-- Chọn Phường / Xã * --</option>
                                    @php
                                        $wards = [
                                            'Phường Cửa Đông', 'Phường Cửa Nam', 'Phường Đồng Xuân', 'Phường Hàng Bạc', 'Phường Hàng Bồ', 'Phường Hàng Bông', 'Phường Hàng Buồm', 'Phường Hàng Đào', 'Phường Hàng Gai', 'Phường Hàng Mã', 'Phường Hàng Trống', 'Phường Lý Thái Tổ', 'Phường Phan Chu Trinh', 'Phường Phúc Tân', 'Phường Trần Hưng Đạo', 'Phường Chương Dương Độ', 'Phường Cổ Dương', 'Phường Đông Mác', 'Phường Đồng Nhân', 'Phường Đồng Tâm', 'Phường Lê Đại Hành', 'Phường Minh Khai', 'Phường Ngô Thì Nhậm', 'Phường Nguyễn Du', 'Phường Phố Huế', 'Phường Phúc Đồng', 'Phường Quỳnh Lôi', 'Phường Quỳnh Mai', 'Phường Thanh Lương', 'Phường Thanh Nhàn', 'Phường Trương Định', 'Phường Vĩnh Tuy', 'Phường Bạch Đằng', 'Phường Bách Khoa', 'Phường Bồ Đề', 'Phường Cự Khối', 'Phường Đức Giang', 'Phường Gia Thụy', 'Phường Giang Biên', 'Phường Long Biên', 'Phường Ngọc Lâm', 'Phường Ngọc Thụy', 'Phường Phúc Đồng', 'Phường Phúc Lợi', 'Phường Sài Đồng', 'Phường Thạch Bàn', 'Phường Thượng Thanh', 'Phường Việt Hưng', 'Phường Cát Linh', 'Phường Hàng Bột', 'Phường Khâm Thiên', 'Phường Khương Thượng', 'Phường Kim Liên', 'Phường Láng Hạ', 'Phường Láng Thượng', 'Phường Nam Đồng', 'Phường Ngã Tư Sở', 'Phường Ô Chợ Dừa', 'Phường Phương Liên', 'Phường Phương Mai', 'Phường Quang Trung', 'Phường Quốc Tử Giám', 'Phường Thịnh Quang', 'Phường Thổ Quan', 'Phường Trung Liệt', 'Phường Trung Phụng', 'Phường Văn Chương', 'Phường Văn Miếu', 'Phường Bưởi', 'Phường Nhật Tân', 'Phường Phú Thượng', 'Phường Quảng An', 'Phường Thụy Khuê', 'Phường Tứ Liên', 'Phường Xuân La', 'Phường Yên Phụ', 'Phường Cầu Dền', 'Phường Đống Mác', 'Phường Đồng Tâm', 'Phường Láng Hạ', 'Phường Láng Thượng', 'Phường Ngã Tư Sở', 'Phường Ô Chợ Dừa', 'Phường Phương Liên', 'Phường Phương Mai', 'Phường Thịnh Quang', 'Phường Trung Liệt', 'Phường Văn Chương', 'Phường Bạch Mai', 'Phường Bùi Thị Xuân', 'Phường Cầu Dền', 'Phường Đống Mác', 'Phường Giáp Bát', 'Phường Láng Hạ', 'Phường Láng Thượng', 'Phường Mai Động', 'Phường Minh Khai', 'Phường Ngã Tư Sở', 'Phường Nguyễn Du', 'Phường Phố Huế', 'Phường Quỳnh Lôi', 'Phường Quỳnh Mai', 'Phường Thanh Lương', 'Phường Thanh Nhàn', 'Phường Trương Định', 'Phường Vĩnh Tuy', 'Phường Bạch Đằng', 'Phường Bách Khoa', 'Phường Bồ Đề', 'Phường Cự Khối', 'Phường Đức Giang', 'Phường Gia Thụy', 'Phường Giang Biên', 'Phường Long Biên', 'Phường Ngọc Lâm', 'Phường Ngọc Thụy', 'Phường Phúc Đồng', 'Phường Phúc Lợi', 'Phường Sài Đồng', 'Phường Thạch Bàn', 'Phường Thượng Thanh', 'Phường Việt Hưng'
                                        ];
                                    @endphp
                                    @foreach($wards as $ward)
                                        <option value="{{ $ward }}">{{ $ward }}</option>
                                    @endforeach
                                </select>
                                <input type="text" class="compact-input" id="shipping-sonha-cart" placeholder="Số nhà, tên đường *" required>
                                
                                <div id="hanoi-ship-notice" style="padding: 12px; border-radius: var(--radius-md); font-size: 13px; line-height: 1.5; background: #f0f9ff; color: #0369a1; border: 1px solid #bae6fd;">
                                    <i class="fas fa-info-circle"></i> Phí ship nội thành Hà Nội: <strong>20.000₫</strong>
                                </div>
                            </div>
                        </div>

                        <div class="control-group" style="margin-bottom: 24px; background: #f1f5f9; padding: 16px; border-radius: var(--radius-md);">
                            <span class="control-label" style="margin-bottom: 8px; display: block;">Số ngày mượn</span>
                            <select id="common-borrow-days" onchange="updateCommonBorrowDays()" class="compact-input" style="background: white;">
                                <option value="">-- Chọn số ngày mượn --</option>
                                @for($i = 7; $i <= 30; $i++)
                                    <option value="{{ $i }}">{{ $i }} ngày</option>
                                @endfor
                            </select>
                        </div>

                        <div id="rental-summary-results">
                            <div class="breakdown-row">
                                <span>Tiền thuê sách:</span>
                                <span id="summary-tien-thue">0₫</span>
                            </div>
                            <div class="breakdown-row">
                                <span>Tiền cọc sách:</span>
                                <span id="summary-tien-coc">0₫</span>
                            </div>
                            <div class="breakdown-row">
                                <span>Phí vận chuyển:</span>
                                <span id="summary-tien-ship">0₫</span>
                            </div>
                            
                            <div class="breakdown-row total">
                                <span>Tổng cộng:</span>
                                <span class="final-price" id="final-payment">0₫</span>
                            </div>
                        </div>

                        <button type="button" class="btn-checkout-primary" id="btn-checkout-main" onclick="checkout()">
                            XÁC NHẬN MƯỢN SÁCH <i class="fas fa-arrow-right"></i>
                        </button>
                    </div>

                    <div class="summary-card" style="padding: 16px;">
                        <span class="control-label" style="display: block; margin-bottom: 12px;">Mã giảm giá</span>
                        <div style="display: flex; gap: 8px;">
                            <input type="text" id="discount-code" class="compact-input" placeholder="Nhập mã...">
                            <button type="button" class="btn-estimate" style="width: auto; padding: 0 16px;" onclick="applyDiscountCode()">Áp dụng</button>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>

    @include('components.footer')

<script>
    function toggleSelectAllItems() {
        const selectAll = document.getElementById('select-all-items');
        const checkboxes = document.querySelectorAll('.item-checkbox');
        checkboxes.forEach(cb => {
            cb.checked = selectAll.checked;
            updateItemSelected(cb.getAttribute('data-item-id'), selectAll.checked);
        });
        recalculateSummary();
    }

    function handleCheckboxChange(checkbox) {
        updateItemSelected(checkbox.getAttribute('data-item-id'), checkbox.checked);
        recalculateSummary();
        
        // Nếu đã chọn sách và đã có số ngày mượn được chọn, tự động áp dụng
        const selectedDays = document.getElementById('common-borrow-days').value;
        if (checkbox.checked && selectedDays && selectedDays !== '') {
            // Tự động áp dụng số ngày mượn cho sách vừa chọn
            const itemId = checkbox.getAttribute('data-item-id');
            fetch(`{{ route('borrow-cart.update', '') }}/${itemId}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({ borrow_days: selectedDays })
            });
        }
    }

    function updateItemSelected(itemId, isSelected) {
        fetch(`{{ route('borrow-cart.update', '') }}/${itemId}`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({ is_selected: isSelected })
        });
    }

    function recalculateSummary() {
        let totalTienThue = 0;
        let totalTienCoc = 0;
        let totalTienShip = window.manualShippingFee || 0;
        let hasSelection = false;

        document.querySelectorAll('.item-checkbox:checked').forEach(cb => {
            hasSelection = true;
            const item = cb.closest('.cart-item');
            totalTienThue += parseFloat(item.getAttribute('data-tien-thue')) || 0;
            totalTienCoc += parseFloat(item.getAttribute('data-tien-coc')) || 0;
        });

        if (!hasSelection) {
            totalTienThue = 0; totalTienCoc = 0; totalTienShip = 0;
        }

        document.getElementById('summary-tien-thue').textContent = formatCurrency(totalTienThue);
        document.getElementById('summary-tien-coc').textContent = formatCurrency(totalTienCoc);
        document.getElementById('summary-tien-ship').textContent = formatCurrency(totalTienShip);
        document.getElementById('final-payment').textContent = formatCurrency(totalTienThue + totalTienCoc + totalTienShip);
    }

    function updateQuantity(itemId, change) {
        const input = document.getElementById('quantity-' + itemId);
        const currentValue = parseInt(input.value) || 1;
        const newValue = currentValue + change;
        
        // Nếu trừ và giá trị mới sẽ <= 0, hỏi xác nhận xóa
        if (change < 0 && newValue <= 0) {
            if (confirm('Bạn có muốn xóa sách này khỏi giỏ hàng không?')) {
                // Xóa item khỏi giỏ hàng
                removeItem(itemId);
            }
            return; // Không làm gì nếu hủy
        }
        
        // Đảm bảo số lượng tối thiểu là 1
        input.value = Math.max(1, newValue);
        updateQuantityInput(itemId);
    }

    function updateQuantityInput(itemId) {
        const qty = document.getElementById('quantity-' + itemId).value;
        fetch(`{{ route('borrow-cart.update', '') }}/${itemId}`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({ quantity: qty })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) location.reload();
            else {
                if(window.showGlobalModal) window.showGlobalModal('Thông báo', data.message, 'error');
                else alert(data.message);
            }
        });
    }

    function updateCommonBorrowDays() {
        const days = document.getElementById('common-borrow-days').value;
        const selected = Array.from(document.querySelectorAll('.item-checkbox:checked')).map(cb => cb.dataset.itemId);
        
        // Nếu chưa chọn số ngày, không làm gì cả (cho phép khách chọn trước)
        if (!days || days === '') {
            return;
        }
        
        // Nếu chưa chọn sách nào, chỉ lưu giá trị đã chọn, không hiển thị cảnh báo
        if (selected.length === 0) {
            // Cho phép khách chọn số ngày trước, sau đó khi chọn sách sẽ tự động áp dụng
            return;
        }

        // Cập nhật số ngày mượn cho các sách đã chọn (không reload trang)
        Promise.all(selected.map(id => 
            fetch(`{{ route('borrow-cart.update', '') }}/${id}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({ borrow_days: days })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success && data.item) {
                    // Cập nhật lại data attributes của item để tính lại tổng tiền
                    const itemElement = document.querySelector(`[data-item-id="${id}"]`);
                    if (itemElement) {
                        const quantity = parseInt(document.getElementById(`quantity-${id}`).value) || 1;
                        const tienThue = (data.item.tien_thue || 0) * quantity;
                        const tienCoc = (data.item.tien_coc || 0) * quantity;
                        itemElement.setAttribute('data-tien-thue', tienThue);
                        itemElement.setAttribute('data-tien-coc', tienCoc);
                        
                        // Cập nhật thành tiền của item
                        const itemTotal = tienThue + tienCoc;
                        const subtotalElement = document.getElementById(`subtotal-${id}`);
                        if (subtotalElement) {
                            subtotalElement.textContent = formatCurrency(itemTotal);
                        }
                    }
                }
                return data;
            })
        ))
        .then(() => {
            // Tính lại tổng tiền sau khi cập nhật
            recalculateSummary();
        });
    }

    function removeItem(itemId) {
        if (!confirm('Xóa sách này?')) return;
        fetch(`{{ route('borrow-cart.remove', '') }}/${itemId}`, {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
        }).then(() => location.reload());
    }

    function clearCart() {
        if (!confirm('Xóa toàn bộ giỏ?')) return;
        fetch('{{ route('borrow-cart.clear') }}', {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
        }).then(() => location.reload());
    }

    function updateShippingFeeHanoi() {
        // 固定为Hà Nội，运费20000
        window.manualShippingFee = 20000;
        recalculateSummary();
    }

    function formatCurrency(n) {
        return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(n);
    }

    function checkout() {
        const xa = document.getElementById('shipping-xa-cart').value.trim();
        const soNha = document.getElementById('shipping-sonha-cart').value.trim();

        if (!xa) {
            showToast('⚠️ Vui lòng chọn Phường/Xã!', 'warning');
            return;
        }

        if (!soNha) {
            showToast('⚠️ Vui lòng nhập số nhà, tên đường!', 'warning');
            return;
        }

        const selectedCount = document.querySelectorAll('.item-checkbox:checked').length;
        if (selectedCount === 0) {
            showToast('⚠️ Vui lòng chọn ít nhất một quyển sách!', 'warning');
            return;
        }

        // Truyền phí ship và địa chỉ sang checkout
        const params = new URLSearchParams({
            manual_shipping_fee: 20000,
            address_tinh: 'Hà Nội',
            address_xa: xa,
            address_sonha: soNha
        });

        window.location.href = `{{ route('borrow-cart.checkout') }}?${params.toString()}`;
    }

    function showToast(msg, type) {
        if(window.showGlobalModal) window.showGlobalModal('Thông báo', msg, type || 'info');
        else alert(msg);
    }

    document.addEventListener('DOMContentLoaded', () => {
        recalculateSummary();
        // 初始化运费
        updateShippingFeeHanoi();
    });
</script>
</body>
</html>
