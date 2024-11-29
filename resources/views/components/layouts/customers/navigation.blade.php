<!-- Sidebar -->
<ul class="navbar-nav bg-gradient-info sidebar sidebar-dark accordion" id="accordionSidebar">

    <!-- Sidebar - Brand -->
    <a class="sidebar-brand d-flex align-items-center justify-content-center" href="/">
        <div class="sidebar-brand-icon rotate-n-15">
            <i class="fas fa-rss"></i>
        </div>
        <div class="sidebar-brand-text mx-3" style="text-transform: none">{{ $websystem->title }}</div>
    </a>

    <!-- Divider -->
    <hr class="sidebar-divider my-0">

    <!-- Nav Item - Dashboard -->
    <li class="nav-item @if (request()->routeIs('customer.dashboard')) active @endif">
        <a class="nav-link" href="{{ route('customer.dashboard') }}">
            <i class="fas fa-home"></i>
            <span>{{ __('Home') }}</span>
        </a>
    </li>

    <!-- Nav Item - Pages Collapse Menu -->
    <li class="nav-item {{ Request::is('customer/billings*') ? 'active' : '' }}">
        <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapsePayment"
            aria-expanded="true" aria-controls="collapsePayment" style="padding-top: inherit;">
            <i class="fas fa-stream"></i>
            <span>{{ __('Payments') }}</span>
        </a>
        <div id="collapsePayment" class="collapse {{ Request::is('customer/billings*') ?: '' }}"
            aria-labelledby="headingPayment" data-parent="#accordionSidebar">

            <div class="bg-white py-2 collapse-inner rounded">
                <a class="collapse-item @if (Request::is('customer/billings/payments*')) active @endif "
                    href="{{ route('customer.payment.index') }}">Billing</a>
                @if ($paymentGateway == 'enable')
                    <a class="collapse-item @if (Request::is('customer/billings/orders*')) active @endif "
                        href="{{ route('customer.order.index') }}">Orders</a>
                @endif
            </div>

        </div>
    </li>
</ul>
<!-- End of Sidebar -->
