@php
    /**
     * @var \App\Models\Book $book
     */
@endphp

<header class="main-header">
    <div class="header-top">
        <div class="logo-section">
            <a href="{{ route('home') }}" style="text-decoration: none; display: flex; align-items: center;">
                <img src="{{ asset('favicon.ico') }}" alt="Logo" class="logo-img">
                <div class="logo-text">
                    <span class="logo-part1">THƯ VIỆN</span>
                    <span class="logo-part2">LIBHUB</span>
                </div>
            </a>
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
                <a href="{{ route('borrow-cart.index') }}" class="cart-link" id="borrow-cart-link" title="Giỏ sách">
                    <i class="fas fa-shopping-cart"></i>
                    <span>Giỏ sách</span>
                    <span class="cart-badge" id="borrow-cart-count" style="display: none;">0</span>
                </a>
                <div class="user-menu-dropdown" style="position: relative;">
                    <a href="#" class="auth-link user-menu-toggle">
                        <span class="user-icon">👤</span>
                        <span>{{ auth()->user()->name }}</span>
                    </a>
                    <div class="user-dropdown-menu">
                        <div class="dropdown-header"
                            style="padding: 12px 15px; border-bottom: 1px solid #eee; font-weight: 600; color: #333;">
                            <span class="user-icon">👤</span>
                            {{ auth()->user()->name }}
                        </div>
                        @if(auth()->user()->reader)
                            <a href="{{ route('account.borrowed-books') }}" class="dropdown-item">
                                <span>📚</span> Sách đang mượn
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
            @else
                <a href="{{ route('login') }}" class="auth-link">
                    <span class="user-icon">👤</span> Đăng nhập
                </a>
                <a href="{{ route('register') }}" class="auth-link">Đăng ký</a>
            @endauth
        </div>
    </div>
    <div class="header-nav">
        <div class="search-bar">
            <form action="{{ route('books.public') }}" method="GET" class="search-form">
                <input type="text" name="keyword" placeholder="Tìm sách, tác giả, sản phẩm mong muốn..." 
                    value="{{ request('keyword') }}" class="search-input">
                <button type="submit" class="search-button">🔍 Tìm kiếm</button>
            </form>
        </div>
    </div>
</header>

@auth
<script>
    document.addEventListener('DOMContentLoaded', function () {
        loadBorrowCartCount();
    });

    function loadBorrowCartCount() {
        const badge = document.getElementById('borrow-cart-count');
        if (!badge) return;
        
        fetch('{{ route('borrow-cart.count') }}')
            .then(response => response.json())
            .then(data => {
                if (data.count > 0) {
                    badge.textContent = data.count;
                    badge.style.display = 'flex';
                } else {
                    badge.style.display = 'none';
                }
            })
            .catch(error => console.error('Error loading cart count:', error));
    }
</script>
@endauth

<style>
    /* User Actions Dropdown Styling */
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
        font-size: 14px;
        background: none;
        border: none;
        width: 100%;
        text-align: left;
    }

    .user-menu-dropdown .dropdown-item:hover {
        background-color: #f5f5f5;
    }

    .user-menu-dropdown .dropdown-item.logout-btn {
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

    @media (max-width: 768px) {
        .hotline-section {
            display: none;
        }

        .logo-part1 {
            display: none;
        }
    }
</style>