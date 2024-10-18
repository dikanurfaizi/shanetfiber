<nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow">
    <!-- Topbar Navbar -->
    <ul class="navbar-nav ml-auto">
        <!-- Sidenav Toggle Button-->
        <button class="btn btn-icon btn-transparent-dark order-1 order-lg-0 me-2 ms-lg-2 me-lg-0" id="sidebarToggle">
            <i data-feather="menu"></i>
        </button>
        @if ($billings->where('status', '!=', 'LS')->count() > 0)
        <li class="nav-item">
            <a class="nav-link" href="{{ route('billings.index', 'search_status=!LS') }}">
                <span class="badge bg-danger text-white">You have {{$billings->where('status', '!=', 'LS')->count()}} bill in all periode</span>
            </a>
        </li>
        @endif
        <li class="nav-item">
            <a class="nav-link" href="{{ route('home') }}">
                <img src="/images/nih_buat_jajan.png" alt="Nih buat jajan" style="height: 30px !important;">
            </a>
        </li>
        <div class="topbar-divider d-none d-sm-block"></div>
        <!-- Nav Item - User Information -->
        <li class="nav-item dropdown no-arrow">
            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <span class="mr-2 d-none d-lg-inline text-gray-600 small">{{ Auth::user()->name }}
                    <div>{{ Carbon\Carbon::now()->format('d F Y') }}</div>
                </span>

                <img class="img-profile rounded-circle" src="{{ asset('images/undraw_profile.svg') }}">
            </a><!-- Sidebar Toggle (Topbar) -->

            <!-- Dropdown - User Information -->
            <div class="dropdown-menu dropdown-menu-right shadow animated--grow-in" aria-labelledby="userDropdown">
                <a class="dropdown-item" href="{{ route('profile.show') }}">
                    <i class="fas fa-user fa-sm fa-fw mr-2 text-gray-400"></i>
                    {{ __('Profile') }}
                </a>
                <div class="dropdown-divider"></div>
                <a class="dropdown-item" href="#" data-toggle="modal" data-target="#logoutModal">
                    <i class="fas fa-sign-out-alt fa-sm fa-fw mr-2 text-gray-400"></i>
                    {{ __('Logout') }}
                </a>

            </div>
        </li>

    </ul>

</nav>