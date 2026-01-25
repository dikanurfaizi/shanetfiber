<!-- Nav Item - Dashboard -->
<li class="nav-item @if (request()->routeIs('home')) active @endif">
    <a class="nav-link" href="{{ route('home') }}">
        <i class="fas fa-home"></i>
        <span>{{ __('Home') }}</span>
    </a>
</li>


<!-- Nav Item - Tables -->

<!-- Customers -->
@canany(['customer-list', 'customer-create', 'customer-edit', 'customer-delete', 'customer-trashed'])
    <li class="nav-item @if (request()->routeIs('customers*')) active @endif">
        <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseCustomer">
            <i class="fas fa-users"></i>
            <span>{{ __('navigation.customers.nav-item') }}</span>
        </a>
        <div id="collapseCustomer" class="collapse @if (request()->routeIs('customers*')) show @endif"
            data-parent="#accordionSidebar">
            <div class="bg-white py-2 collapse-inner rounded">
                @can('customer-create')
                    <a class="collapse-item @if (request()->routeIs('customers.create')) active @endif"
                        href="{{ route('customers.create') }}">
                        {{ __('navigation.customers.create') }}
                    </a>
                @endcan
                @can('customer-list')
                    <a class="collapse-item @if (request()->routeIs('customers.index')) active @endif"
                        href="{{ route('customers.index') }}">
                        {{ __('navigation.customers.index') }}
                    </a>
                @endcan
                @can('customer-trashed')
                    <a class="collapse-item @if (request()->routeIs('customers.trashed')) active @endif"
                        href="{{ route('customers.trashed') }}">
                        {{ __('navigation.customers.trashed') }}
                    </a>
                @endcan
            </div>
        </div>
    </li>
@endcanany

<!-- Memberships -->
@canany(['member-list', 'member-create'])
    <li class="nav-item @if (request()->routeIs('members*')) active @endif">
        <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseMembership">
            <i class="fas fa-id-card"></i>
            <span>{{ __('navigation.membership.nav-item') }}</span>
        </a>
        <div id="collapseMembership" class="collapse @if (request()->routeIs('members*')) show @endif"
            data-parent="#accordionSidebar">
            <div class="bg-white py-2 collapse-inner rounded">
                @can('member-create')
                    <a class="collapse-item @if (request()->routeIs('members.create')) active @endif"
                        href="{{ route('members.create') }}">
                        {{ __('navigation.membership.create') }}
                    </a>
                @endcan
                @can('member-list')
                    <a class="collapse-item @if (request()->routeIs('members.index')) active @endif"
                        href="{{ route('members.index') }}">
                        {{ __('navigation.membership.index') }}
                    </a>
                @endcan
            </div>
        </div>
    </li>
@endcanany

<!-- Packages -->
@canany(['package-list', 'package-create', 'package-edit'])
    <li class="nav-item @if (request()->routeIs('packages*') || request()->routeIs('categories*')) active @endif">
        <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapsePackages">
            <i class="fas fa-box"></i>
            <span>{{ __('navigation.packages.nav-item') }}</span>
        </a>
        <div id="collapsePackages" class="collapse @if (request()->routeIs('packages*') || request()->routeIs('categories*')) show @endif"
            data-parent="#accordionSidebar">
            <div class="bg-white py-2 collapse-inner rounded">
                @can('package-create')
                    <a class="collapse-item @if (request()->routeIs('packages.create')) active @endif"
                        href="{{ route('packages.create') }}">
                        {{ __('navigation.packages.create') }}
                    </a>
                @endcan
                @can('package-category-list')
                    <a class="collapse-item @if (request()->routeIs('categories.index')) active @endif"
                        href="{{ route('categories.index') }}">
                        {{ __('navigation.packages.package-categories') }}
                    </a>
                @endcan
                @can('package-list')
                    <a class="collapse-item @if (request()->routeIs('packages.index')) active @endif"
                        href="{{ route('packages.index') }}">
                        {{ __('navigation.packages.index') }}
                    </a>
                @endcan
            </div>
        </div>
    </li>
@endcanany

<!-- Addons -->
@canany(['addon-view', 'addon-create', 'addon-edit', 'addon-enable', 'addon-disable'])
    <li class="nav-item @if (request()->routeIs('addons*')) active @endif">
        <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseAddons">
            <i class="fas fa-puzzle-piece"></i>
            <span>{{ __('navigation.addons.nav-item') }}</span>
        </a>
        <div id="collapseAddons" class="collapse @if (request()->routeIs('addons*')) show @endif"
            data-parent="#accordionSidebar">
            <div class="bg-white py-2 collapse-inner rounded">
                @can('addon-create')
                    <a class="collapse-item @if (request()->routeIs('addons.create')) active @endif"
                        href="{{ route('addons.create') }}">
                        {{ __('navigation.addons.create') }}
                    </a>
                @endcan
                @can('addon-view')
                    <a class="collapse-item @if (request()->routeIs('addons.index')) active @endif"
                        href="{{ route('addons.index') }}">
                        {{ __('navigation.addons.index') }}
                    </a>
                @endcan
            </div>
        </div>
    </li>
@endcanany

<hr class="sidebar-divider">

@canany(['billing-list', 'billing-create', 'billing-edit'])
    <li class="nav-item @if (request()->routeIs('billings*') || request()->routeIs('billingHistory*')) active @endif">
        <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseBilling">
            <i class="fas fa-layer-group"></i>
            <span>{{ __('navigation.billings.nav-item') }}</span>
        </a>
        <div id="collapseBilling" class="collapse @if (request()->routeIs('billings*') || request()->routeIs('billingHistory*')) show @endif"
            data-parent="#accordionSidebar">
            <div class="bg-white py-2 collapse-inner rounded">
                @can('billing-create')
                    <a class="collapse-item @if (request()->routeIs('billings.create')) active @endif"
                        href="{{ route('billings.create') }}">
                        {{ __('navigation.billings.create') }}
                    </a>
                @endcan
                @can('billing-list')
                    <a class="collapse-item @if (request()->routeIs('billings.index')) active @endif"
                        href="{{ route('billings.index') }}">
                        {{ __('navigation.billings.active_billing') }}
                    </a>
                    <a class="collapse-item @if (request()->routeIs('billingHistory.index')) active @endif"
                        href="{{ route('billingHistory.index') }}">
                        {{ __('navigation.billings.history') }}
                    </a>
                @endcan
            </div>
        </div>
    </li>
@endcanany

<!-- Reports -->
@canany(['report-view'])
    <li class="nav-item @if (request()->routeIs('reports*')) active @endif">
        <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseReports">
            <i class="fas fa-chart-bar"></i>
            <span>{{ __('navigation.reports.nav-item') }}</span>
        </a>
        <div id="collapseReports" class="collapse @if (request()->routeIs('reports*')) show @endif"
            data-parent="#accordionSidebar">
            <div class="bg-white py-2 collapse-inner rounded">
                <a class="collapse-item @if (request()->routeIs('reports.kpi')) active @endif"
                    href="{{ route('reports.kpi') }}">
                    {{ __('navigation.reports.kpi.index') }}
                </a>
                <a class="collapse-item @if (request()->routeIs('reports.internet.monthly')) active @endif"
                    href="{{ route('reports.internet.monthly') }}">
                    {{ __('navigation.reports.internet-monthly.index') }}
                </a>
            </div>
        </div>
    </li>
@endcanany

<hr class="sidebar-divider">

<!-- ======================
         OPERASIONAL
    ======================= -->

<!-- Networks -->
@canany(['olt-list', 'ont-list'])
    <li class="nav-item @if (request()->routeIs('olts*') || request()->routeIs('onts*')) active @endif">
        <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseNetworks">
            <i class="fas fa-server"></i>
            <span>{{ __('navigation.network.nav-item') }}</span>
        </a>
        <div id="collapseNetworks" class="collapse @if (request()->routeIs('olts*') || request()->routeIs('onts*')) show @endif"
            data-parent="#accordionSidebar">
            <div class="bg-white py-2 collapse-inner rounded">
                <a class="collapse-item @if (request()->routeIs('olts.index')) active @endif"
                    href="{{ route('olts.index') }}">
                    {{ __('navigation.network.olt-managements.index') }}
                </a>
                <a class="collapse-item @if (request()->routeIs('onts.index')) active @endif"
                    href="{{ route('onts.index') }}">
                    {{ __('navigation.network.onu-managements.index') }}
                </a>
            </div>
        </div>
    </li>
@endcanany

<hr class="sidebar-divider">

<!-- ======================
         SYSTEM
    ======================= -->

<!-- Settings -->
@canany(['user-list', 'role-list', 'server-list'])
    <li class="nav-item">
        <a class="nav-link" href="{{ route('websystem.edit', 'websystem') }}">
            <i class="fas fa-tools"></i>
            <span><strong>Settings</strong></span>
        </a>
    </li>
@endcanany

<!-- Sidebar Toggle -->
<div class="text-center d-none d-md-inline pt-4">
    <button class="rounded-circle border-0" id="sidebarToggle"></button>
</div>

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            if (window.innerWidth < 768) {
                document
                    .querySelectorAll('#accordionSidebar .collapse.show')
                    .forEach(el => el.classList.remove('show'));
            }
        });
    </script>
@endpush
