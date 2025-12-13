<div class="position-relative">
    @if (session('success'))
        <div class="bs-toast toast fade show bg-success position-absolute top-0 end-0 mt-4 me-4" role="alert"
            aria-live="assertive" aria-atomic="true">
            <div class="toast-header">
                <i class='bx bxs-badge-check'></i>
                <div class="me-auto fw-semibold">Success</div>
                {{-- <small>11 mins ago</small> --}}
                <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
            <div class="toast-body">
                {{ session('success') }}
            </div>
        </div>
    @endif

    @if (session('error'))
        <div class="bs-toast toast fade show bg-danger position-absolute top-0 end-0 mt-4 me-4" role="alert"
            aria-live="assertive" aria-atomic="true">
            <div class="toast-header">
                <i class='bx bxs-error-circle'></i>
                <div class="me-auto fw-semibold">Error</div>
                {{-- <small>11 mins ago</small> --}}
                <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
            <div class="toast-body">
                {{ session('error') }}
            </div>
        </div>
    @endif

    @if (session('info'))
        <div class="bs-toast toast fade show bg-primary position-absolute top-0 end-0 mt-4 me-4" role="alert"
            aria-live="assertive" aria-atomic="true">
            <div class="toast-header">
                <i class='bx bxs-info-circle'></i>
                <div class="me-auto fw-semibold">Info</div>
                {{-- <small>11 mins ago</small> --}}
                <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
            <div class="toast-body">
                {{ session('info') }}
            </div>
        </div>
    @endif
</div>
