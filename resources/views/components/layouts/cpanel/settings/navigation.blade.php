<!-- Nav Item - Dashboard -->
<li class="nav-item">
    <a class="nav-link" href="{{ route('home') }}">
        <i class="fas fa-arrow-alt-circle-left"></i>
        <span><strong>{{ __('navigation.settings.nav-item') }}</strong></span>
    </a>
</li>


<!-- Nav Item - Tables -->
@can('is-admin')
    <li class="nav-item @if (request()->routeIs('websystem.edit')) active @endif">
        <a class="nav-link collapsed" href="{{ route('websystem.edit', 'websystem') }}" aria-expanded="true"
            aria-controls="collapseSetting" style="padding-top: inherit;">
            <i class="fas fa-money-bill-wave"></i>
            <span>{{ __('navigation.settings.general') }}</span>
        </a>

    </li>
@endcan
@canany(['server-list', 'server-delete', 'server-create', 'server-edit'])
    <li class="nav-item @if (request()->routeIs('servers*')) active @endif">
        <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseServer"
            aria-expanded="true" aria-controls="collapseSetting" style="padding-top: inherit;">
            <i class="fas fa-server"></i>
            <span>{{ __('navigation.settings.servers.nav-item') }}</span>
        </a>
        <div id="collapseServer" class="collapse collapse @if (request()->routeIs('servers*'))  @endif"
            aria-labelledby="headingBilling" data-parent="#accordionSidebar">
            <div class="bg-white py-2 collapse-inner rounded">
                @can('server-create')
                    <a class="collapse-item @if (request()->routeIs('servers.create')) active @endif"
                        href="{{ route('servers.create') }}">
                        <i class="fa fa-plus"></i>
                        <span> {{ __('navigation.settings.servers.create') }}</span>
                    </a>
                @endcan
                <a class="collapse-item @if (request()->routeIs('servers.index')) active @endif"
                    href="{{ route('servers.index') }}">
                    <i class="fas fa-server"></i>
                    <span> {{ __('navigation.settings.servers.index') }}</span>
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
            <span>{{ __('navigation.settings.routers.nav-item') }}</span>
        </a>
        <div id="collapseRouter" class="collapse collapse @if (request()->routeIs('routers*') || request()->routeIs('brand*'))  @endif"
            aria-labelledby="headingBilling" data-parent="#accordionSidebar">
            <div class="bg-white py-2 collapse-inner rounded">
                @can('server-create')
                    <a class="collapse-item @if (request()->routeIs('routers.create')) active @endif"
                        href="{{ route('routers.create') }}">
                        <i class="fa fa-plus"></i>
                        <span> {{ __('navigation.settings.routers.create') }}</span>
                    </a>
                @endcan
                <a class="collapse-item @if (request()->routeIs('routers.index')) active @endif"
                    href="{{ route('routers.index') }}">
                    <i class="fas fa-server"></i>
                    <span> {{ __('navigation.settings.routers.index') }}</span>
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
            <span>{{ __('navigation.settings.customers.nav-item') }}</span>
        </a>

    </li>
@endcan
@canany(['user-list', 'user-create', 'user-edit', 'user-delete'])
    <li class="nav-item @if (request()->routeIs('users*')) active @endif">
        <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseUser" aria-expanded="true"
            aria-controls="collapseSetting" style="padding-top: inherit;">
            <i class="fas fa-user-cog"></i>
            <span>{{ __('navigation.settings.users.nav-item') }}</span>
        </a>
        <div id="collapseUser" class="collapse collapse @if (request()->routeIs('users*'))  @endif"
            aria-labelledby="headingBilling" data-parent="#accordionSidebar">
            <div class="bg-white py-2 collapse-inner rounded">
                @can('user-create')
                    <a class="collapse-item @if (request()->routeIs('users.create')) active @endif"
                        href="{{ route('users.create') }}">
                        <i class="fas fa-user-plus"></i>
                        <span> {{ __('navigation.settings.users.create') }}</span>
                    </a>
                @endcan
                <a class="collapse-item @if (request()->routeIs('users.index')) active @endif"
                    href="{{ route('users.index') }}">
                    <i class="fas fa-users"></i>
                    <span> {{ __('navigation.settings.users.index') }}</span>
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
            <span>{{ __('navigation.settings.roles.nav-item') }}</span>
        </a>
        <div id="collapseRole" class="collapse collapse @if (request()->routeIs('roles*'))  @endif"
            aria-labelledby="headingBilling" data-parent="#accordionSidebar">
            <div class="bg-white py-2 collapse-inner rounded">
                <a class="collapse-item @if (request()->routeIs('roles.create')) active @endif"
                    href="{{ route('roles.create') }}">
                    <i class="fa fa-plus"></i>
                    <span> {{ __('navigation.settings.roles.create') }}</span>
                </a>
                <a class="collapse-item @if (request()->routeIs('roles.index')) active @endif"
                    href="{{ route('roles.index') }}">
                    <i class="fas fa-user-shield"></i>
                    <span> {{ __('navigation.settings.roles.index') }}</span>
                </a>
            </div>
        </div>
    </li>
@endcanany
@can('is-admin')
    <li class="nav-item @if (request()->routeIs('telegramgateway*') || request()->routeIs('telegrammessage*')) active @endif">
        <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseTelGateway"
            aria-expanded="true" aria-controls="collapseSetting" style="padding-top: inherit;">
            <i class="fab fa-telegram-plane"></i>
            <span>{{ __('navigation.settings.telegram.nav-item') }}</span>
        </a>
        <div id="collapseTelGateway" class="collapse collapse @if (request()->routeIs('telegramgateway*') || request()->routeIs('telegrammessage*'))  @endif"
            aria-labelledby="headingBilling" data-parent="#accordionSidebar">
            <div class="bg-white py-2 collapse-inner rounded">

                <a class="collapse-item @if (request()->routeIs('telegramgateway*')) active @endif"
                    href="{{ route('telegramgateway.index') }}">
                    <i class="fas fa-plug"></i>
                    <span> {{ __('navigation.settings.telegram.gateways') }}</span>
                </a>
                <a class="collapse-item @if (request()->routeIs('telegramchat*')) active @endif"
                    href="{{ route('telegramchat.index') }}">
                    <i class="fas fa-user-tag"></i>
                    <span> {{ __('navigation.settings.telegram.chat-ids') }}</span>
                </a>
                <a class="collapse-item @if (request()->routeIs('telegrammessage*')) active @endif"
                    href="{{ route('telegrammessage.index') }}">
                    <i class="fas fa-comment-dots"></i>
                    <span> {{ __('navigation.settings.telegram.messages') }}</span>
                </a>
            </div>
        </div>
    </li>
@endcan
@can('is-admin')
    <li class="nav-item @if (request()->routeIs('whatsappgateway*') || request()->routeIs('whatsappmessage*')) active @endif">
        <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseGateway"
            aria-expanded="true" aria-controls="collapseSetting" style="padding-top: inherit;">
            <i class="fab fa-whatsapp"></i>
            <span>{{ __('navigation.settings.whatsapp.nav-item') }}</span>
        </a>
        <div id="collapseGateway" class="collapse collapse @if (request()->routeIs('whatsappgateway*') || request()->routeIs('whatsappmessage*'))  @endif"
            aria-labelledby="headingBilling" data-parent="#accordionSidebar">
            <div class="bg-white py-2 collapse-inner rounded">

                <a class="collapse-item @if (request()->routeIs('whatsappgateway*')) active @endif"
                    href="{{ route('whatsappgateway.index') }}">
                    <i class="fas fa-plug"></i>
                    <span> {{ __('navigation.settings.whatsapp.gateways') }}</span>
                </a>
                <a class="collapse-item @if (request()->routeIs('whatsappmessage*')) active @endif"
                    href="{{ route('whatsappmessage.index') }}">
                    <i class="fas fa-comment-dots"></i>
                    <span> {{ __('navigation.settings.whatsapp.messages') }}</span>
                </a>
                <a class="collapse-item @if (request()->routeIs('whatsapptemplate*')) active @endif"
                    href="{{ route('whatsapptemplate.index') }}">
                    <i class="fas fa-puzzle-piece"></i>
                    <span> {{ __('navigation.settings.whatsapp.templates') }}</span>
                </a>
                <a class="collapse-item @if (request()->routeIs('whatsappfeature*')) active @endif"
                    href="{{ route('whatsappfeature.index') }}">
                    <i class="fas fa-cogs"></i>
                    <span> {{ __('navigation.settings.whatsapp.features') }}</span>
                </a>
            </div>
        </div>
    </li>
@endcan
@can('is-admin')
    <li class="nav-item @if (request()->routeIs('messageevents*')) active @endif">
        <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseMessageEvents"
            aria-expanded="true" aria-controls="collapseSetting" style="padding-top: inherit;">
            <i class="fas fa-bell"></i>
            <span>{{ __('navigation.settings.message-events.nav-item') }}</span>
        </a>
        <div id="collapseMessageEvents" class="collapse collapse @if (request()->routeIs('messageevents*'))  @endif"
            aria-labelledby="headingBilling" data-parent="#accordionSidebar">
            <div class="bg-white py-2 collapse-inner rounded">

                <a class="collapse-item @if (request()->routeIs('messageevents*')) active @endif"
                    href="{{ route('messageevents.index') }}">
                    <i class="fas fa-bell"></i>
                    <span> {{ __('navigation.settings.message-events.index') }}</span>
                </a>
            </div>
        </div>
    </li>
@endcan
@can('is-admin')
    <li class="nav-item @if (request()->routeIs('paymentgateway*')) active @endif">
        <a class="nav-link collapsed" href="{{ route('paymentgateway.index') }}" aria-expanded="true"
            aria-controls="collapseSetting" style="padding-top: inherit;">
            <i class="fas fa-money-bill-wave"></i>
            <span>{{ __('navigation.settings.payment-gateways.nav-item') }}</span>
        </a>

    </li>

    <li class="nav-item @if (request()->routeIs('banks*')) active @endif">
        <a class="nav-link collapsed" href="{{ route('banks.index') }}" aria-expanded="true"
            aria-controls="collapseSetting" style="padding-top: inherit;">
            <i class="fas fa-money-bill-wave"></i>
            <span>{{ __('navigation.settings.banks.nav-item') }}</span>
        </a>

    </li>

    <li class="nav-item @if (request()->routeIs('autoisolir*')) active @endif">
        <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseAutoisolir"
            aria-expanded="true" aria-controls="collapseSetting" style="padding-top: inherit;">
            <i class="fas fa-user-times"></i>
            <span>{{ __('navigation.settings.autoisolir.nav-item') }}</span>
        </a>
        <div id="collapseAutoisolir" class="collapse collapse @if (request()->routeIs('autoisolir*'))  @endif"
            aria-labelledby="headingBilling" data-parent="#accordionSidebar">
            <div class="bg-white py-2 collapse-inner rounded">
                <a class="collapse-item @if (request()->routeIs('autoisolir.create')) active @endif"
                    href="{{ route('autoisolir.create') }}">
                    <i class="fa fa-plus"></i>
                    <span> {{ __('navigation.settings.autoisolir.create') }}</span>
                </a>

                <a class="collapse-item @if (request()->routeIs('autoisolir.index')) active @endif"
                    href="{{ route('autoisolir.index') }}">
                    <i class="fas fa-user-times"></i>
                    <span> {{ __('navigation.settings.autoisolir.index') }}</span>
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
            <span>{{ __('navigation.settings.exports.nav-item') }}</span>
        </a>

    </li>
@endcanany
<li class="nav-item @if (request()->routeIs('settings.update*')) active @endif">
    <a class="nav-link collapsed" href="{{ route('settings.update.index') }}" aria-expanded="true"
        aria-controls="collapseSetting" style="padding-top: inherit;">
        <i class="fas fa-upload"></i>
        <span>{{ __('navigation.settings.updates.nav-item') }}</span>
    </a>

</li>



<hr class="sidebar-divider my-0">

<!-- Nav Item - Dashboard -->
<li class="nav-item">
    <h3><a class="nav-link" href="{{ route('home') }}">
            <i class="fas fa-arrow-alt-circle-left"></i>
            <span><strong>{{ __('navigation.settings.back') }}</strong></span>
        </a></h3>
</li>
<div class="text-center d-none d-md-inline pt-4">
    <button class="rounded-circle border-0" id="sidebarToggle"></button>
</div>
