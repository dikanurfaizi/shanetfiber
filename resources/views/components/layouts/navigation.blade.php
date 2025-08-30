<!-- Sidebar -->
<ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">

    <!-- Sidebar - Brand -->
    <a class="sidebar-brand d-flex align-items-center justify-content-center" href="{{ route('home') }}">
        <div class="sidebar-brand-icon rotate-n-15">
            <i class="fas fa-rss"></i>
        </div>
        <div class="sidebar-brand-text mx-3">Customer Management</div>
    </a>
    <hr class="sidebar-divider my-0">

    <!-- Dashboard -->
    <li class="nav-item @if (request()->routeIs('home')) active @endif">
        <a class="nav-link" href="{{ route('home') }}">
            <i class="fas fa-home"></i>
            <span>{{ __('Home') }}</span>
        </a>
    </li>

    <!-- Customers -->
    @canany(['customer-list', 'customer-edit', 'customer-delete'])
        <li class="nav-item @if (request()->routeIs('customers*')) active @endif">
            <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseCustomer"
                aria-expanded="true" aria-controls="collapseCustomer">
                <i class="fas fa-users"></i>
                <span>{{ __('navigation.customers.nav-item') }}</span>
            </a>
            <div id="collapseCustomer" class="collapse @if (request()->routeIs('customers*')) show @endif"
                aria-labelledby="headingCustomer" data-parent="#accordionSidebar">
                <div class="bg-white py-2 collapse-inner rounded">
                    @can('customer-create')
                        <a class="collapse-item @if (request()->routeIs('customers.create')) active @endif"
                            href="{{ route('customers.create') }}">{{ __('navigation.customers.create') }}</a>
                    @endcan
                    <a class="collapse-item @if (request()->routeIs('customers.index')) active @endif"
                        href="{{ route('customers.index') }}">{{ __('navigation.customers.index') }}</a>
                </div>

                <div class="bg-white py-2 collapse-inner rounded">
                    <a class="collapse-item @if (request()->routeIs('members.index')) active @endif"
                        href="{{ route('members.index') }}">{{ __('navigation.members.index') }}</a>
                    <a class="collapse-item @if (request()->routeIs('customers.trashed')) active @endif"
                        href="{{ route('customers.trashed') }}">{{ __('navigation.customers.trashed') }}</a>
                </div>
            </div>
        </li>
    @endcanany

    <!-- Subscriptions -->
    @canany(['customer-subscription-list', 'subscription-duration-list'])
        <li class="nav-item @if (request()->routeIs('customerSubscriptions*') || request()->routeIs('subscriptionDurations*')) active @endif">
            <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseSubscriptions"
                aria-expanded="true" aria-controls="collapseSubscriptions">
                <i class="fas fa-layer-group"></i>
                <span>{{ __('navigation.subscriptions.nav-item') }}</span>
            </a>
            <div id="collapseSubscriptions" class="collapse @if (request()->routeIs('customerSubscriptions*') || request()->routeIs('subscriptionDurations*')) show @endif"
                aria-labelledby="headingSubscriptions" data-parent="#accordionSidebar">
                <div class="bg-white py-2 collapse-inner rounded">
                    <a class="collapse-item @if (request()->routeIs('customerSubscriptions.index')) active @endif"
                        href="{{ route('customerSubscriptions.index') }}">
                        {{ __('navigation.subscriptions.customer_subscriptions.index') }}</a>
                    <a class="collapse-item @if (request()->routeIs('subscriptionDurations.index')) active @endif"
                        href="{{ route('subscriptionDurations.index') }}">
                        {{ __('navigation.subscriptions.subscription_durations.index') }}</a>
                </div>
            </div>
        </li>
    @endcanany

    <!-- Billing -->
    @canany(['billing-edit', 'billing-delete'])
        <li class="nav-item @if (request()->routeIs('billings*') || request()->routeIs('billingHistory*')) active @endif">
            <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseBilling"
                aria-expanded="true" aria-controls="collapseBilling">
                <i class="fas fa-layer-group"></i>
                <span>{{ __('navigation.billings.nav-item') }}</span>
            </a>
            <div id="collapseBilling" class="collapse @if (request()->routeIs('billings*') || request()->routeIs('billingHistory*')) show @endif"
                aria-labelledby="headingBilling" data-parent="#accordionSidebar">
                @can('billing-create')
                    <div class="bg-white py-2 collapse-inner rounded">
                        <a class="collapse-item @if (request()->routeIs('billings.create')) active @endif"
                            href="{{ route('billings.create') }}">{{ __('navigation.billings.create') }}</a>
                        <a class="collapse-item @if (request()->routeIs('billings.create.one')) active @endif"
                            href="{{ route('billings.create.one') }}">{{ __('navigation.billings.add') }}</a>
                    </div>
                @endcanany
                @canany(['billing-list'])
                    <div class="bg-white py-2 collapse-inner rounded">
                        <a class="collapse-item @if (request()->routeIs('billings.index')) active @endif"
                            href="{{ route('billings.index') }}">
                            {{ __('navigation.billings.active_billing') }}</a>
                        <a class="collapse-item @if (request()->routeIs('billingHistory.index')) active @endif"
                            href="{{ route('billingHistory.index') }}">
                            {{ __('navigation.billings.history') }}</a>
                    </div>
                @endcanany
            </div>
        </li>
    @endcanany

    <!-- Paket -->
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
                        <a class="collapse-item @if (request()->routeIs('categories.*')) active @endif"
                            href="{{ route('categories.index') }}">{{ __('navigation.pakets.package-categories') }}</a>
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

    <!-- Settings -->
    @canany(['user-list', 'user-create', 'user-edit', 'user-delete', 'role-list', 'role-edit', 'role-delete',
        'server-list', 'server-edit', 'server-delete', 'server-create'])
        <hr class="sidebar-divider">
        <li class="nav-item">
            <a class="nav-link" href="{{ route('websystem.edit', 'websystem') }}">
                <i class="fas fa-tools"></i>
                <span><strong>{{ __('Settings') }}</strong></span>
            </a>
        </li>
    @endcanany

    <!-- Sidebar Toggler -->
    <div class="text-center d-none d-md-inline pt-4">
        <button class="rounded-circle border-0" id="sidebarToggle"></button>
    </div>

</ul>
<!-- End of Sidebar -->
