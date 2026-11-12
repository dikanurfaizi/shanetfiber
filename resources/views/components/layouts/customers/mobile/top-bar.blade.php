<nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow">
    <a class="nav-link" href="/customer">
        <img src="/images/donate.png" alt="Saweria" style="height: 30px !important;">
    </a>

    <!-- Topbar Navbar -->
    <ul class="navbar-nav ms-auto">
        <!-- Nav Item - User Information -->
        <li class="nav-item dropdown no-arrow">
            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown"
                aria-haspopup="true" aria-expanded="false">
                <span
                    class="me-2 d-none d-lg-inline text-gray-600 small">{{ auth()->guard('customer')->user()->name }}</span>
                <img class="img-profile rounded-circle" src="{{ asset('images/undraw_profile.svg') }}">
            </a>

            <!-- Dropdown - User Information -->
            <div class="dropdown-menu dropdown-menu-end shadow animated--grow-in" aria-labelledby="userDropdown">
                <a class="dropdown-item nav-item">
                    <span
                        class="me-2 d-lg-inline text-gray-600 small">{{ auth()->guard('customer')->user()->name }}</span>
                    <img class="img-profile rounded-circle avatar" src="{{ asset('images/undraw_profile.svg') }}">
                </a>
                <a class="dropdown-item" href="{{ route('customer.profile') }}">
                    <i class="fas fa-user fa-sm fa-fw me-2 text-gray-400"></i>
                    {{ __('Profile') }}
                </a>
                <div class="dropdown-divider"></div>
                <a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#logoutModal">
                    <i class="fas fa-sign-out-alt fa-sm fa-fw me-2 text-gray-400"></i>
                    {{ __('Logout') }}
                </a>
            </div>
        </li>

        <!-- Sidenav Toggle Button (Burger Menu) -->
        <button class="btn btn-icon btn-transparent-dark order-1 order-lg-0 me-2 ms-lg-2 me-lg-0 ms-auto"
            id="sidebarToggle">
            <i class="fas fa-ellipsis-v"></i>
        </button>
    </ul>
</nav>
