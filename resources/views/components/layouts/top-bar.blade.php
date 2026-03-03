<nav class="navbar navbar-expand navbar-light topbar-premium mb-4 static-top">
    <!-- Link Donasi (Pindahkan ke posisi paling kiri) -->
    <a class="nav-link pr-3 border-right" href="/cpanel/home">
        <img src="/images/donate.png" style="height:22px;">
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
                <a class="nav-link d-flex align-items-center"
                    href="{{ route('billings.index', ['search_status' => '!LS-!VD']) }}">
                    <span class="mr-2 text-muted small">Outstanding</span>
                    <span
                        class="badge badge-pill {{ $billingsToShow->count() > 0 ? 'badge-danger' : 'badge-success' }}">
                        {{ $billingsToShow->count() }}
                    </span>
                </a>
            </div>
        </li>

        <!-- Nav Item - User Information -->
        <li class="nav-item dropdown no-arrow">
            <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" id="userDropdown"
                role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">

                <div class="mr-3 text-right d-none d-lg-block">
                    <div class="font-weight-600 text-dark small">
                        {{ Auth::user()->name }}
                    </div>
                    <div class="text-muted" style="font-size: 0.75rem;">
                        {{ Carbon\Carbon::now()->format('d F Y') }}
                    </div>
                </div>

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
