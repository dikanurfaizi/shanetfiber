<!-- Nav Item - Dashboard -->
<li class="nav-item">
    <a class="nav-link" href="{{ route('home') }}">
        <i class="fas fa-arrow-alt-circle-left"></i>
        <span><strong>{{ __('Settings') }}</strong></span>
    </a>
</li>


<!-- Nav Item - Tables -->
@can('is-admin')
    <li class="nav-item @if (request()->routeIs('websystem.edit')) active @endif">
        <a class="nav-link collapsed" href="{{ route('websystem.edit', 'websystem') }}" aria-expanded="true"
            aria-controls="collapseSetting" style="padding-top: inherit;">
            <i class="fas fa-money-bill-wave"></i>
            <span>{{ __(' General') }}</span>
        </a>

    </li>
@endcan
@canany(['server-list', 'server-delete', 'server-create', 'server-edit'])
    <li class="nav-item @if (request()->routeIs('servers*')) active @endif">
        <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseServer"
            aria-expanded="true" aria-controls="collapseSetting" style="padding-top: inherit;">
            <i class="fas fa-server"></i>
            <span>{{ __('Servers') }}</span>
        </a>
        <div id="collapseServer" class="collapse collapse @if (request()->routeIs('servers*')) show @endif"
            aria-labelledby="headingBilling" data-parent="#accordionSidebar">
            <div class="bg-white py-2 collapse-inner rounded">
                @can('server-create')
                    <a class="collapse-item @if (request()->routeIs('servers.create')) active @endif"
                        href="{{ route('servers.create') }}">
                        <i class="fa fa-plus"></i>
                        <span> {{ __('Add Server') }}</span>
                    </a>
                @endcan
                <a class="collapse-item @if (request()->routeIs('servers.index')) active @endif"
                    href="{{ route('servers.index') }}">
                    <i class="fas fa-server"></i>
                    <span> {{ __('All Servers') }}</span>
                </a>
            </div>
        </div>

    </li>
@endcanany
@canany(['server-list', 'server-delete', 'server-create', 'server-edit'])
    <li class="nav-item @if (request()->routeIs('routers*') || request()->routeIs('brand*')) active @endif">
        <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseRouter"
            aria-expanded="true" aria-controls="collapseSetting" style="padding-top: inherit;">
            <i class="fas fa-user-shield"></i>
            <span>{{ __(' Router') }}</span>
        </a>
        <div id="collapseRouter" class="collapse collapse @if (request()->routeIs('routers*') || request()->routeIs('brand*')) show @endif"
            aria-labelledby="headingBilling" data-parent="#accordionSidebar">
            <div class="bg-white py-2 collapse-inner rounded">
                @can('server-create')
                    <a class="collapse-item @if (request()->routeIs('routers.create')) active @endif"
                        href="{{ route('routers.create') }}">
                        <i class="fa fa-plus"></i>
                        <span> {{ __('Add New Router') }}</span>
                    </a>
                @endcan
                <a class="collapse-item @if (request()->routeIs('routers.index')) active @endif"
                    href="{{ route('routers.index') }}">
                    <i class="fas fa-server"></i>
                    <span> {{ __('All Routers') }}</span>
                </a>
            </div>
        </div>
    </li>
@endcanany
@can('is-admin')
    <li class="nav-item @if (request()->routeIs('websystem.customer')) active @endif">
        <a class="nav-link collapsed" href="{{ route('websystem.customer', 'websystem') }}" aria-expanded="true"
            aria-controls="collapseSetting" style="padding-top: inherit;">
            <i class="fas fa-fw fa-users"></i>
            <span>{{ __(' Customer') }}</span>
        </a>

    </li>
@endcan
@canany(['user-list', 'user-create', 'user-edit', 'user-delete'])
    <li class="nav-item @if (request()->routeIs('users*')) active @endif">
        <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseUser" aria-expanded="true"
            aria-controls="collapseSetting" style="padding-top: inherit;">
            <i class="fas fa-user-cog"></i>
            <span>{{ __('dashboard.users') }}</span>
        </a>
        <div id="collapseUser" class="collapse collapse @if (request()->routeIs('users*')) show @endif"
            aria-labelledby="headingBilling" data-parent="#accordionSidebar">
            <div class="bg-white py-2 collapse-inner rounded">
                @can('user-create')
                    <a class="collapse-item @if (request()->routeIs('users.create')) active @endif"
                        href="{{ route('users.create') }}">
                        <i class="fas fa-user-plus"></i>
                        <span> {{ __('Add User') }}</span>
                    </a>
                @endcan
                <a class="collapse-item @if (request()->routeIs('users.index')) active @endif"
                    href="{{ route('users.index') }}">
                    <i class="fas fa-users"></i>
                    <span> {{ __('All Users') }}</span>
                </a>
            </div>
        </div>
    </li>
@endcanany
@canany(['role-list', 'role-edit', 'role-delete'])
    <li class="nav-item @if (request()->routeIs('roles*')) active @endif">
        <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseRole" aria-expanded="true"
            aria-controls="collapseSetting" style="padding-top: inherit;">
            <i class="fas fa-user-secret"></i>
            <span>{{ __(' Roles') }}</span>
        </a>
        <div id="collapseRole" class="collapse collapse @if (request()->routeIs('roles*')) show @endif"
            aria-labelledby="headingBilling" data-parent="#accordionSidebar">
            <div class="bg-white py-2 collapse-inner rounded">
                <a class="collapse-item @if (request()->routeIs('roles.create')) active @endif"
                    href="{{ route('roles.create') }}">
                    <i class="fa fa-plus"></i>
                    <span> {{ __('Add Role') }}</span>
                </a>
                <a class="collapse-item @if (request()->routeIs('roles.index')) active @endif"
                    href="{{ route('roles.index') }}">
                    <i class="fas fa-user-shield"></i>
                    <span> {{ __('All Roles') }}</span>
                </a>
            </div>
        </div>
    </li>
@endcanany
@can('is-admin')
    <li class="nav-item @if (request()->routeIs('paymentgateway*')) active @endif">
        <a class="nav-link collapsed" href="{{ route('paymentgateway.index') }}" aria-expanded="true"
            aria-controls="collapseSetting" style="padding-top: inherit;">
            <i class="fas fa-money-bill-wave"></i>
            <span>{{ __(' Payment Gateway') }}</span>
        </a>

    </li>

    <li class="nav-item @if (request()->routeIs('whatsapp-gateway.*')) active @endif">
        <a class="nav-link collapsed" href="{{ route('whatsapp-gateway.index') }}" aria-expanded="true"
            aria-controls="collapseSetting" style="padding-top: inherit;">
            <i class="fab fa-whatsapp"></i>
            <span>{{ __(' WhatsApp Broadcast') }}</span>
        </a>
    </li>


    <li class="nav-item @if (request()->routeIs('banks*')) active @endif">
        <a class="nav-link collapsed" href="{{ route('banks.index') }}" aria-expanded="true"
            aria-controls="collapseSetting" style="padding-top: inherit;">
            <i class="fas fa-money-bill-wave"></i>
            <span>{{ __(' Bank') }}</span>
        </a>

    </li>

    <li class="nav-item @if (request()->routeIs('autoisolir*')) active @endif">
        <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseAutoisolir"
            aria-expanded="true" aria-controls="collapseSetting" style="padding-top: inherit;">
            <i class="fas fa-user-times"></i>
            <span>{{ __(' Auto Isolir') }}</span>
        </a>
        <div id="collapseAutoisolir" class="collapse collapse @if (request()->routeIs('autoisolir*')) show @endif"
            aria-labelledby="headingBilling" data-parent="#accordionSidebar">
            <div class="bg-white py-2 collapse-inner rounded">
                <a class="collapse-item @if (request()->routeIs('autoisolir.create')) active @endif"
                    href="{{ route('autoisolir.create') }}">
                    <i class="fa fa-plus"></i>
                    <span> {{ __('Add New Auto Isolir') }}</span>
                </a>
                <a class="collapse-item @if (request()->routeIs('autoisolir.index')) active @endif"
                    href="{{ route('autoisolir.index') }}">
                    <i class="fas fa-user-times"></i>
                    <span> {{ __('Auto Isolirs') }}</span>
                </a>
            </div>
        </div>
    </li>
@endcan
@canany(['secret-create', 'secret-edit', 'secret-delete'])
    <li class="nav-item @if (request()->routeIs('export*')) active @endif">
        <a class="nav-link collapsed" href="{{ route('export.index') }}" aria-expanded="true"
            aria-controls="collapseSetting" style="padding-top: inherit;">
            <i class="fas fa-clone"></i>
            <span>{{ __(' Exports to router') }}</span>
        </a>

    </li>
@endcanany
<li class="nav-item @if (request()->routeIs('settings.update*')) active @endif">
    <a class="nav-link collapsed" href="{{ route('settings.update.index') }}" aria-expanded="true"
        aria-controls="collapseSetting" style="padding-top: inherit;">
        <i class="fas fa-upload"></i>
        <span>{{ __(' Update') }}</span>
    </a>

</li>



<hr class="sidebar-divider my-0">

<!-- Nav Item - Dashboard -->
<li class="nav-item">
    <h3><a class="nav-link" href="{{ route('home') }}">
            <i class="fas fa-arrow-alt-circle-left"></i>
            <span><strong>{{ __('Back') }}</strong></span>
        </a></h3>
</li>
<div class="text-center d-none d-md-inline pt-4">
    <button class="rounded-circle border-0" id="sidebarToggle"></button>
</div>
