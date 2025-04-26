<!-- Sidebar -->
<ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">

    <!-- Sidebar - Brand -->
    <a class="sidebar-brand d-flex align-items-center justify-content-center" href="/">
        <div class="sidebar-brand-icon rotate-n-15">
            <i class="fas fa-rss"></i>
        </div>
        <div class="sidebar-brand-text mx-3">Customer Management</div>
    </a>
    <!-- Divider -->
    <hr class="sidebar-divider my-0">

    <!-- Nav Item - Dashboard -->
    <li class="nav-item @if (request()->routeIs('home')) active @endif">
        <a class="nav-link" href="{{ route('home') }}">
            <i class="fas fa-home"></i>
            <span>{{ __('Home') }}</span>
        </a>
    </li>
    <!-- Nav Item - Tables -->
    @canany(['customer-list', 'customer-edit', 'customer-delete', 'customer-create', 'secret-edit'])
        <li class="nav-item @if (request()->routeIs('customers*')) active @endif">
            {{-- <a class="nav-link collapsed" href="{{ route('customers.index') }}" data-target="#collapseBilling" --}}
            <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseCustomer"
                aria-expanded="true" aria-controls="collapseCustomer" style="padding-top: inherit;">
                <i class="fas fa-fw fa-users"></i>
                <span>{{ __('navigation.customers.nav-item') }}</span>
            </a>
            <div id="collapseCustomer" class="collapse" aria-labelledby="headingCustomer" data-parent="#accordionSidebar">
                <div class="bg-white py-2 collapse-inner rounded">
                    @can('customer-create')
                        <a class="collapse-item @if (request()->routeIs('customers.create')) active @endif"
                            href="{{ route('customers.create') }}">{{ __('navigation.customers.create') }}</a>
                    @endcan
                    <a class="collapse-item @if (request()->routeIs('customers.index')) active @endif"
                        href="{{ route('customers.index') }}">{{ __('navigation.customers.index') }}</a>
                </div>
                @canany(['customer-list', 'customer-edit', 'customer-delete'])
                    <div class="bg-white py-2 collapse-inner rounded">
                        <a class="collapse-item @if (request()->routeIs('customers.member')) active @endif"
                            href="{{ route('members.index') }}">{{ __('navigation.members.index') }}</a>
                    </div>
                @endcanany
            </div>
        </li>
    @endcanany
    @canany(['billing-list', 'billing-create', 'billing-edit', 'billing-delete'])
        <li class="nav-item @if (request()->routeIs('billings*')) active @endif">
            <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseBilling"
                aria-expanded="true" aria-controls="collapseBilling" style="padding-top: inherit;">
                <i class="fas fa-money-check-alt"></i>
                <span>{{ __('navigation.billings.nav-item') }}</span>
            </a>
            <div id="collapseBilling" class="collapse collapse @if (request()->routeIs('billings*'))  @endif"
                aria-labelledby="headingBilling" data-parent="#accordionSidebar">
                @can('billing-create')
                    <div class="bg-white py-2 collapse-inner rounded">
                        <a class="collapse-item @if (request()->routeIs('billings.create')) active @endif"
                            href="{{ route('billings.create') }}">{{ __('navigation.billings.create') }}</a>
                        <a class="collapse-item @if (request()->routeIs('billings.create.one')) active @endif"
                            href="{{ route('billings.create.one') }}">{{ __('navigation.billings.add') }}</a>
                    </div>
                @endcan
                @canany(['billing-list', 'billing-edit', 'billing-delete'])
                    <div class="bg-white py-2 collapse-inner rounded">
                        <a class="collapse-item @if (request()->routeIs('billings.index')) active @endif"
                            href="{{ route('billings.index') }}">{{ __('navigation.billings.last') }}</a>
                    </div>
                @endcanany
            </div>
        </li>
    @endcanany
    @canany(['packet-list', 'packet-create', 'packet-edit', 'packet-delete'])
        <li class="nav-item @if (request()->routeIs('packets*')) active @endif">
            <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapsePacket"
                aria-expanded="true" aria-controls="collapsePacket" style="padding-top: inherit;">
                <i class="fas fa-box"></i>
                <span>{{ __('navigation.pakets.nav-item') }}</span>
            </a>

            <div id="collapsePacket" class="collapse collapse @if (request()->routeIs('packets*') || request()->routeIs('profiles*'))  @endif"
                aria-labelledby="headingBilling" data-parent="#accordionSidebar">
                <div class="bg-white py-2 collapse-inner rounded">
                    @can('packet-create')
                        <a class="collapse-item @if (request()->routeIs('packets.create')) active @endif"
                            href="{{ route('packets.create') }}">{{ __('navigation.pakets.create') }}</a>
                    @endcan

                    <a class="collapse-item @if (request()->routeIs('packets.index')) active @endif"
                        href="{{ route('packets.index') }}">{{ __('navigation.pakets.index') }}</a>

                </div>

                <div class="bg-white py-2 collapse-inner rounded">
                    @can('packet-create')
                        <a class="collapse-item @if (request()->routeIs('ppp-profiles.create')) active @endif"
                            href="{{ route('ppp-profiles.create') }}">{{ __('navigation.pakets.profile.create') }}</a>
                    @endcan
                    @canany(['packet-edit', 'packet-delete'])
                        <a class="collapse-item @if (request()->routeIs('ppp-profiles.index')) active @endif"
                            href="{{ route('ppp-profiles.index') }}">{{ __('navigation.pakets.profile.index') }}</a>
                    @endcanany
                </div>
            </div>

        </li>
    @endcanany

    @canany(['user-list', 'user-create', 'user-edit', 'user-delete', 'role-list', 'role-edit', 'role-delete',
        'server-list', 'server-edit', 'server-delete', 'server-create'])
        <!-- Divider -->
        <hr class="sidebar-divider">
        <li class="nav-item">
            <a class="nav-link" href="{{ route('websystem.edit', 'websystem') }}">
                <i class="fas fa-tools"></i>
                <span><strong>{{ __('Settings') }}</strong></span>
            </a>
        </li>
    @endcanany
    {{-- <hr class="sidebar-divider">
    <li class="nav-item @if (request()->routeIs('about')) active @endif">
        <a class="nav-link" href="{{ route('about') }}">
            <i class="fas fa-fw fa-eye"></i>
            <span>{{ __('About') }}</span></a>
    </li> --}}

    <!-- Sidebar Toggler (Sidebar) -->
    <div class="text-center d-none d-md-inline pt-4">
        <button class="rounded-circle border-0" id="sidebarToggle"></button>
    </div>

</ul>
<!-- End of Sidebar -->
