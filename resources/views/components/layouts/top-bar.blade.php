<nav class="navbar navbar-expand navbar-light topbar-premium mb-4 static-top">
    <a class="nav-link pr-3 border-right" href="/cpanel/home">
        <img src="/images/donate.png" style="height:22px;">

        {{-- 🔔 Lonceng Notifikasi WhatsApp Chat --}}
        <a class="nav-link p-0 position-relative text-gray-600 dropdown-toggle no-arrow"
            href="{{ route('whatsapp.conversations.index') }}" title="WhatsApp Chat"
            style="cursor: pointer; text-decoration: none; margin-left: 10px; margin-right: 10px;">
            <i class="fab fa-whatsapp" style="font-size: 1.15rem;"></i>

            @if (($unreadChatCount ?? 0) > 0)
                {{-- TAMBAHKAN id="topbar-unread-badge" DI SINI --}}
                <span id="topbar-unread-badge"
                    class="badge bg-danger text-white rounded-circle position-absolute d-flex align-items-center justify-content-center"
                    style="top: -6px; right: -10px; min-width: 18px; height: 18px; font-size: 0.65rem; font-weight: 700; padding: 2px; border: 2px solid #fff;">
                    {{ $unreadChatCount }}
                </span>
            @endif
        </a>
    </a>

    <ul class="navbar-nav ml-auto align-items-center">
        <li class="nav-item d-none d-md-block">
            <a class="nav-link" href="{{ route('billings.index', ['search_status' => '!LS-!VD']) }}">
                <span class="badge text-white {{ $billingsToShow->count() > 0 ? 'bg-danger' : 'bg-success' }}">
                    {{ $billingsToShow->count() > 0 ? "You have {$billingsToShow->count()} bill in all period" : 'No outstanding bills at this time.' }}
                </span>
            </a>
        </li>

        <li class="nav-item dropdown d-block d-md-none">
            <a class="nav-link dropdown-toggle" href="#" id="mobileMenuDropdown" role="button"
                data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <i class="fas fa-ellipsis-h"></i>
            </a>
            <div class="dropdown-menu dropdown-menu-right shadow animated--grow-in"
                aria-labelledby="mobileMenuDropdown">
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

        <li class="nav-item dropdown no-arrow mx-2">
            <a class="nav-link dropdown-toggle d-flex align-items-center pr-2" href="#" id="languageDropdown"
                role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"
                style="cursor: pointer; text-decoration: none;">

                @if (app()->getLocale() == 'id')
                    <span class="fi fi-id rounded flag-modern-size mr-2 border shadow-sm"></span>
                    <span class="d-none d-sm-inline text-gray-600 small font-weight-bold">ID</span>
                @else
                    <span class="fi fi-gb rounded flag-modern-size mr-2 border shadow-sm"></span>
                    <span class="d-none d-sm-inline text-gray-600 small font-weight-bold">EN</span>
                @endif
            </a>

            <div class="dropdown-menu dropdown-menu-right shadow animated--grow-in p-1"
                aria-labelledby="languageDropdown" style="min-width: 160px;">
                <a class="dropdown-item d-flex align-items-center py-2 {{ app()->getLocale() == 'id' ? 'active text-white' : '' }}"
                    href="{{ route('lang.switch', 'id') }}">
                    <span class="fi fi-id rounded mr-2 border shadow-xs" style="width: 20px; height: 15px;"></span>
                    <span class="small font-weight-600">Indonesia</span>
                </a>

                <a class="dropdown-item d-flex align-items-center py-2 {{ app()->getLocale() == 'en' ? 'active text-white' : '' }}"
                    href="{{ route('lang.switch', 'en') }}">
                    <span class="fi fi-gb rounded mr-2 border shadow-xs" style="width: 20px; height: 15px;"></span>
                    <span class="small font-weight-600">English</span>
                </a>
            </div>
        </li>

        <div class="topbar-divider d-none d-sm-block"></div>

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

    /* Mencegah distorsi rasio bendera bawaan dari css flag-icons */
    .fi {
        background-repeat: no-repeat !important;
    }
</style>
