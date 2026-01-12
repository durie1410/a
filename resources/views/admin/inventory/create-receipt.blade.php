@extends('layouts.admin')

@section('title', 'Tạo Phiếu Nhập Kho - Admin')

@section('content')
<!-- Page Header -->
<div class="page-header">
    <div>
        <h1 class="page-title">
            <i class="fas fa-boxes" style="color: #22c55e;"></i>
            Tạo Phiếu Nhập Kho
        </h1>
        <p class="page-subtitle">Nhập thông tin để tạo phiếu nhập kho mới - Có thể thêm nhiều sách cùng lúc</p>
    </div>
</div>

@if($errors->any())
    <div class="alert alert-danger">
        <ul>
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="card" style="background: white; border: 1px solid #e5e7eb; border-radius: 12px; padding: 25px;">
    <form action="{{ route('admin.inventory.receipts.store') }}" method="POST" id="receiptForm">
        @csrf
        
        <!-- Thông tin phiếu nhập -->
        <div class="row" style="margin-bottom: 25px; padding-bottom: 20px; border-bottom: 2px solid #e5e7eb;">
            <div class="col-md-6">
                <div class="mb-3">
                    <label class="form-label"><strong>Số phiếu</strong></label>
                    <input type="text" class="form-control" value="{{ $receiptNumber }}" readonly style="background: #f3f4f6;">
                    <small class="form-text text-muted">Số phiếu được tạo tự động</small>
                </div>
            </div>
            <div class="col-md-6">
                <div class="mb-3">
                    <label class="form-label"><strong>Ngày nhập</strong> <span class="text-danger">*</span></label>
                    <input type="date" name="receipt_date" class="form-control" value="{{ old('receipt_date', date('Y-m-d')) }}" required>
                </div>
            </div>
        </div>

        <!-- Thông tin chung -->
        <div class="row" style="margin-bottom: 25px; padding-bottom: 20px; border-bottom: 2px solid #e5e7eb;">
            <div class="col-md-6">
                <div class="mb-3">
                    <label class="form-label"><strong>Nhà cung cấp</strong></label>
                    <input type="text" name="supplier" class="form-control" value="{{ old('supplier') }}" placeholder="Nhập tên nhà cung cấp">
                </div>
            </div>
            <div class="col-md-6">
                <div class="mb-3">
                    <label class="form-label"><strong>Ghi chú</strong></label>
                    <textarea name="notes" class="form-control" rows="2" placeholder="Ghi chú chung cho phiếu nhập...">{{ old('notes') }}</textarea>
                </div>
            </div>
        </div>

        <!-- Bảng sách -->
        <div style="margin-bottom: 25px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                <h4 style="margin: 0; font-weight: 600; color: #1f2937;">
                    <i class="fas fa-book" style="color: #22c55e;"></i> Danh sách sách nhập kho
                </h4>
                <button type="button" class="btn btn-primary" onclick="openBookSelectionModal()" style="background: #22c55e; color: white; border: none; padding: 10px 20px; border-radius: 8px;">
                    <i class="fas fa-plus"></i> Thêm sách từ quản lý sách
                </button>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered" id="booksTable" style="background: white;">
                    <thead style="background: #f9fafb;">
                        <tr>
                            <th style="width: 5%;">STT</th>
                            <th style="width: 25%;">Tên sách</th>
                            <th style="width: 15%;">Thể loại</th>
                            <th style="width: 10%;">Số lượng</th>
                            <th style="width: 15%;">Vị trí</th>
                            <th style="width: 10%;">Loại lưu trữ</th>
                            <th style="width: 10%;">Giá đơn vị</th>
                            <th style="width: 10%;">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody id="booksTableBody">
                        <!-- Sách sẽ được thêm vào đây bằng JavaScript -->
                    </tbody>
                </table>
            </div>

            <div id="emptyBooksMessage" style="text-align: center; padding: 40px; color: #9ca3af; border: 2px dashed #e5e7eb; border-radius: 8px; margin-top: 15px;">
                <i class="fas fa-book-open" style="font-size: 48px; margin-bottom: 15px; opacity: 0.5;"></i>
                <p style="margin: 0; font-size: 16px;">Chưa có sách nào. Vui lòng nhấn "Thêm sách từ quản lý sách" để thêm sách vào phiếu nhập.</p>
            </div>
        </div>

        <div class="d-flex justify-content-between" style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #e5e7eb;">
            <a href="{{ route('admin.inventory.receipts') }}" class="btn btn-secondary" style="background: white; color: #1f2937; border: 1px solid #e5e7eb; padding: 12px 20px; border-radius: 10px; font-weight: 500; display: inline-flex; align-items: center; gap: 8px;">
                <i class="fas fa-times"></i> Hủy
            </a>
            <button type="submit" class="btn btn-primary" style="background: #22c55e; color: white; border: none; padding: 12px 20px; border-radius: 10px; font-weight: 500; display: inline-flex; align-items: center; gap: 8px;">
                <i class="fas fa-save"></i> Tạo Phiếu Nhập
            </button>
        </div>
    </form>
</div>

<!-- Modal chọn sách từ quản lý sách -->
<div class="modal fade" id="bookSelectionModal" tabindex="-1" role="dialog" aria-labelledby="bookSelectionModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document" style="max-width: 90%;">
        <div class="modal-content">
            <div class="modal-header" style="background: #22c55e; color: white;">
                <h5 class="modal-title" id="bookSelectionModalLabel">
                    <i class="fas fa-book"></i> Chọn sách từ quản lý sách
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="color: white; opacity: 1;">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" style="max-height: 70vh; overflow-y: auto;">
                <!-- Tìm kiếm và lọc -->
                <div style="margin-bottom: 20px; padding: 15px; background: #f9fafb; border-radius: 8px;">
                    <div class="row">
                        <div class="col-md-4">
                            <input type="text" id="searchBookKeyword" class="form-control" placeholder="Tìm theo tên sách hoặc tác giả..." onkeyup="searchBooks()">
                        </div>
                        <div class="col-md-3">
                            <select id="searchBookCategory" class="form-control" onchange="searchBooks()">
                                <option value="">-- Tất cả thể loại --</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}">{{ $category->ten_the_loai }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <select id="searchBookPublisher" class="form-control" onchange="searchBooks()">
                                <option value="">-- Tất cả nhà xuất bản --</option>
                                @foreach($publishers as $publisher)
                                    <option value="{{ $publisher->id }}">{{ $publisher->ten_nha_xuat_ban }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <button type="button" class="btn btn-primary btn-block" onclick="searchBooks()" style="background: #22c55e; border: none;">
                                <i class="fas fa-search"></i> Tìm
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Danh sách sách -->
                <div id="booksListContainer">
                    <div style="text-align: center; padding: 40px; color: #9ca3af;">
                        <i class="fas fa-spinner fa-spin" style="font-size: 32px;"></i>
                        <p>Đang tải danh sách sách...</p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Đóng</button>
                <button type="button" class="btn btn-primary" onclick="addSelectedBooks()" style="background: #22c55e; border: none;">
                    <i class="fas fa-check"></i> Thêm sách đã chọn
                </button>
            </div>
        </div>
    </div>
</div>

<script>
let selectedBooks = []; // Lưu danh sách sách đã chọn trong modal
let addedBookIds = new Set(); // Lưu các book_id đã thêm vào bảng để tránh trùng lặp
let bookCounter = 0; // Đếm số sách đã thêm

// Mở modal chọn sách
function openBookSelectionModal() {
    const modal = document.getElementById('bookSelectionModal');
    if (modal) {
        // Sử dụng Bootstrap modal nếu có
        if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
            const bsModal = new bootstrap.Modal(modal);
            bsModal.show();
        } else if (typeof $ !== 'undefined') {
            $('#bookSelectionModal').modal('show');
        } else {
            // Fallback: hiển thị modal thủ công
            modal.style.display = 'block';
            modal.classList.add('show');
            document.body.classList.add('modal-open');
            const backdrop = document.createElement('div');
            backdrop.className = 'modal-backdrop fade show';
            document.body.appendChild(backdrop);
        }
        loadBooks();
    }
}

// Tải danh sách sách (đã được thay thế bởi loadBooksFromServer)
function loadBooks(keyword = '', categoryId = '', publisherId = '') {
    loadBooksFromServer(keyword, categoryId, publisherId);
}

// Tải sách từ API
function fetchBooksFromAPI(keyword = '', categoryId = '', publisherId = '') {
    const container = document.getElementById('booksListContainer');
    
    // Sử dụng route API
    const url = `{{ route('admin.books.api.list') }}?keyword=${encodeURIComponent(keyword)}&category_id=${categoryId}&publisher_id=${publisherId}`;
    
    fetch(url)
        .then(response => response.json())
        .then(data => {
            if (data.success && data.books && data.books.length > 0) {
                renderBooksList(data.books);
            } else {
                container.innerHTML = '<div style="text-align: center; padding: 40px; color: #9ca3af;"><i class="fas fa-book-open"></i><p>Không tìm thấy sách nào</p></div>';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            // Fallback: sử dụng dữ liệu từ server
            loadBooksFromServer(keyword, categoryId, publisherId);
        });
}

// Tải sách từ server (fallback)
function loadBooksFromServer(keyword = '', categoryId = '', publisherId = '') {
    const container = document.getElementById('booksListContainer');
    const books = @json($allBooks ?? $books);
    
    let filteredBooks = books;
    
    if (keyword) {
        const lowerKeyword = keyword.toLowerCase();
        filteredBooks = filteredBooks.filter(book => 
            book.ten_sach.toLowerCase().includes(lowerKeyword) ||
            (book.tac_gia && book.tac_gia.toLowerCase().includes(lowerKeyword))
        );
    }
    
    if (categoryId) {
        filteredBooks = filteredBooks.filter(book => book.category_id == categoryId);
    }
    
    if (publisherId) {
        filteredBooks = filteredBooks.filter(book => book.nha_xuat_ban_id == publisherId);
    }
    
    if (filteredBooks.length > 0) {
        renderBooksList(filteredBooks);
    } else {
        container.innerHTML = '<div style="text-align: center; padding: 40px; color: #9ca3af;"><i class="fas fa-book-open"></i><p>Không tìm thấy sách nào</p></div>';
    }
}

// Hiển thị danh sách sách
function renderBooksList(books) {
    const container = document.getElementById('booksListContainer');
    
    if (books.length === 0) {
        container.innerHTML = '<div style="text-align: center; padding: 40px; color: #9ca3af;"><i class="fas fa-book-open"></i><p>Không tìm thấy sách nào</p></div>';
        return;
    }
    
    let html = '<div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 15px;">';
    
    books.forEach(book => {
        const isSelected = selectedBooks.some(b => b.id === book.id);
        const isAdded = addedBookIds.has(book.id);
        const categoryName = book.category ? book.category.ten_the_loai : 'Chưa phân loại';
        const publisherName = book.publisher ? book.publisher.ten_nha_xuat_ban : 'Chưa có';
        
        html += `
            <div class="book-card" style="border: 2px solid ${isSelected ? '#22c55e' : isAdded ? '#fbbf24' : '#e5e7eb'}; border-radius: 8px; padding: 15px; background: white; cursor: pointer; transition: all 0.3s;" 
                 onclick="toggleBookSelection(${book.id})"
                 data-book-id="${book.id}">
                <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 10px;">
                    <div style="flex: 1;">
                        <h6 style="margin: 0; font-weight: 600; color: #1f2937; font-size: 14px;">${book.ten_sach || 'Chưa có tên'}</h6>
                        <p style="margin: 5px 0 0 0; color: #6b7280; font-size: 12px;">${book.tac_gia || 'Chưa có tác giả'}</p>
                    </div>
                    <input type="checkbox" ${isSelected ? 'checked' : ''} ${isAdded ? 'disabled' : ''} 
                           onclick="event.stopPropagation(); toggleBookSelection(${book.id})"
                           style="margin-left: 10px; width: 20px; height: 20px; cursor: pointer;">
                </div>
                <div style="margin-top: 10px; padding-top: 10px; border-top: 1px solid #e5e7eb;">
                    <div style="display: flex; justify-content: space-between; font-size: 12px; color: #6b7280;">
                        <span><i class="fas fa-tag"></i> ${categoryName}</span>
                        <span><i class="fas fa-building"></i> ${publisherName}</span>
                    </div>
                    ${isAdded ? '<div style="margin-top: 8px; padding: 5px; background: #fef3c7; border-radius: 4px; text-align: center; font-size: 11px; color: #92400e;"><i class="fas fa-info-circle"></i> Đã thêm vào phiếu</div>' : ''}
                </div>
            </div>
        `;
    });
    
    html += '</div>';
    container.innerHTML = html;
}

// Toggle chọn sách
function toggleBookSelection(bookId) {
    const allBooks = @json($allBooks ?? $books);
    const book = allBooks.find(b => b.id === bookId);
    if (!book) return;
    
    if (addedBookIds.has(bookId)) {
        alert('Sách này đã được thêm vào phiếu nhập. Vui lòng xóa khỏi bảng trước khi thêm lại.');
        return;
    }
    
    const index = selectedBooks.findIndex(b => b.id === bookId);
    if (index > -1) {
        selectedBooks.splice(index, 1);
    } else {
        selectedBooks.push({
            id: book.id,
            ten_sach: book.ten_sach,
            tac_gia: book.tac_gia,
            category: book.category,
            publisher: book.publisher,
            gia: book.gia || 0
        });
    }
    
    // Cập nhật UI
    const card = document.querySelector(`[data-book-id="${bookId}"]`);
    if (card) {
        const checkbox = card.querySelector('input[type="checkbox"]');
        if (checkbox) {
            checkbox.checked = selectedBooks.some(b => b.id === bookId);
        }
        card.style.borderColor = checkbox.checked ? '#22c55e' : '#e5e7eb';
    }
}

// Thêm sách đã chọn vào bảng
function addSelectedBooks() {
    if (selectedBooks.length === 0) {
        alert('Vui lòng chọn ít nhất một cuốn sách!');
        return;
    }
    
    selectedBooks.forEach(book => {
        if (!addedBookIds.has(book.id)) {
            addBookToTable(book);
        }
    });
    
    selectedBooks = [];
    $('#bookSelectionModal').modal('hide');
}

// Thêm sách vào bảng
function addBookToTable(book) {
    if (addedBookIds.has(book.id)) {
        return; // Đã thêm rồi
    }
    
    bookCounter++;
    addedBookIds.add(book.id);
    
    const tbody = document.getElementById('booksTableBody');
    const emptyMessage = document.getElementById('emptyBooksMessage');
    
    if (emptyMessage) {
        emptyMessage.style.display = 'none';
    }
    
    // Lấy vị trí mặc định từ locations
    const locationsInStock = @json($locationsInStock);
    const defaultLocation = locationsInStock.length > 0 ? locationsInStock[0].location : 'Kệ 1, Tầng 1';
    
    const row = document.createElement('tr');
    row.setAttribute('data-book-id', book.id);
    row.innerHTML = `
        <td>${bookCounter}</td>
        <td>
            <strong>${book.ten_sach || 'Chưa có tên'}</strong><br>
            <small style="color: #6b7280;">${book.tac_gia || 'Chưa có tác giả'}</small>
            <input type="hidden" name="books[${bookCounter}][book_id]" value="${book.id}">
        </td>
        <td>${book.category ? book.category.ten_the_loai : 'Chưa phân loại'}</td>
        <td>
            <input type="number" name="books[${bookCounter}][quantity]" class="form-control" value="1" min="1" required style="width: 80px;" onchange="updateTotalPrice(${bookCounter})">
        </td>
        <td>
            <select name="books[${bookCounter}][storage_location]" class="form-control" required>
                <option value="">-- Chọn vị trí --</option>
                @foreach($locationsInStock as $loc)
                    <option value="{{ $loc['location'] }}" ${defaultLocation === '{{ $loc['location'] }}' ? 'selected' : ''}>
                        {{ $loc['location'] }}
                    </option>
                @endforeach
            </select>
        </td>
        <td>
            <select name="books[${bookCounter}][storage_type]" class="form-control" required>
                <option value="Kho" selected>Kho</option>
                <option value="Trung bay">Trưng bày</option>
            </select>
        </td>
        <td>
            <input type="number" name="books[${bookCounter}][unit_price]" class="form-control" value="${book.gia || 0}" min="0" step="1000" style="width: 120px;" onchange="updateTotalPrice(${bookCounter})">
        </td>
        <td>
            <button type="button" class="btn btn-sm btn-danger" onclick="removeBookFromTable(${book.id})" style="background: #ef4444; border: none;">
                <i class="fas fa-trash"></i>
            </button>
        </td>
    `;
    
    tbody.appendChild(row);
}

// Xóa sách khỏi bảng
function removeBookFromTable(bookId) {
    const row = document.querySelector(`tr[data-book-id="${bookId}"]`);
    if (row) {
        row.remove();
        addedBookIds.delete(bookId);
        
        // Nếu không còn sách nào, hiển thị thông báo
        const tbody = document.getElementById('booksTableBody');
        if (tbody.children.length === 0) {
            const emptyMessage = document.getElementById('emptyBooksMessage');
            if (emptyMessage) {
                emptyMessage.style.display = 'block';
            }
        }
        
        // Cập nhật lại STT
        updateRowNumbers();
    }
}

// Cập nhật số thứ tự
function updateRowNumbers() {
    const rows = document.querySelectorAll('#booksTableBody tr');
    rows.forEach((row, index) => {
        row.querySelector('td:first-child').textContent = index + 1;
    });
    bookCounter = rows.length;
}

// Cập nhật tổng giá (nếu cần)
function updateTotalPrice(index) {
    // Có thể thêm logic tính tổng giá nếu cần
}

// Tìm kiếm sách
function searchBooks() {
    const keyword = document.getElementById('searchBookKeyword').value;
    const categoryId = document.getElementById('searchBookCategory').value;
    const publisherId = document.getElementById('searchBookPublisher').value;
    loadBooksFromServer(keyword, categoryId, publisherId);
}

// Validate form trước khi submit
document.getElementById('receiptForm').addEventListener('submit', function(e) {
    if (addedBookIds.size === 0) {
        e.preventDefault();
        alert('Vui lòng thêm ít nhất một cuốn sách vào phiếu nhập!');
        return false;
    }
    
    // Validate từng sách
    const rows = document.querySelectorAll('#booksTableBody tr');
    let isValid = true;
    rows.forEach(row => {
        const quantity = row.querySelector('input[name*="[quantity]"]').value;
        const location = row.querySelector('select[name*="[storage_location]"]').value;
        
        if (!quantity || quantity < 1) {
            isValid = false;
        }
        if (!location) {
            isValid = false;
        }
    });
    
    if (!isValid) {
        e.preventDefault();
        alert('Vui lòng điền đầy đủ thông tin cho tất cả sách (số lượng và vị trí)!');
        return false;
    }
});

// Khởi tạo khi trang load
document.addEventListener('DOMContentLoaded', function() {
    // Khi mở modal mới tải sách
    const modal = document.getElementById('bookSelectionModal');
    if (modal) {
        modal.addEventListener('show.bs.modal', function() {
            loadBooksFromServer();
        });
        
        // Fallback cho jQuery nếu có
        if (typeof $ !== 'undefined') {
            $('#bookSelectionModal').on('show.bs.modal', function() {
                loadBooksFromServer();
            });
        }
    }
});
</script>

<style>
.book-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}
</style>
@endsection
