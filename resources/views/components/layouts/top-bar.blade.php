<nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow">
    <!-- Link Donasi (Pindahkan ke posisi paling kiri) -->
    <a class="nav-link" href="/cpanel/home">
        <img src="/images/donate.png" alt="Saweria" style="height: 30px !important;">
    </a>

    <!-- Topbar Navbar -->
    <ul class="navbar-nav ml-auto">
        <!-- Link Billings (Hanya tampil di layar besar) -->
        <li class="nav-item d-none d-md-block">
            <a class="nav-link" href="{{ route('billings.index', ['search_status' => '!LS-!VD']) }}">
                <span class="badge text-white {{ $billingsToShow->count() > 0 ? 'bg-danger' : 'bg-success' }}">
                    {{ $billingsToShow->count() > 0 ? "You have {$billingsToShow->count()} bill in all period" : 'No outstanding bills at this time.' }}
                </span>
            </a>
        </li>

        <!-- Dropdown Menu untuk layar kecil -->
        <li class="nav-item dropdown d-block d-md-none">
            <a class="nav-link dropdown-toggle" href="#" id="mobileMenuDropdown" role="button"
                data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <i class="fas fa-ellipsis-h"></i>
            </a>
            <div class="dropdown-menu dropdown-menu-right shadow animated--grow-in"
                aria-labelledby="mobileMenuDropdown">
                <!-- Link Billings -->
                <a class="nav-link" href="{{ route('billings.index', ['search_status' => '!LS-!VD']) }}">
                    <span class="badge text-white {{ $billingsToShow->count() > 0 ? 'bg-danger' : 'bg-success' }}">
                        {{ $billingsToShow->count() > 0 ? "You have {$billingsToShow->count()} bill in all period" : 'No outstanding bills at this time.' }}
                    </span>
                </a>
            </div>
        </li>

        <!-- Divider -->
        <div class="topbar-divider d-none d-sm-block"></div>

        <!-- Nav Item - User Information -->
        <li class="nav-item dropdown no-arrow">
            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-toggle="dropdown"
                aria-haspopup="true" aria-expanded="false">
                <span class="mr-2 d-none d-lg-inline text-gray-600 small">{{ Auth::user()->name }}
                    <div>{{ Carbon\Carbon::now()->format('d F Y') }}</div>
                </span>
                <img class="img-profile rounded-circle" src="{{ asset('images/undraw_profile.svg') }}">
            </a>
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
