<!-- Sidebar -->
<ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">

    <!-- Brand -->
    <a class="sidebar-brand d-flex align-items-center justify-content-center" href="{{ route('home') }}">
        <div class="sidebar-brand-icon rotate-n-15">
            <i class="fas fa-rss"></i>
        </div>
        <div class="sidebar-brand-text mx-3">{{ __('navigation.header.name') }}</div>
    </a>

    <hr class="sidebar-divider my-0">

    <!-- Home -->
    <li class="nav-item @if (request()->routeIs('home')) active @endif">
        <a class="nav-link" href="{{ route('home') }}">
            <i class="fas fa-home"></i>
            <span>{{ __('navigation.home.nav-item') }}</span>
        </a>
    </li>

    <!-- ======================
         CORE BUSINESS
    ======================= -->

    <!-- Customers -->
    @canany(['customer-list', 'customer-create', 'customer-edit', 'customer-delete', 'customer-trashed'])
        <li class="nav-item @if (request()->routeIs('customers*')) active @endif">
            <a class="nav-link collapsed" href="#" data-bs-toggle="collapse" data-bs-target="#collapseCustomer">
                <i class="fas fa-users"></i>
                <span>{{ __('navigation.customers.nav-item') }}</span>
            </a>
            <div id="collapseCustomer" class="collapse @if (request()->routeIs('customers*')) show @endif"
                data-bs-parent="#accordionSidebar">
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
            <a class="nav-link collapsed" href="#" data-bs-toggle="collapse" data-bs-target="#collapseMembership">
                <i class="fas fa-id-card"></i>
                <span>{{ __('navigation.membership.nav-item') }}</span>
            </a>
            <div id="collapseMembership" class="collapse @if (request()->routeIs('members*')) show @endif"
                data-bs-parent="#accordionSidebar">
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
    @canany(['package-list', 'package-create', 'package-edit', 'package-category-list', 'secret-list'])
        <li class="nav-item @if (request()->routeIs('packages*') || request()->routeIs('categories*')) active @endif">
            <a class="nav-link collapsed" href="#" data-bs-toggle="collapse" data-bs-target="#collapsePackages">
                <i class="fas fa-box"></i>
                <span>{{ __('navigation.packages.nav-item') }}</span>
            </a>
            <div id="collapsePackages" class="collapse @if (request()->routeIs('packages*') || request()->routeIs('categories*')) show @endif"
                data-bs-parent="#accordionSidebar">
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

    @canany(['secret-create', 'secret-list'])
        <li class="nav-item @if (request()->routeIs('ppp-profiles*')) active @endif">
            <a class="nav-link collapsed" href="#" data-bs-toggle="collapse" data-bs-target="#collapseProfiles">
                <i class="fas fa-users"></i>
                <span>{{ __('navigation.profiles.nav-item') }}</span>
            </a>
            <div id="collapseProfiles" class="collapse @if (request()->routeIs('ppp-profiles*')) show @endif"
                data-bs-parent="#accordionSidebar">
                <div class="bg-white py-2 collapse-inner rounded">
                    @can('secret-create')
                        <a class="collapse-item @if (request()->routeIs('ppp-profiles.create')) active @endif"
                            href="{{ route('ppp-profiles.create') }}">
                            {{ __('navigation.profiles.create') }}
                        </a>
                    @endcan
                    @can('secret-list')
                        <a class="collapse-item @if (request()->routeIs('ppp-profiles.index')) active @endif"
                            href="{{ route('ppp-profiles.index') }}">
                            {{ __('navigation.profiles.index') }}
                        </a>
                    @endcan
                </div>
            </div>
        </li>
    @endcanany

    <!-- Addons -->
    @canany(['addon-view', 'addon-create', 'addon-edit', 'addon-enable', 'addon-disable'])
        <li class="nav-item @if (request()->routeIs('addons*')) active @endif">
            <a class="nav-link collapsed" href="#" data-bs-toggle="collapse" data-bs-target="#collapseAddons">
                <i class="fas fa-puzzle-piece"></i>
                <span>{{ __('navigation.addons.nav-item') }}</span>
            </a>
            <div id="collapseAddons" class="collapse @if (request()->routeIs('addons*')) show @endif"
                data-bs-parent="#accordionSidebar">
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

    <!-- ======================
         TRANSACTIONAL
    ======================= -->

    <!-- Billing -->
    @canany(['billing-list', 'billing-create', 'billing-edit'])
        <li class="nav-item @if (request()->routeIs('billings*') || request()->routeIs('billingHistory*')) active @endif">
            <a class="nav-link collapsed" href="#" data-bs-toggle="collapse" data-bs-target="#collapseBilling">
                <i class="fas fa-layer-group"></i>
                <span>{{ __('navigation.billings.nav-item') }}</span>
            </a>
            <div id="collapseBilling" class="collapse @if (request()->routeIs('billings*') || request()->routeIs('billingHistory*')) show @endif"
                data-bs-parent="#accordionSidebar">
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
            <a class="nav-link collapsed" href="#" data-bs-toggle="collapse" data-bs-target="#collapseReports">
                <i class="fas fa-chart-bar"></i>
                <span>{{ __('navigation.reports.nav-item') }}</span>
            </a>
            <div id="collapseReports" class="collapse @if (request()->routeIs('reports*')) show @endif"
                data-bs-parent="#accordionSidebar">
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

    @canany(['conversation-list'])
        {{-- Deteksi active diperluas ke semua sub-route whatsapp.conversations --}}
        <li class="nav-item @if (request()->routeIs('whatsapp.conversations*')) active @endif">
            <a class="nav-link d-flex align-items-center justify-content-between"
                href="{{ route('whatsapp.conversations.index') }}">
                <div>
                    {{-- Gunakan warna hijau WhatsApp agar senada dengan Topbar --}}
                    <i class="fab fa-whatsapp me-2" style="color: #25D366; font-size: 1.1rem;"></i>
                    <span>{{ __('navigation.conversations.index') }}</span>
                </div>

                {{-- 🟢 KONDISI TAMBAHAN: Munculkan badge jumlah unread di sidebar jika ada pesan masuk --}}
                @if (isset($unreadChatCount) && $unreadChatCount > 0)
                    <span id="sidebar-unread-badge" class="badge bg-danger rounded-pill px-2 py-1"
                        style="font-size: 0.7rem; font-weight: 700;">
                        {{ $unreadChatCount }}
                    </span>
                @endif
            </a>
        </li>
    @endcanany

    <hr class="sidebar-divider">

    <!-- ======================
         OPERASIONAL
    ======================= -->

    <!-- Networks -->
    @canany(['olt-list', 'ont-list'])
        <li class="nav-item @if (request()->routeIs('olts*') || request()->routeIs('onts*')) active @endif">
            <a class="nav-link collapsed" href="#" data-bs-toggle="collapse" data-bs-target="#collapseNetworks">
                <i class="fas fa-server"></i>
                <span>{{ __('navigation.network.nav-item') }}</span>
            </a>
            <div id="collapseNetworks" class="collapse @if (request()->routeIs('olts*') || request()->routeIs('onts*')) show @endif"
                data-bs-parent="#accordionSidebar">
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
                <span><strong>{{ __('navigation.settings.nav-item') }}</strong></span>
            </a>
        </li>
    @endcanany

    <!-- Sidebar Toggle -->
    <div class="text-center d-none d-md-inline pt-4">
        <button class="rounded-circle border-0" id="sidebarToggle"></button>
    </div>

</ul>
<!-- End Sidebar -->
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
