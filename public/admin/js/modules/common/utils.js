/**
 * Admin Utilities
 * Common functions used across admin pages
 */

class AdminUtils {
    /**
     * Show success toast message
     */
    static showSuccess(message) {
        this.showToast(message, 'success');
    }

    /**
     * Show error toast message
     */
    static showError(message) {
        this.showToast(message, 'error');
    }

    /**
     * Show warning toast message
     */
    static showWarning(message) {
        this.showToast(message, 'warning');
    }

    /**
     * Show info toast message
     */
    static showInfo(message) {
        this.showToast(message, 'info');
    }

    /**
     * Show modern notification using theme's alert styling
     */
    static showToast(message, type = 'info') {
        // Create notification container if it doesn't exist
        let notificationContainer = document.querySelector('.notification-container');
        if (!notificationContainer) {
            notificationContainer = document.createElement('div');
            notificationContainer.className = 'notification-container position-fixed top-0 end-0 p-3';
            notificationContainer.style.zIndex = '9999';
            notificationContainer.style.maxWidth = '400px';
            document.body.appendChild(notificationContainer);
        }

        // Create notification element using theme's alert styling
        const notificationId = 'notification-' + Date.now();
        const notificationElement = document.createElement('div');
        notificationElement.id = notificationId;
        notificationElement.className = 'alert alert-dismissible fade show mb-3';
        notificationElement.setAttribute('role', 'alert');
        notificationElement.style.boxShadow = '0 4px 12px rgba(0,0,0,0.15)';
        notificationElement.style.border = 'none';
        notificationElement.style.borderRadius = '12px';
        notificationElement.style.animation = 'slideInRight 0.3s ease-out';

        // Set notification content based on type
        let iconClass, alertClass, title;
        switch (type) {
            case 'success':
                iconClass = 'bx bxs-badge-check';
                alertClass = 'alert-success';
                title = 'Success';
                break;
            case 'error':
                iconClass = 'bx bxs-error-circle';
                alertClass = 'alert-danger';
                title = 'Error';
                break;
            case 'warning':
                iconClass = 'bx bxs-error-alt';
                alertClass = 'alert-warning';
                title = 'Warning';
                break;
            default:
                iconClass = 'bx bxs-info-circle';
                alertClass = 'alert-info';
                title = 'Info';
        }

        // Add alert class
        notificationElement.classList.add(alertClass);

        // Create modern notification structure
        notificationElement.innerHTML = `
            <div class="d-flex align-items-center">
                <div class="flex-shrink-0 me-3">
                    <i class="${iconClass}" style="font-size: 1.5rem;"></i>
                </div>
                <div class="flex-grow-1">
                    <h6 class="alert-heading mb-1" style="font-weight: 600; font-size: 0.9rem;">${title}</h6>
                    <p class="mb-0" style="font-size: 0.85rem; line-height: 1.4;">${message}</p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close" style="margin-left: 1rem;"></button>
            </div>
        `;

        // Add notification to container
        notificationContainer.appendChild(notificationElement);

        // Auto-remove notification after 5 seconds
        setTimeout(() => {
            if (notificationElement && notificationElement.parentNode) {
                notificationElement.style.animation = 'slideOutRight 0.3s ease-in';
                setTimeout(() => {
                    if (notificationElement && notificationElement.parentNode) {
                        notificationElement.remove();
                    }
                }, 300);
            }
        }, 5000);

        // Add click to dismiss functionality
        notificationElement.addEventListener('click', function (e) {
            if (e.target.classList.contains('btn-close') || e.target.closest('.btn-close')) {
                notificationElement.style.animation = 'slideOutRight 0.3s ease-in';
                setTimeout(() => {
                    if (notificationElement && notificationElement.parentNode) {
                        notificationElement.remove();
                    }
                }, 300);
            }
        });
    }

    /**
     * Get CSRF token
     */
    static getCsrfToken() {
        return $('meta[name="csrf-token"]').attr('content');
    }

    /**
     * Clear validation errors
     */
    static clearValidationErrors() {
        $('.is-invalid').removeClass('is-invalid');
        $('.invalid-feedback').remove();
        $('.product-image-error').hide().text('');
        $('.product-image-upload').css('border-color', '');
    }

    /**
     * Show button loading state
     */
    static showButtonLoading(button, text = 'Loading...') {
        const $btn = $(button);
        $btn.prop('disabled', true);
        $btn.data('original-text', $btn.html());
        $btn.html(`<span class="spinner-border spinner-border-sm me-2" role="status"></span>${text}`);
    }
    /**
     * Hide button loading state
     */
    static hideButtonLoading(button) {
        const $btn = $(button);
        $btn.prop('disabled', false);
        $btn.html($btn.data('original-text'));
    }

    /**
     * Make AJAX request with error handling
     */
    static ajaxRequest(options) {
        const defaultOptions = {
            headers: {
                'X-CSRF-TOKEN': this.getCsrfToken()
            },
            error: function (xhr, status, error) {
                console.error('AJAX Error:', xhr, status, error);
                AdminUtils.showError('An error occurred. Please try again.');
            }
        };

        const mergedOptions = { ...defaultOptions, ...options };
        return $.ajax(mergedOptions);
    }
}

// Make AdminUtils available globally
window.AdminUtils = AdminUtils;