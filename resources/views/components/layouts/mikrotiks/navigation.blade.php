<!-- Nav Item - Dashboard -->
<li class="nav-item @if(request()->routeIs('home')) active @endif">
    <a class="nav-link" href="{{ route('home') }}">
        <i class="fas fa-home"></i>
        <span>{{ __('Home') }}</span>
    </a>
</li>
<li class="nav-item {{ Request::is('server/*') ? 'active' : '' }}">
    <a class="nav-link" href="{{ route('mikrotiks.show', ['mikrotik' => request()->mikrotik]) }}">
        <i class="fas fa-fw fa-tachometer-alt"></i>
        <span>{{ __('dashboard.dashboard') }}</span></a>
</li>


<!-- Divider -->
<hr class="sidebar-divider">

<!-- Nav Item - Pages Collapse Menu -->
<li class="nav-item {{ Request::is('server/*/ppp*') ? 'active' : '' }}">
    <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapsePPP" aria-expanded="true" aria-controls="collapsePPP" style="padding-top: inherit;">
        <i class="fas fa-stream"></i>
        <span>PPP</span>
    </a>
    <div id="collapsePPP" class="collapse {{ Request::is('server/*/ppp*') ? 'show' : '' }}" aria-labelledby="headingPPP" data-parent="#accordionSidebar">

        <div class="bg-white py-2 collapse-inner rounded">
            <a class="collapse-item {{ Request::is('server/*/ppp/profiles') ? 'active' : '' }}" href="{{ route('ppp.profiles.index', ['mikrotik' => request()->mikrotik]) }}">Import PPP Profiles</a>
        </div>
        <div class="bg-white py-2 collapse-inner rounded">
            <a class="collapse-item {{ Request::is('server/*/ppp/secret/create') ? 'active' : '' }}" href="{{ route('ppp.secret.create', ['mikrotik' => request()->mikrotik]) }}">Created User Secret</a>
            <a class="collapse-item {{ Request::is('server/*/ppp/secrets') ? 'active' : '' }}" href="{{ route('ppp.secrets.index', ['mikrotik' => request()->mikrotik]) }}">Import User Secret</a>
        </div>
    </div>
</li>





<!-- Sidebar Toggler (Sidebar) -->
<div class="text-center d-none d-md-inline pt-4">
    <button class="rounded-circle border-0" id="sidebarToggle"></button>
</div>