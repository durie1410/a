<div class="modal fade" id="globalNotificationModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 16px; overflow: hidden;">
            <div class="modal-header border-0 pb-0 justify-content-center">
                <div id="modalIconContainer" class="mt-3 mb-2">
                    <!-- Icon will be inserted here via JS -->
                </div>
            </div>
            <div class="modal-body text-center pt-0 px-4">
                <h5 class="modal-title fw-bold mb-2" id="globalModalTitle" style="font-size: 1.25rem;">Thông báo</h5>
                <p class="text-muted mb-4" id="globalModalMessage" style="font-size: 1rem; line-height: 1.6;">
                    Nội dung thông báo
                </p>
                <div class="d-grid gap-2 mb-3">
                    <button type="button" class="btn btn-primary fw-bold py-2" data-bs-dismiss="modal"
                        style="border-radius: 10px;">
                        Đã hiểu
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const modalElement = document.getElementById('globalNotificationModal');
        const modalTitle = document.getElementById('globalModalTitle');
        const modalMessage = document.getElementById('globalModalMessage');
        const modalIconContainer = document.getElementById('modalIconContainer');

        let bsModal = null;

        // Function to show modal
        window.showGlobalModal = function (title, message, type = 'info') {
            if (!bsModal && typeof bootstrap !== 'undefined') {
                bsModal = new bootstrap.Modal(modalElement);
            }

            modalTitle.textContent = title;
            modalMessage.innerHTML = message;

            // Set icon and color based on type
            let iconHtml = '';
            let colorClass = '';

            switch (type) {
                case 'success':
                    iconHtml = '<i class="fas fa-check-circle" style="font-size: 3.5rem;"></i>';
                    colorClass = 'text-success';
                    break;
                case 'error':
                case 'danger':
                    iconHtml = '<i class="fas fa-times-circle" style="font-size: 3.5rem;"></i>';
                    colorClass = 'text-danger';
                    break;
                case 'warning':
                    iconHtml = '<i class="fas fa-exclamation-circle" style="font-size: 3.5rem;"></i>';
                    colorClass = 'text-warning';
                    break;
                default:
                    iconHtml = '<i class="fas fa-info-circle" style="font-size: 3.5rem;"></i>';
                    colorClass = 'text-primary';
            }

            modalIconContainer.className = `mt-3 mb-2 ${colorClass}`;
            modalIconContainer.innerHTML = iconHtml;

            if (bsModal) {
                bsModal.show();
            } else {
                console.warn('Bootstrap Modal not initialized. Make sure Bootstrap 5 JS is loaded.');
            }
        };

        // Check for session flash messages
        @if(session('success'))
            showGlobalModal('Thành công', "{!! addslashes(session('success')) !!}", 'success');
        @endif

        @if(session('error'))
            showGlobalModal('Có lỗi xảy ra', "{!! addslashes(session('error')) !!}", 'error');
        @endif

        @if(session('warning'))
            showGlobalModal('Cảnh báo', "{!! addslashes(session('warning')) !!}", 'warning');
        @endif

        @if(session('info'))
            showGlobalModal('Thông báo', "{!! addslashes(session('info')) !!}", 'info');
        @endif

            // Handle Validation Errors
            @if($errors->any())
                let errorMsg = '<ul class="text-start d-inline-block">';
                @foreach($errors->all() as $error)
                    errorMsg += '<li>{{ $error }}</li>';
                @endforeach
                errorMsg += '</ul>';
                showGlobalModal('Vui lòng kiểm tra lại', errorMsg, 'error');
            @endif
    });
</script>