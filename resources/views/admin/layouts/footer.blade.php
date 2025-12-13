<footer class="footer bg-light">
    <div
        class="container-fluid d-flex flex-md-row flex-column justify-content-center align-items-md-center gap-1 container-p-x py-4">

        <div>
            © {{ date('Y') }} , All rights reserved by
            <a href="javascript:void(0)" class="footer-link fw-bolder">
                {{ env('APP_NAME') }}
            </a>
            <!-- <li>
                <a class="dropdown-item" href="{{ route('logout') }}"
                    onclick="event.preventDefault();
                              document.getElementById('logout-form').submit();">
                    <i class="icon-base bx bx-power-off icon-md me-3"></i><span>{{ __('Logout') }}</span>
                </a>

                <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                    @csrf
                </form>
            </li> -->
        </div>
    </div>
</footer>
