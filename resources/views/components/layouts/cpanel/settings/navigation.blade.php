<!-- Nav Item - Dashboard / Top Back -->
<li class="nav-item">
    <a class="nav-link" href="{{ route('home') }}">
        <i class="fas fa-arrow-alt-circle-left fa-fw me-2"></i>
        <span><strong>{{ __('navigation.settings.nav-item') }}</strong></span>
    </a>
</li>

<!-- Nav Item - General Settings -->
@can('is-admin')
    <li class="nav-item @if (request()->routeIs('websystem.edit')) active @endif">
        <a class="nav-link" href="{{ route('websystem.edit', 'websystem') }}">
            <i class="fas fa-money-bill-wave fa-fw me-2"></i>
            <span>{{ __('navigation.settings.general') }}</span>
        </a>
    </li>
@endcan

<!-- Nav Item - Servers & RADIUS -->
@canany(['server-list', 'server-delete', 'server-create', 'server-edit'])
    <li class="nav-item @if (request()->routeIs('servers*') || request()->routeIs('radius*')) active @endif">
        <a class="nav-link @if (!request()->routeIs('servers*') && !request()->routeIs('radius*')) collapsed @endif" href="#" data-bs-toggle="collapse"
            data-bs-target="#collapseServer" aria-expanded="@if (request()->routeIs('servers*') || request()->routeIs('radius*')) true @else false @endif"
            aria-controls="collapseServer">
            <i class="fas fa-server fa-fw me-2"></i>
            <span>{{ __('navigation.settings.servers.nav-item') }}</span>
        </a>
        <div id="collapseServer" class="collapse @if (request()->routeIs('servers*') || request()->routeIs('radius*')) show @endif"
            aria-labelledby="headingBilling" data-bs-parent="#accordionSidebar">
            <div class="bg-white py-2 collapse-inner rounded">
                @can('server-create')
                    <a class="collapse-item @if (request()->routeIs('servers.create')) active @endif"
                        href="{{ route('servers.create') }}">
                        <i class="fas fa-plus fa-fw me-2"></i>
                        <span>{{ __('navigation.settings.servers.create') }}</span>
                    </a>
                @endcan

                <a class="collapse-item @if (request()->routeIs('servers.index')) active @endif"
                    href="{{ route('servers.index') }}">
                    <i class="fas fa-server fa-fw me-2"></i>
                    <span>{{ __('navigation.settings.servers.index') }}</span>
                </a>

                {{-- Radius Management --}}
                <a class="collapse-item @if (request()->routeIs('radius.index')) active @endif" href="{{ route('radius.index') }}">
                    <i class="fas fa-database fa-fw me-2"></i>
                    <span>{{ __('navigation.settings.radius.management') }}</span>
                </a>

                {{-- Session Aktif / Online Users --}}
                <a class="collapse-item @if (request()->routeIs('radius.active-sessions')) active @endif"
                    href="{{ route('radius.active-sessions') }}">
                    <i class="fas fa-wifi fa-fw me-2"></i>
                    <span>{{ __('navigation.settings.radius.active_sessions') }}</span>
                </a>

                {{-- Provisioning Mode Settings --}}
                <a class="collapse-item @if (request()->routeIs('radius.settings')) active @endif"
                    href="{{ route('radius.settings') }}">
                    <i class="fas fa-cogs fa-fw me-2"></i>
                    <span>{{ __('navigation.settings.radius.provisioning_mode') }}</span>
                </a>
            </div>
        </div>
    </li>
@endcanany

<!-- Nav Item - Routers -->
@canany(['server-list', 'server-delete', 'server-create', 'server-edit'])
    <li class="nav-item @if (request()->routeIs('routers*') || request()->routeIs('brand*')) active @endif">
        <a class="nav-link @if (!request()->routeIs('routers*') && !request()->routeIs('brand*')) collapsed @endif" href="#" data-bs-toggle="collapse"
            data-bs-target="#collapseRouter" aria-expanded="@if (request()->routeIs('routers*') || request()->routeIs('brand*')) true @else false @endif"
            aria-controls="collapseRouter">
            <i class="fas fa-user-shield fa-fw me-2"></i>
            <span>{{ __('navigation.settings.routers.nav-item') }}</span>
        </a>
        <div id="collapseRouter" class="collapse @if (request()->routeIs('routers*') || request()->routeIs('brand*')) show @endif"
            aria-labelledby="headingBilling" data-bs-parent="#accordionSidebar">
            <div class="bg-white py-2 collapse-inner rounded">
                @can('server-create')
                    <a class="collapse-item @if (request()->routeIs('routers.create')) active @endif"
                        href="{{ route('routers.create') }}">
                        <i class="fas fa-plus fa-fw me-2"></i>
                        <span>{{ __('navigation.settings.routers.create') }}</span>
                    </a>
                @endcan
                <a class="collapse-item @if (request()->routeIs('routers.index')) active @endif"
                    href="{{ route('routers.index') }}">
                    <i class="fas fa-server fa-fw me-2"></i>
                    <span>{{ __('navigation.settings.routers.index') }}</span>
                </a>
            </div>
        </div>
    </li>
@endcanany

<!-- Nav Item - Customers Settings -->
@can('is-admin')
    <li class="nav-item @if (request()->routeIs('websystem.customer')) active @endif">
        <a class="nav-link" href="{{ route('websystem.customer', 'websystem') }}">
            <i class="fas fa-users fa-fw me-2"></i>
            <span>{{ __('navigation.settings.customers.nav-item') }}</span>
        </a>
    </li>
@endcan

<!-- Nav Item - Users -->
@canany(['user-list', 'user-create', 'user-edit', 'user-delete'])
    <li class="nav-item @if (request()->routeIs('users*')) active @endif">
        <a class="nav-link @if (!request()->routeIs('users*')) collapsed @endif" href="#" data-bs-toggle="collapse"
            data-bs-target="#collapseUser" aria-expanded="@if (request()->routeIs('users*')) true @else false @endif"
            aria-controls="collapseUser">
            <i class="fas fa-user-cog fa-fw me-2"></i>
            <span>{{ __('navigation.settings.users.nav-item') }}</span>
        </a>
        <div id="collapseUser" class="collapse @if (request()->routeIs('users*')) show @endif"
            aria-labelledby="headingBilling" data-bs-parent="#accordionSidebar">
            <div class="bg-white py-2 collapse-inner rounded">
                @can('user-create')
                    <a class="collapse-item @if (request()->routeIs('users.create')) active @endif"
                        href="{{ route('users.create') }}">
                        <i class="fas fa-user-plus fa-fw me-2"></i>
                        <span>{{ __('navigation.settings.users.create') }}</span>
                    </a>
                @endcan
                <a class="collapse-item @if (request()->routeIs('users.index')) active @endif"
                    href="{{ route('users.index') }}">
                    <i class="fas fa-users fa-fw me-2"></i>
                    <span>{{ __('navigation.settings.users.index') }}</span>
                </a>
            </div>
        </div>
    </li>
@endcanany

<!-- Nav Item - Roles -->
@canany(['role-list', 'role-edit', 'role-delete'])
    <li class="nav-item @if (request()->routeIs('roles*')) active @endif">
        <a class="nav-link @if (!request()->routeIs('roles*')) collapsed @endif" href="#" data-bs-toggle="collapse"
            data-bs-target="#collapseRole" aria-expanded="@if (request()->routeIs('roles*')) true @else false @endif"
            aria-controls="collapseRole">
            <i class="fas fa-user-secret fa-fw me-2"></i>
            <span>{{ __('navigation.settings.roles.nav-item') }}</span>
        </a>
        <div id="collapseRole" class="collapse @if (request()->routeIs('roles*')) show @endif"
            aria-labelledby="headingBilling" data-bs-parent="#accordionSidebar">
            <div class="bg-white py-2 collapse-inner rounded">
                <a class="collapse-item @if (request()->routeIs('roles.create')) active @endif"
                    href="{{ route('roles.create') }}">
                    <i class="fas fa-plus fa-fw me-2"></i>
                    <span>{{ __('navigation.settings.roles.create') }}</span>
                </a>
                <a class="collapse-item @if (request()->routeIs('roles.index')) active @endif"
                    href="{{ route('roles.index') }}">
                    <i class="fas fa-user-shield fa-fw me-2"></i>
                    <span>{{ __('navigation.settings.roles.index') }}</span>
                </a>
            </div>
        </div>
    </li>
@endcanany

<!-- Nav Item - Telegram Gateway -->
@can('is-admin')
    <li class="nav-item @if (request()->routeIs('telegramgateway*') ||
            request()->routeIs('telegrammessage*') ||
            request()->routeIs('telegramchat*')) active @endif">
        <a class="nav-link @if (!request()->routeIs('telegram*')) collapsed @endif" href="#" data-bs-toggle="collapse"
            data-bs-target="#collapseTelGateway"
            aria-expanded="@if (request()->routeIs('telegram*')) true @else false @endif"
            aria-controls="collapseTelGateway">
            <i class="fab fa-telegram-plane fa-fw me-2"></i>
            <span>{{ __('navigation.settings.telegram.nav-item') }}</span>
        </a>
        <div id="collapseTelGateway" class="collapse @if (request()->routeIs('telegram*')) show @endif"
            aria-labelledby="headingBilling" data-bs-parent="#accordionSidebar">
            <div class="bg-white py-2 collapse-inner rounded">
                <a class="collapse-item @if (request()->routeIs('telegramgateway*')) active @endif"
                    href="{{ route('telegramgateway.index') }}">
                    <i class="fas fa-plug fa-fw me-2"></i>
                    <span>{{ __('navigation.settings.telegram.gateways') }}</span>
                </a>
                <a class="collapse-item @if (request()->routeIs('telegramchat*')) active @endif"
                    href="{{ route('telegramchat.index') }}">
                    <i class="fas fa-user-tag fa-fw me-2"></i>
                    <span>{{ __('navigation.settings.telegram.chat-ids') }}</span>
                </a>
                <a class="collapse-item @if (request()->routeIs('telegrammessage*')) active @endif"
                    href="{{ route('telegrammessage.index') }}">
                    <i class="fas fa-comment-dots fa-fw me-2"></i>
                    <span>{{ __('navigation.settings.telegram.messages') }}</span>
                </a>
            </div>
        </div>
    </li>
@endcan

<!-- Nav Item - WhatsApp Gateway -->
@can('is-admin')
    <li class="nav-item @if (request()->routeIs('whatsapp*')) active @endif">
        <a class="nav-link @if (!request()->routeIs('whatsapp*')) collapsed @endif" href="#" data-bs-toggle="collapse"
            data-bs-target="#collapseGateway" aria-expanded="@if (request()->routeIs('whatsapp*')) true @else false @endif"
            aria-controls="collapseGateway">
            <i class="fab fa-whatsapp fa-fw me-2"></i>
            <span>{{ __('navigation.settings.whatsapp.nav-item') }}</span>
        </a>
        <div id="collapseGateway" class="collapse @if (request()->routeIs('whatsapp*')) show @endif"
            aria-labelledby="headingBilling" data-bs-parent="#accordionSidebar">
            <div class="bg-white py-2 collapse-inner rounded">
                <a class="collapse-item @if (request()->routeIs('whatsappgateway*')) active @endif"
                    href="{{ route('whatsappgateway.index') }}">
                    <i class="fas fa-plug fa-fw me-2"></i>
                    <span>{{ __('navigation.settings.whatsapp.gateways') }}</span>
                </a>
                <a class="collapse-item @if (request()->routeIs('whatsappmessage*')) active @endif"
                    href="{{ route('whatsappmessage.index') }}">
                    <i class="fas fa-comment-dots fa-fw me-2"></i>
                    <span>{{ __('navigation.settings.whatsapp.messages') }}</span>
                </a>
                <a class="collapse-item @if (request()->routeIs('whatsapptemplate*')) active @endif"
                    href="{{ route('whatsapptemplate.index') }}">
                    <i class="fas fa-puzzle-piece fa-fw me-2"></i>
                    <span>{{ __('navigation.settings.whatsapp.templates') }}</span>
                </a>
                <a class="collapse-item @if (request()->routeIs('whatsappfeature*')) active @endif"
                    href="{{ route('whatsappfeature.index') }}">
                    <i class="fas fa-cogs fa-fw me-2"></i>
                    <span>{{ __('navigation.settings.whatsapp.features') }}</span>
                </a>
            </div>
        </div>
    </li>
@endcan

<!-- Nav Item - Message Events -->
@can('is-admin')
    <li class="nav-item @if (request()->routeIs('messageevents*')) active @endif">
        <a class="nav-link" href="{{ route('messageevents.index') }}">
            <i class="fas fa-bell fa-fw me-2"></i>
            <span>{{ __('navigation.settings.message-events.index') }}</span>
        </a>
    </li>
@endcan

<!-- Nav Item - Payment Gateways, Banks, & Auto Isolir -->
@can('is-admin')
    <li class="nav-item @if (request()->routeIs('paymentgateway*')) active @endif">
        <a class="nav-link" href="{{ route('paymentgateway.index') }}">
            <i class="fas fa-money-bill-wave fa-fw me-2"></i>
            <span>{{ __('navigation.settings.payment-gateways.nav-item') }}</span>
        </a>
    </li>

    <li class="nav-item @if (request()->routeIs('banks*')) active @endif">
        <a class="nav-link" href="{{ route('banks.index') }}">
            <i class="fas fa-university fa-fw me-2"></i>
            <span>{{ __('navigation.settings.banks.nav-item') }}</span>
        </a>
    </li>

    <li class="nav-item @if (request()->routeIs('autoisolir*')) active @endif">
        <a class="nav-link @if (!request()->routeIs('autoisolir*')) collapsed @endif" href="#" data-bs-toggle="collapse"
            data-bs-target="#collapseAutoisolir"
            aria-expanded="@if (request()->routeIs('autoisolir*')) true @else false @endif"
            aria-controls="collapseAutoisolir">
            <i class="fas fa-user-times fa-fw me-2"></i>
            <span>{{ __('navigation.settings.autoisolir.nav-item') }}</span>
        </a>
        <div id="collapseAutoisolir" class="collapse @if (request()->routeIs('autoisolir*')) show @endif"
            aria-labelledby="headingBilling" data-bs-parent="#accordionSidebar">
            <div class="bg-white py-2 collapse-inner rounded">
                <a class="collapse-item @if (request()->routeIs('autoisolir.create')) active @endif"
                    href="{{ route('autoisolir.create') }}">
                    <i class="fas fa-plus fa-fw me-2"></i>
                    <span>{{ __('navigation.settings.autoisolir.create') }}</span>
                </a>
                <a class="collapse-item @if (request()->routeIs('autoisolir.index')) active @endif"
                    href="{{ route('autoisolir.index') }}">
                    <i class="fas fa-user-times fa-fw me-2"></i>
                    <span>{{ __('navigation.settings.autoisolir.index') }}</span>
                </a>
            </div>
        </div>
    </li>
@endcan

<!-- Nav Item - Exports -->
@canany(['secret-create', 'secret-edit', 'secret-delete'])
    <li class="nav-item @if (request()->routeIs('export*')) active @endif">
        <a class="nav-link" href="{{ route('export.index') }}">
            <i class="fas fa-clone fa-fw me-2"></i>
            <span>{{ __('navigation.settings.exports.nav-item') }}</span>
        </a>
    </li>
@endcanany

<!-- Nav Item - System Updates -->
<li class="nav-item @if (request()->routeIs('settings.update*')) active @endif">
    <a class="nav-link" href="{{ route('settings.update.index') }}">
        <i class="fas fa-upload fa-fw me-2"></i>
        <span>{{ __('navigation.settings.updates.nav-item') }}</span>
    </a>
</li>

<hr class="sidebar-divider my-0">

<!-- Nav Item - Bottom Back -->
<li class="nav-item">
    <a class="nav-link" href="{{ route('home') }}">
        <i class="fas fa-arrow-alt-circle-left fa-fw me-2"></i>
        <span><strong>{{ __('navigation.settings.back') }}</strong></span>
    </a>
</li>

<!-- Sidebar Toggler (Sidebar) -->
<div class="text-center d-none d-md-inline pt-4">
    <button class="rounded-circle border-0" id="sidebarToggle"></button>
</div>
