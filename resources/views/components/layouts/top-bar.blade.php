<nav class="navbar navbar-expand navbar-light topbar-premium mb-4 static-top">
    <!-- Logo & WhatsApp Chat Button Dipisah dengan Benar -->
    <div class="d-flex align-items-center">
        <a class="nav-link pe-3 border-end" href="/cpanel/home">
            <img src="/images/donate.png" style="height:22px;" alt="Logo">
        </a>

        {{-- 🔔 Lonceng Notifikasi WhatsApp Chat --}}
        <a class="nav-link p-0 position-relative text-secondary dropdown-toggle no-arrow ms-3"
            href="{{ route('whatsapp.conversations.index') }}" title="WhatsApp Chat"
            style="cursor: pointer; text-decoration: none;">
            <i class="fab fa-whatsapp" style="font-size: 1.15rem;"></i>

            @if (($unreadChatCount ?? 0) > 0)
                <span id="topbar-unread-badge"
                    class="badge bg-danger text-white rounded-circle position-absolute d-flex align-items-center justify-content-center"
                    style="top: -6px; right: -10px; min-width: 18px; height: 18px; font-size: 0.65rem; font-weight: 700; padding: 2px; border: 2px solid #fff;">
                    {{ $unreadChatCount }}
                </span>
            @endif
        </a>
    </div>

    <ul class="navbar-nav ms-auto align-items-center">
        <li class="nav-item d-none d-md-block">
            <a class="nav-link" href="{{ route('billings.index', ['search_status' => '!LS-!VD']) }}">
                <span class="badge text-white {{ $billingsToShow->count() > 0 ? 'bg-danger' : 'bg-success' }}">
                    {{ $billingsToShow->count() > 0 ? "You have {$billingsToShow->count()} bill in all period" : 'No outstanding bills at this time.' }}
                </span>
            </a>
        </li>

        <li class="nav-item dropdown d-block d-md-none">
            <a class="nav-link dropdown-toggle" href="#" id="mobileMenuDropdown" role="button"
                data-bs-toggle="dropdown" aria-expanded="false">
                <i class="fas fa-ellipsis-h"></i>
            </a>
            <ul class="dropdown-menu dropdown-menu-end shadow animated--grow-in" aria-labelledby="mobileMenuDropdown">
                <li>
                    <a class="dropdown-item d-flex align-items-center"
                        href="{{ route('billings.index', ['search_status' => '!LS-!VD']) }}">
                        <span class="me-2 text-muted small">Outstanding</span>
                        <span
                            class="badge rounded-pill {{ $billingsToShow->count() > 0 ? 'bg-danger' : 'bg-success' }}">
                            {{ $billingsToShow->count() }}
                        </span>
                    </a>
                </li>
            </ul>
        </li>

        <li class="nav-item dropdown no-arrow mx-2">
            <a class="nav-link dropdown-toggle d-flex align-items-center pe-2" href="#" id="languageDropdown"
                role="button" data-bs-toggle="dropdown" aria-expanded="false"
                style="cursor: pointer; text-decoration: none;">

                @if (app()->getLocale() == 'id')
                    <span class="fi fi-id rounded flag-modern-size me-2 border shadow-sm"></span>
                    <span class="d-none d-sm-inline text-secondary small fw-bold">ID</span>
                @else
                    <span class="fi fi-gb rounded flag-modern-size me-2 border shadow-sm"></span>
                    <span class="d-none d-sm-inline text-secondary small fw-bold">EN</span>
                @endif
            </a>

            <ul class="dropdown-menu dropdown-menu-end shadow animated--grow-in p-1" aria-labelledby="languageDropdown"
                style="min-width: 160px;">
                <li>
                    <a class="dropdown-item d-flex align-items-center py-2 {{ app()->getLocale() == 'id' ? 'active text-white' : '' }}"
                        href="{{ route('lang.switch', 'id') }}">
                        <span class="fi fi-id rounded me-2 border shadow-xs" style="width: 20px; height: 15px;"></span>
                        <span class="small fw-semibold">Indonesia</span>
                    </a>
                </li>
                <li>
                    <a class="dropdown-item d-flex align-items-center py-2 {{ app()->getLocale() == 'en' ? 'active text-white' : '' }}"
                        href="{{ route('lang.switch', 'en') }}">
                        <span class="fi fi-gb rounded me-2 border shadow-xs" style="width: 20px; height: 15px;"></span>
                        <span class="small fw-semibold">English</span>
                    </a>
                </li>
            </ul>
        </li>

        <li class="nav-item d-none d-sm-block px-2">
            <div class="topbar-divider border-start h-50 my-auto"></div>
        </li>

        <li class="nav-item dropdown no-arrow">
            <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" id="userDropdown"
                role="button" data-bs-toggle="dropdown" aria-expanded="false">

                <div class="me-3 text-end d-none d-lg-block">
                    <div class="fw-semibold text-dark small">
                        {{ Auth::user()->name }}
                    </div>
                    <div class="text-muted" style="font-size: 0.75rem;">
                        {{ Carbon\Carbon::now()->format('d F Y') }}
                    </div>
                </div>

                <img class="img-profile rounded-circle" src="{{ asset('images/undraw_profile.svg') }}" alt="Profile"
                    style="width: 32px; height: 32px; object-fit: cover;">
            </a>
            <ul class="dropdown-menu dropdown-menu-end shadow animated--grow-in" aria-labelledby="userDropdown">
                <li>
                    <a class="dropdown-item" href="{{ route('profile.show') }}">
                        <i class="fas fa-user fa-sm fa-fw me-2 text-gray-400"></i>
                        {{ __('Profile') }}
                    </a>
                </li>
                <li>
                    <hr class="dropdown-divider">
                </li>
                <li>
                    <a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#logoutModal">
                        <i class="fas fa-sign-out-alt fa-sm fa-fw me-2 text-gray-400"></i>
                        {{ __('Logout') }}
                    </a>
                </li>
            </ul>
        </li>
    </ul>
</nav>

<style>
    .flag-modern-size {
        width: 22px !important;
        height: 16px !important;
        background-size: cover !important;
        background-position: center !important;
        display: inline-block;
    }

    .shadow-xs {
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.075) !important;
    }

    .fi {
        background-repeat: no-repeat !important;
    }
</style>
