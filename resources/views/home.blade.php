<x-layouts.app-layout title="Dashboard">

    <div class="card-header py-3">
        <h5 class="m-0 font-weight-bold text-primary">
            Dashboard
        </h5>
    </div>
    <div class="row col-lg-12">
        <div class="card-body">
            <div class="row">

                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card border-start-lg border-start-primary h-100">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">
                                <i class="fas fa-fw fa-server text-primary"></i> Server :
                                @if ($mikrotiks->count() > 0)
                                    <a href="{{ route('servers.index') }}">{{ $mikrotiks->count() }}</a>
                                @else
                                    0
                                @endif
                                <span class="text-xs fw-bold text-success d-inline-flex align-items-center">
                                    servers
                                </span>
                            </h6>

                        </div>
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-grow-1">

                                    <div class="small fw-bold text-primary mb-1">
                                        <span><i class="fas fa-server text-primary"></i>
                                            <span>
                                                <a href="{{ route('servers.index') }}">
                                                    @if ($mikrotiks->count() > 0)
                                                        {{ $mikrotiks->count() }}
                                                    @else
                                                        0
                                                    @endif
                                                    Servers
                                                </a>
                                            </span>
                                        </span>
                                        <div>
                                            <i class="fas fa-check-circle text-success"></i>
                                            <span>
                                                <a href="{{ route('servers.index', ['disabled' => 'false']) }}">
                                                    @if ($routersEnable->count() > 0)
                                                        {{ $routersEnable->count() }}
                                                    @else
                                                        0
                                                    @endif
                                                    Enable
                                                </a>
                                            </span>
                                        </div>
                                        <div>
                                            <i class="fas fa-ban text-danger"></i>
                                            <span>
                                                <a href="{{ route('servers.index', ['disabled' => 'true']) }}">
                                                    @if ($mikrotiks->count() - $routersEnable->count() > 0)
                                                        {{ $mikrotiks->count() - $routersEnable->count() }}
                                                    @else
                                                        0
                                                    @endif
                                                    Disable
                                                </a>
                                            </span>
                                        </div>
                                    </div>


                                </div>
                                <div class="ms-2"><svg class="svg-inline--fa fa-dollar-sign fa-2x text-gray-200"
                                        aria-hidden="true" focusable="false" data-prefix="fas" data-icon="dollar-sign"
                                        role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 320 512"
                                        data-fa-i2svg="">
                                        <path fill="currentColor"
                                            d="M146 0c17.7 0 32 14.3 32 32V67.7c1.6 .2 3.1 .4 4.7 .7c.4 .1 .7 .1 1.1 .2l48 8.8c17.4 3.2 28.9 19.9 25.7 37.2s-19.9 28.9-37.2 25.7l-47.5-8.7c-31.3-4.6-58.9-1.5-78.3 6.2s-27.2 18.3-29 28.1c-2 10.7-.5 16.7 1.2 20.4c1.8 3.9 5.5 8.3 12.8 13.2c16.3 10.7 41.3 17.7 73.7 26.3l2.9 .8c28.6 7.6 63.6 16.8 89.6 33.8c14.2 9.3 27.6 21.9 35.9 39.5c8.5 17.9 10.3 37.9 6.4 59.2c-6.9 38-33.1 63.4-65.6 76.7c-13.7 5.6-28.6 9.2-44.4 11V480c0 17.7-14.3 32-32 32s-32-14.3-32-32V445.1c-.4-.1-.9-.1-1.3-.2l-.2 0 0 0c-24.4-3.8-64.5-14.3-91.5-26.3C4.9 411.4-2.4 392.5 4.8 376.3s26.1-23.4 42.2-16.2c20.9 9.3 55.3 18.5 75.2 21.6c31.9 4.7 58.2 2 76-5.3c16.9-6.9 24.6-16.9 26.8-28.9c1.9-10.6 .4-16.7-1.3-20.4c-1.9-4-5.6-8.4-13-13.3c-16.4-10.7-41.5-17.7-74-26.3l-2.8-.7 0 0C105.4 279.3 70.4 270 44.4 253c-14.2-9.3-27.5-22-35.8-39.6C.3 195.4-1.4 175.4 2.5 154.1C9.7 116 38.3 91.2 70.8 78.3c13.3-5.3 27.9-8.9 43.2-11V32c0-17.7 14.3-32 32-32z">
                                        </path>
                                    </svg><!-- <i class="fas fa-dollar-sign fa-2x text-gray-200"></i> Font Awesome fontawesome.com -->
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card border-start-lg border-start-primary h-100">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">
                                <i class="fas fa-fw fa-server text-primary"></i> Server Status
                            </h6>

                        </div>
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-grow-1">

                                    <div class="small fw-bold text-primary mb-1">
                                        <span><i class="fas fa-bullseye text-success"></i> {{ $countRouterOnline }}
                                            Online</span>
                                    </div>

                                    <div class="small fw-bold text-primary mb-1">
                                        <span><i class="fas fa-power-off text-danger"></i> {{ $countRouterOffline }}
                                            Offline</span>
                                    </div>

                                </div>
                                <div class="ms-2"><svg class="svg-inline--fa fa-dollar-sign fa-2x text-gray-200"
                                        aria-hidden="true" focusable="false" data-prefix="fas" data-icon="dollar-sign"
                                        role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 320 512"
                                        data-fa-i2svg="">
                                        <path fill="currentColor"
                                            d="M146 0c17.7 0 32 14.3 32 32V67.7c1.6 .2 3.1 .4 4.7 .7c.4 .1 .7 .1 1.1 .2l48 8.8c17.4 3.2 28.9 19.9 25.7 37.2s-19.9 28.9-37.2 25.7l-47.5-8.7c-31.3-4.6-58.9-1.5-78.3 6.2s-27.2 18.3-29 28.1c-2 10.7-.5 16.7 1.2 20.4c1.8 3.9 5.5 8.3 12.8 13.2c16.3 10.7 41.3 17.7 73.7 26.3l2.9 .8c28.6 7.6 63.6 16.8 89.6 33.8c14.2 9.3 27.6 21.9 35.9 39.5c8.5 17.9 10.3 37.9 6.4 59.2c-6.9 38-33.1 63.4-65.6 76.7c-13.7 5.6-28.6 9.2-44.4 11V480c0 17.7-14.3 32-32 32s-32-14.3-32-32V445.1c-.4-.1-.9-.1-1.3-.2l-.2 0 0 0c-24.4-3.8-64.5-14.3-91.5-26.3C4.9 411.4-2.4 392.5 4.8 376.3s26.1-23.4 42.2-16.2c20.9 9.3 55.3 18.5 75.2 21.6c31.9 4.7 58.2 2 76-5.3c16.9-6.9 24.6-16.9 26.8-28.9c1.9-10.6 .4-16.7-1.3-20.4c-1.9-4-5.6-8.4-13-13.3c-16.4-10.7-41.5-17.7-74-26.3l-2.8-.7 0 0C105.4 279.3 70.4 270 44.4 253c-14.2-9.3-27.5-22-35.8-39.6C.3 195.4-1.4 175.4 2.5 154.1C9.7 116 38.3 91.2 70.8 78.3c13.3-5.3 27.9-8.9 43.2-11V32c0-17.7 14.3-32 32-32z">
                                        </path>
                                    </svg><!-- <i class="fas fa-dollar-sign fa-2x text-gray-200"></i> Font Awesome fontawesome.com -->
                                </div>
                            </div>
                        </div>
                    </div>
                </div>


                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card border-start-lg border-start-primary h-100">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">
                                <i class="fas fa-fw fa-users text-primary"></i> Customers :
                                @if ($totalCustomers > 0)
                                    <a href="{{ route('customers.index') }}">{{ $totalCustomers }}</a>
                                @else
                                    0
                                @endif
                                <span class="text-xs fw-bold text-success d-inline-flex align-items-center">
                                    customers
                                </span>
                            </h6>

                        </div>
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-grow-1">


                                    <div class="small fw-bold text-primary mb-1">
                                        <span>
                                            <a href="{{ route('customers.index', ['status' => 'false']) }}">
                                                <i class="fas fa-check-circle text-success"></i>
                                                @if ($totalEnabled > 0)
                                                    {{ $totalEnabled }}
                                                @else
                                                    0
                                                @endif Enable
                                            </a>
                                        </span>
                                    </div>
                                    <div class="small fw-bold text-primary mb-1">
                                        <span>
                                            <a href="{{ route('customers.index', ['status' => 'true']) }}">
                                                <i class="fas fa-ban text-danger"></i>
                                                @if ($totalDisabled > 0)
                                                    {{ $totalDisabled }}
                                                @else
                                                    0
                                                @endif Disable
                                        </span>
                                        </a>
                                    </div>
                                    <div class="small fw-bold text-primary mb-1">
                                        <span>
                                            <a href="{{ route('customers.index', ['status' => 'deactivated']) }}">
                                                <i class="fas fa-ban text-danger"></i>
                                                @if ($totalNonActiveDisabled > 0)
                                                    {{ $totalNonActiveDisabled }}
                                                @else
                                                    0
                                                @endif Deactivation
                                            </a>
                                        </span>
                                    </div>
                                </div>
                                <div class="ms-2"><svg class="svg-inline--fa fa-dollar-sign fa-2x text-gray-200"
                                        aria-hidden="true" focusable="false" data-prefix="fas" data-icon="dollar-sign"
                                        role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 320 512"
                                        data-fa-i2svg="">
                                        <path fill="currentColor"
                                            d="M146 0c17.7 0 32 14.3 32 32V67.7c1.6 .2 3.1 .4 4.7 .7c.4 .1 .7 .1 1.1 .2l48 8.8c17.4 3.2 28.9 19.9 25.7 37.2s-19.9 28.9-37.2 25.7l-47.5-8.7c-31.3-4.6-58.9-1.5-78.3 6.2s-27.2 18.3-29 28.1c-2 10.7-.5 16.7 1.2 20.4c1.8 3.9 5.5 8.3 12.8 13.2c16.3 10.7 41.3 17.7 73.7 26.3l2.9 .8c28.6 7.6 63.6 16.8 89.6 33.8c14.2 9.3 27.6 21.9 35.9 39.5c8.5 17.9 10.3 37.9 6.4 59.2c-6.9 38-33.1 63.4-65.6 76.7c-13.7 5.6-28.6 9.2-44.4 11V480c0 17.7-14.3 32-32 32s-32-14.3-32-32V445.1c-.4-.1-.9-.1-1.3-.2l-.2 0 0 0c-24.4-3.8-64.5-14.3-91.5-26.3C4.9 411.4-2.4 392.5 4.8 376.3s26.1-23.4 42.2-16.2c20.9 9.3 55.3 18.5 75.2 21.6c31.9 4.7 58.2 2 76-5.3c16.9-6.9 24.6-16.9 26.8-28.9c1.9-10.6 .4-16.7-1.3-20.4c-1.9-4-5.6-8.4-13-13.3c-16.4-10.7-41.5-17.7-74-26.3l-2.8-.7 0 0C105.4 279.3 70.4 270 44.4 253c-14.2-9.3-27.5-22-35.8-39.6C.3 195.4-1.4 175.4 2.5 154.1C9.7 116 38.3 91.2 70.8 78.3c13.3-5.3 27.9-8.9 43.2-11V32c0-17.7 14.3-32 32-32z">
                                        </path>
                                    </svg><!-- <i class="fas fa-dollar-sign fa-2x text-gray-200"></i> Font Awesome fontawesome.com -->
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card border-start-lg border-start-primary h-100">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">
                                <i class="fas fa-fw fa-users text-primary"></i> Customers Status
                            </h6>
                        </div>
                        <div class="card-body">

                            <div class="d-flex align-items-center">
                                <div class="flex-grow-1">

                                    <div class="small fw-bold text-primary mb-1">
                                        <span>
                                            <a href="{{ route('customers.index', ['status' => 'online']) }}">
                                                <i class="fas fa-check-circle text-success"></i>
                                                @if ($sumCustomersOnline - $sumCustomersIsolir > 0)
                                                    {{ $sumCustomersOnline - $sumCustomersIsolir }}
                                                @else
                                                    0
                                                @endif {{ __('connected') }}
                                            </a>
                                        </span>
                                    </div>
                                    <div class="small fw-bold text-primary mb-1">
                                        <span>
                                            <a href="{{ route('customers.index', ['status' => 'isolir']) }}">
                                                <i class="fas fa-ban text-danger"></i>
                                                @if ($sumCustomersIsolir - $totalNonActiveDisabled > 0)
                                                    {{ $sumCustomersIsolir - $totalNonActiveDisabled }}
                                                @else
                                                    0
                                                @endif Isolir
                                            </a>
                                        </span>
                                    </div>

                                    <div class="small fw-bold text-primary mb-1">
                                        <span>
                                            <a href="{{ route('customers.index', ['status' => 'offline']) }}">
                                                <i class="fas fa-check-circle text-danger"></i>
                                                @if ($sumCustomersOffline > 0)
                                                    {{ $sumCustomersOffline }}
                                                @else
                                                    0
                                                @endif {{ __('disconnect') }}
                                            </a>
                                        </span>
                                    </div>

                                </div>
                                <div class="ms-2"><svg class="svg-inline--fa fa-dollar-sign fa-2x text-gray-200"
                                        aria-hidden="true" focusable="false" data-prefix="fas"
                                        data-icon="dollar-sign" role="img" xmlns="http://www.w3.org/2000/svg"
                                        viewBox="0 0 320 512" data-fa-i2svg="">
                                        <path fill="currentColor"
                                            d="M146 0c17.7 0 32 14.3 32 32V67.7c1.6 .2 3.1 .4 4.7 .7c.4 .1 .7 .1 1.1 .2l48 8.8c17.4 3.2 28.9 19.9 25.7 37.2s-19.9 28.9-37.2 25.7l-47.5-8.7c-31.3-4.6-58.9-1.5-78.3 6.2s-27.2 18.3-29 28.1c-2 10.7-.5 16.7 1.2 20.4c1.8 3.9 5.5 8.3 12.8 13.2c16.3 10.7 41.3 17.7 73.7 26.3l2.9 .8c28.6 7.6 63.6 16.8 89.6 33.8c14.2 9.3 27.6 21.9 35.9 39.5c8.5 17.9 10.3 37.9 6.4 59.2c-6.9 38-33.1 63.4-65.6 76.7c-13.7 5.6-28.6 9.2-44.4 11V480c0 17.7-14.3 32-32 32s-32-14.3-32-32V445.1c-.4-.1-.9-.1-1.3-.2l-.2 0 0 0c-24.4-3.8-64.5-14.3-91.5-26.3C4.9 411.4-2.4 392.5 4.8 376.3s26.1-23.4 42.2-16.2c20.9 9.3 55.3 18.5 75.2 21.6c31.9 4.7 58.2 2 76-5.3c16.9-6.9 24.6-16.9 26.8-28.9c1.9-10.6 .4-16.7-1.3-20.4c-1.9-4-5.6-8.4-13-13.3c-16.4-10.7-41.5-17.7-74-26.3l-2.8-.7 0 0C105.4 279.3 70.4 270 44.4 253c-14.2-9.3-27.5-22-35.8-39.6C.3 195.4-1.4 175.4 2.5 154.1C9.7 116 38.3 91.2 70.8 78.3c13.3-5.3 27.9-8.9 43.2-11V32c0-17.7 14.3-32 32-32z">
                                        </path>
                                    </svg><!-- <i class="fas fa-dollar-sign fa-2x text-gray-200"></i> Font Awesome fontawesome.com -->
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>



    @if ($lastBillings)
        <div class="card-header py-3">
            <h5 class="m-0 font-weight-bold text-primary">Billing Periode :
                {{ $lastBillingPeriode->name ?? 'No Billing Periode' }}</h5>
        </div>

        <div class="row col-lg-12">
            <div class="card-body">
                <div class="row">
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card border-start-lg border-start-primary h-100">
                            <div class="card-header py-3">
                                <h6 class="m-0 font-weight-bold text-primary">
                                    <i class="far fa-dot-circle text-success"></i> Billings
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="flex-grow-1">
                                        <div id="all-ppp-secrets-active-count" class="h5">
                                            <div id="loading">

                                                @if ($lastBillings->count() > 0)
                                                    <a
                                                        href="{{ route('billings.index') }}">{{ $lastBillings->count() }}</a>
                                                @else
                                                    0
                                                @endif
                                                <div
                                                    class="text-xs fw-bold text-success d-inline-flex align-items-center">
                                                    customers
                                                </div>
                                            </div>
                                        </div>
                                        @if ($totalBilling !== null)
                                            <div id="all-ppp-secrets-active-count" class="h5">
                                                <h6 class="m-0 font-weight-bold text-primary">
                                                    @moneyIDR($totalBilling)
                                                </h6>
                                            </div>
                                        @endif
                                        @if ($adminBillingSummary && $adminBillingSummary->count() > 0)
                                            <hr>
                                            <h6 class="m-0 font-weight-bold text-primary mb-3">
                                                Details Per Admin</h6>
                                            <ul class="list-group">
                                                @foreach ($adminBillingSummary as $adminData)
                                                    <li class="list-group-item">
                                                        <strong>{{ $adminData['admin_name'] }}</strong><br>
                                                        @if ($lastBillings->count() > 0)
                                                            <a
                                                                href="{{ route('billings.index', [
                                                                    'admin_name' => $adminData['admin_name'],
                                                                    'search_month' => Carbon\Carbon::parse($lastBillingPeriode->name)->format('F'),
                                                                    'search_year' => Carbon\Carbon::parse($lastBillingPeriode->name)->format('Y'),
                                                                ]) }}">{{ $adminData['total_customers'] }}</a>
                                                        @else
                                                            0
                                                        @endif
                                                        <div
                                                            class="text-xs fw-bold text-success d-inline-flex align-items-center">
                                                            customers
                                                        </div><br>
                                                        <div id="all-ppp-secrets-active-count" class="h5">
                                                            <h6 class="m-0 font-weight-bold text-primary">
                                                                @moneyIDR($adminData['total_billing'])
                                                            </h6>
                                                        </div>
                                                    </li>
                                                @endforeach
                                            </ul>
                                        @endif
                                    </div>
                                    <div class="ms-2">
                                        <svg class="svg-inline--fa fa-dollar-sign fa-2x text-gray-200"
                                            aria-hidden="true" focusable="false" data-prefix="fas"
                                            data-icon="dollar-sign" role="img" xmlns="http://www.w3.org/2000/svg"
                                            viewBox="0 0 320 512" data-fa-i2svg="">
                                            <path fill="currentColor"
                                                d="M146 0c17.7 0 32 14.3 32 32V67.7c1.6 .2 3.1 .4 4.7 .7c.4 .1 .7 .1 1.1 .2l48 8.8c17.4 3.2 28.9 19.9 25.7 37.2s-19.9 28.9-37.2 25.7l-47.5-8.7c-31.3-4.6-58.9-1.5-78.3 6.2s-27.2 18.3-29 28.1c-2 10.7-.5 16.7 1.2 20.4c1.8 3.9 5.5 8.3 12.8 13.2c16.3 10.7 41.3 17.7 73.7 26.3l2.9 .8c28.6 7.6 63.6 16.8 89.6 33.8c14.2 9.3 27.6 21.9 35.9 39.5c8.5 17.9 10.3 37.9 6.4 59.2c-6.9 38-33.1 63.4-65.6 76.7c-13.7 5.6-28.6 9.2-44.4 11V480c0 17.7-14.3 32-32 32s-32-14.3-32-32V445.1c-.4-.1-.9-.1-1.3-.2l-.2 0 0 0c-24.4-3.8-64.5-14.3-91.5-26.3C4.9 411.4-2.4 392.5 4.8 376.3s26.1-23.4 42.2-16.2c20.9 9.3 55.3 18.5 75.2 21.6c31.9 4.7 58.2 2 76-5.3c16.9-6.9 24.6-16.9 26.8-28.9c1.9-10.6 .4-16.7-1.3-20.4c-1.9-4-5.6-8.4-13-13.3c-16.4-10.7-41.5-17.7-74-26.3l-2.8-.7 0 0C105.4 279.3 70.4 270 44.4 253c-14.2-9.3-27.5-22-35.8-39.6C.3 195.4-1.4 175.4 2.5 154.1C9.7 116 38.3 91.2 70.8 78.3c13.3-5.3 27.9-8.9 43.2-11V32c0-17.7 14.3-32 32-32z">
                                            </path>
                                        </svg>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card border-start-lg border-start-primary h-100">
                            <div class="card-header py-3">
                                <h6 class="m-0 font-weight-bold text-primary">
                                    <i class="far fa-dot-circle text-success"></i> Payment
                                </h6>
                            </div>

                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="flex-grow-1">
                                        <div id="all-ppp-secrets-active-count" class="h5">
                                            <div id="loading">

                                                @if ($lastBillings->where('status', 'LS')->count() > 0)
                                                    <a
                                                        href="{{ route('billings.index', ['search_status=LS', 'search_month=' . Carbon\Carbon::parse($lastBillingPeriode->name)->format('F'), 'search_year=' . Carbon\Carbon::parse($lastBillingPeriode->name)->format('Y')]) }}">{{ $lastBillings->where('status', 'LS')->count() }}</a>
                                                @else
                                                    0
                                                @endif
                                                <div
                                                    class="text-xs fw-bold text-success d-inline-flex align-items-center">
                                                    customers
                                                </div>
                                            </div>
                                        </div>
                                        @if ($totalPaid !== null)
                                            <div id="all-ppp-secrets-active-count" class="h5">
                                                <h6 class="m-0 font-weight-bold text-primary">
                                                    @moneyIDR($totalPaid)
                                                </h6>
                                            </div>
                                        @endif
                                        @if ($adminBillingSummary && $adminBillingSummary->count() > 0)
                                            <hr>
                                            <h6 class="m-0 font-weight-bold text-primary mb-3">
                                                Details Per Admin</h6>
                                            <ul class="list-group">
                                                @foreach ($adminBillingSummary as $adminData)
                                                    <li class="list-group-item">
                                                        <strong>{{ $adminData['admin_name'] }}</strong><br>
                                                        @if ($lastBillings->count() > 0)
                                                            <a
                                                                href="{{ route('billings.index', [
                                                                    'admin_name' => $adminData['admin_name'],
                                                                    'search_month' => Carbon\Carbon::parse($lastBillingPeriode->name)->format('F'),
                                                                    'search_year' => Carbon\Carbon::parse($lastBillingPeriode->name)->format('Y'),
                                                                    'search_status=LS',
                                                                ]) }}">{{ $adminData['total_paid_customers'] }}</a>
                                                        @else
                                                            0
                                                        @endif
                                                        <div
                                                            class="text-xs fw-bold text-success d-inline-flex align-items-center">
                                                            customers
                                                        </div><br>
                                                        <div id="all-ppp-secrets-active-count" class="h5">
                                                            <h6 class="m-0 font-weight-bold text-primary">
                                                                @moneyIDR($adminData['total_paid'])
                                                            </h6>
                                                        </div>
                                                    </li>
                                                @endforeach
                                            </ul>
                                        @endif
                                    </div>
                                    <div class="ms-2"><svg class="svg-inline--fa fa-dollar-sign fa-2x text-gray-200"
                                            aria-hidden="true" focusable="false" data-prefix="fas"
                                            data-icon="dollar-sign" role="img" xmlns="http://www.w3.org/2000/svg"
                                            viewBox="0 0 320 512" data-fa-i2svg="">
                                            <path fill="currentColor"
                                                d="M146 0c17.7 0 32 14.3 32 32V67.7c1.6 .2 3.1 .4 4.7 .7c.4 .1 .7 .1 1.1 .2l48 8.8c17.4 3.2 28.9 19.9 25.7 37.2s-19.9 28.9-37.2 25.7l-47.5-8.7c-31.3-4.6-58.9-1.5-78.3 6.2s-27.2 18.3-29 28.1c-2 10.7-.5 16.7 1.2 20.4c1.8 3.9 5.5 8.3 12.8 13.2c16.3 10.7 41.3 17.7 73.7 26.3l2.9 .8c28.6 7.6 63.6 16.8 89.6 33.8c14.2 9.3 27.6 21.9 35.9 39.5c8.5 17.9 10.3 37.9 6.4 59.2c-6.9 38-33.1 63.4-65.6 76.7c-13.7 5.6-28.6 9.2-44.4 11V480c0 17.7-14.3 32-32 32s-32-14.3-32-32V445.1c-.4-.1-.9-.1-1.3-.2l-.2 0 0 0c-24.4-3.8-64.5-14.3-91.5-26.3C4.9 411.4-2.4 392.5 4.8 376.3s26.1-23.4 42.2-16.2c20.9 9.3 55.3 18.5 75.2 21.6c31.9 4.7 58.2 2 76-5.3c16.9-6.9 24.6-16.9 26.8-28.9c1.9-10.6 .4-16.7-1.3-20.4c-1.9-4-5.6-8.4-13-13.3c-16.4-10.7-41.5-17.7-74-26.3l-2.8-.7 0 0C105.4 279.3 70.4 270 44.4 253c-14.2-9.3-27.5-22-35.8-39.6C.3 195.4-1.4 175.4 2.5 154.1C9.7 116 38.3 91.2 70.8 78.3c13.3-5.3 27.9-8.9 43.2-11V32c0-17.7 14.3-32 32-32z">
                                            </path>
                                        </svg><!-- <i class="fas fa-dollar-sign fa-2x text-gray-200"></i> Font Awesome fontawesome.com -->
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card border-start-lg border-start-primary h-100">
                            <div class="card-header py-3">
                                <h6 class="m-0 font-weight-bold text-primary">
                                    <i class="far fa-dot-circle text-success"></i> Pay Laters
                                </h6>
                            </div>

                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="flex-grow-1">
                                        <div id="all-ppp-secrets-active-count" class="h5">
                                            <div id="loading">

                                                @if ($lastBillings->where('status', 'PL')->count() > 0)
                                                    <a
                                                        href="{{ route('billings.index', ['search_status=PL', 'search_month=' . Carbon\Carbon::parse($lastBillingPeriode->name)->format('F'), 'search_year=' . Carbon\Carbon::parse($lastBillingPeriode->name)->format('Y')]) }}">{{ $lastBillings->where('status', 'PL')->count() }}</a>
                                                @else
                                                    0
                                                @endif
                                                <div
                                                    class="text-xs fw-bold text-success d-inline-flex align-items-center">
                                                    customers
                                                </div>
                                            </div>
                                        </div>
                                        @if ($totalPaylater !== null)
                                            <div id="all-ppp-secrets-active-count" class="h5">
                                                <h6 class="m-0 font-weight-bold text-primary">
                                                    @moneyIDR($totalPaylater) <!-- Menampilkan nilai $totalPaylater -->
                                                </h6>
                                            </div>
                                        @endif
                                        @if ($adminBillingSummary && $adminBillingSummary->count() > 0)
                                            <hr>
                                            <h6 class="m-0 font-weight-bold text-primary mb-3">
                                                Details Per Admin</h6>
                                            <ul class="list-group">
                                                @foreach ($adminBillingSummary as $adminData)
                                                    <li class="list-group-item">
                                                        <strong>{{ $adminData['admin_name'] }}</strong><br>
                                                        @if ($lastBillings->count() > 0)
                                                            <a
                                                                href="{{ route('billings.index', [
                                                                    'admin_name' => $adminData['admin_name'],
                                                                    'search_month' => Carbon\Carbon::parse($lastBillingPeriode->name)->format('F'),
                                                                    'search_year' => Carbon\Carbon::parse($lastBillingPeriode->name)->format('Y'),
                                                                    'search_status=PL',
                                                                ]) }}">{{ $adminData['total_paylater_customers'] }}</a>
                                                        @else
                                                            0
                                                        @endif
                                                        <div
                                                            class="text-xs fw-bold text-success d-inline-flex align-items-center">
                                                            customers
                                                        </div><br>
                                                        <div id="all-ppp-secrets-active-count" class="h5">
                                                            <h6 class="m-0 font-weight-bold text-primary">
                                                                @moneyIDR($adminData['total_paylater'])
                                                            </h6>
                                                        </div>
                                                    </li>
                                                @endforeach
                                            </ul>
                                        @endif
                                    </div>
                                    <div class="ms-2"><svg class="svg-inline--fa fa-dollar-sign fa-2x text-gray-200"
                                            aria-hidden="true" focusable="false" data-prefix="fas"
                                            data-icon="dollar-sign" role="img" xmlns="http://www.w3.org/2000/svg"
                                            viewBox="0 0 320 512" data-fa-i2svg="">
                                            <path fill="currentColor"
                                                d="M146 0c17.7 0 32 14.3 32 32V67.7c1.6 .2 3.1 .4 4.7 .7c.4 .1 .7 .1 1.1 .2l48 8.8c17.4 3.2 28.9 19.9 25.7 37.2s-19.9 28.9-37.2 25.7l-47.5-8.7c-31.3-4.6-58.9-1.5-78.3 6.2s-27.2 18.3-29 28.1c-2 10.7-.5 16.7 1.2 20.4c1.8 3.9 5.5 8.3 12.8 13.2c16.3 10.7 41.3 17.7 73.7 26.3l2.9 .8c28.6 7.6 63.6 16.8 89.6 33.8c14.2 9.3 27.6 21.9 35.9 39.5c8.5 17.9 10.3 37.9 6.4 59.2c-6.9 38-33.1 63.4-65.6 76.7c-13.7 5.6-28.6 9.2-44.4 11V480c0 17.7-14.3 32-32 32s-32-14.3-32-32V445.1c-.4-.1-.9-.1-1.3-.2l-.2 0 0 0c-24.4-3.8-64.5-14.3-91.5-26.3C4.9 411.4-2.4 392.5 4.8 376.3s26.1-23.4 42.2-16.2c20.9 9.3 55.3 18.5 75.2 21.6c31.9 4.7 58.2 2 76-5.3c16.9-6.9 24.6-16.9 26.8-28.9c1.9-10.6 .4-16.7-1.3-20.4c-1.9-4-5.6-8.4-13-13.3c-16.4-10.7-41.5-17.7-74-26.3l-2.8-.7 0 0C105.4 279.3 70.4 270 44.4 253c-14.2-9.3-27.5-22-35.8-39.6C.3 195.4-1.4 175.4 2.5 154.1C9.7 116 38.3 91.2 70.8 78.3c13.3-5.3 27.9-8.9 43.2-11V32c0-17.7 14.3-32 32-32z">
                                            </path>
                                        </svg><!-- <i class="fas fa-dollar-sign fa-2x text-gray-200"></i> Font Awesome fontawesome.com -->
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>

                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card border-start-lg border-start-primary h-100">
                            <div class="card-header py-3">
                                <h6 class="m-0 font-weight-bold text-primary">
                                    <i class="far fa-dot-circle text-success"></i> Unpayment
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="flex-grow-1">

                                        <div id="all-ppp-secrets-active-count" class="h5">
                                            <div id="loading">

                                                @if ($lastBillings->where('status', 'BL')->count() > 0)
                                                    <a
                                                        href="{{ route('billings.index', ['search_status=BL', 'search_month=' . Carbon\Carbon::parse($lastBillingPeriode->name)->format('F'), 'search_year=' . Carbon\Carbon::parse($lastBillingPeriode->name)->format('Y')]) }}">{{ $lastBillings->where('status', 'BL')->count() }}</a>
                                                @else
                                                    0
                                                @endif
                                                <div
                                                    class="text-xs fw-bold text-success d-inline-flex align-items-center">
                                                    customers
                                                </div>
                                            </div>
                                        </div>
                                        @if ($totalUnpaid !== null)
                                            <div id="all-ppp-secrets-active-count" class="h5">
                                                <h6 class="m-0 font-weight-bold text-primary">
                                                    @moneyIDR($totalUnpaid)
                                                </h6>
                                            </div>
                                        @endif
                                        @if ($adminBillingSummary && $adminBillingSummary->count() > 0)
                                            <hr>
                                            <h6 class="m-0 font-weight-bold text-primary mb-3">
                                                Details Per Admin</h6>
                                            <ul class="list-group">
                                                @foreach ($adminBillingSummary as $adminData)
                                                    <li class="list-group-item">
                                                        <strong>{{ $adminData['admin_name'] }}</strong><br>
                                                        @if ($lastBillings->count() > 0)
                                                            <a
                                                                href="{{ route('billings.index', [
                                                                    'admin_name' => $adminData['admin_name'],
                                                                    'search_month' => Carbon\Carbon::parse($lastBillingPeriode->name)->format('F'),
                                                                    'search_year' => Carbon\Carbon::parse($lastBillingPeriode->name)->format('Y'),
                                                                    'search_status=BL',
                                                                ]) }}">{{ $adminData['total_unpaid_customers'] }}</a>
                                                        @else
                                                            0
                                                        @endif
                                                        <div
                                                            class="text-xs fw-bold text-success d-inline-flex align-items-center">
                                                            customers
                                                        </div><br>
                                                        <div id="all-ppp-secrets-active-count" class="h5">
                                                            <h6 class="m-0 font-weight-bold text-primary">
                                                                @moneyIDR($adminData['total_unpaid'])
                                                            </h6>
                                                        </div>
                                                    </li>
                                                @endforeach
                                            </ul>
                                        @endif
                                    </div>
                                    <div class="ms-2"><svg class="svg-inline--fa fa-dollar-sign fa-2x text-gray-200"
                                            aria-hidden="true" focusable="false" data-prefix="fas"
                                            data-icon="dollar-sign" role="img" xmlns="http://www.w3.org/2000/svg"
                                            viewBox="0 0 320 512" data-fa-i2svg="">
                                            <path fill="currentColor"
                                                d="M146 0c17.7 0 32 14.3 32 32V67.7c1.6 .2 3.1 .4 4.7 .7c.4 .1 .7 .1 1.1 .2l48 8.8c17.4 3.2 28.9 19.9 25.7 37.2s-19.9 28.9-37.2 25.7l-47.5-8.7c-31.3-4.6-58.9-1.5-78.3 6.2s-27.2 18.3-29 28.1c-2 10.7-.5 16.7 1.2 20.4c1.8 3.9 5.5 8.3 12.8 13.2c16.3 10.7 41.3 17.7 73.7 26.3l2.9 .8c28.6 7.6 63.6 16.8 89.6 33.8c14.2 9.3 27.6 21.9 35.9 39.5c8.5 17.9 10.3 37.9 6.4 59.2c-6.9 38-33.1 63.4-65.6 76.7c-13.7 5.6-28.6 9.2-44.4 11V480c0 17.7-14.3 32-32 32s-32-14.3-32-32V445.1c-.4-.1-.9-.1-1.3-.2l-.2 0 0 0c-24.4-3.8-64.5-14.3-91.5-26.3C4.9 411.4-2.4 392.5 4.8 376.3s26.1-23.4 42.2-16.2c20.9 9.3 55.3 18.5 75.2 21.6c31.9 4.7 58.2 2 76-5.3c16.9-6.9 24.6-16.9 26.8-28.9c1.9-10.6 .4-16.7-1.3-20.4c-1.9-4-5.6-8.4-13-13.3c-16.4-10.7-41.5-17.7-74-26.3l-2.8-.7 0 0C105.4 279.3 70.4 270 44.4 253c-14.2-9.3-27.5-22-35.8-39.6C.3 195.4-1.4 175.4 2.5 154.1C9.7 116 38.3 91.2 70.8 78.3c13.3-5.3 27.9-8.9 43.2-11V32c0-17.7 14.3-32 32-32z">
                                            </path>
                                        </svg><!-- <i class="fas fa-dollar-sign fa-2x text-gray-200"></i> Font Awesome fontawesome.com -->
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>


                </div>
            </div>
        </div>
    @endif

    @canany(['view-billings-chart-nominal-total', 'view-billings-chart-nominal-related',
        'view-billings-chart-customer-count'])
        <div id="chartWrapper1"></div>
    @endcanany

    @foreach ($routersEnable as $mikrotik)
        <div class="card-header py-3">
            <h5 class="m-0 font-weight-bold text-primary">
                <a href="{{ route('mikrotiks.show', $mikrotik->slug) }}">
                    <i id="status-{{ $mikrotik->slug }}" class="fas fa-power-off text-danger"></i>
                    {{ $mikrotik->name }}
                </a>
                <span class="badge bg-success text-white" id="boardname-{{ $mikrotik->slug }}"></span> <span
                    class="badge bg-success text-white" id="uptime-{{ $mikrotik->slug }}"></span>
            </h5>
        </div>
        <div class="row col-lg-12">
            <div class="card-body">
                <div class="row">
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card border-start-lg border-start-primary h-100">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="flex-grow-1">
                                        <div class="small fw-bold text-primary mb-1">
                                            <span><i class="fas fa-microchip text-success"></i> CPU</span>
                                        </div>
                                        <div id="all-ppp-secrets-count" class="h5">
                                            <div id="loading">
                                                <div class="progress">
                                                    <div id="cpu-{{ $mikrotik->slug }}"
                                                        class="progress-bar progress-bar-striped progress-bar-animated bg-success"
                                                        role="progressbar" style="width: 0%" aria-valuenow="50"
                                                        aria-valuemin="0" aria-valuemax="100">0% CPU </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="small fw-bold text-primary mb-1">
                                            <span><i class="fas fa-memory text-success"></i> RAM</span>
                                        </div>
                                        <div class="progress">
                                            <div id="memory-{{ $mikrotik->slug }}"
                                                class="progress-bar progress-bar-striped progress-bar-animated bg-warning"
                                                role="progressbar" style="width: 19%;">199.04MB/1GB</div>
                                        </div>
                                        <div class="small fw-bold text-primary mb-1">
                                            <span><i class="fas fa-hdd text-success"></i> HDD</span>
                                        </div>
                                        <div class="progress">
                                            <div id="hdd-{{ $mikrotik->slug }}"
                                                class="progress-bar progress-bar-striped progress-bar-animated bg-warning"
                                                role="progressbar" style="width: 19%;">199.04MB/1GB</div>
                                        </div>

                                    </div>
                                    <div class="ms-2"><svg class="svg-inline--fa fa-dollar-sign fa-2x text-gray-200"
                                            aria-hidden="true" focusable="false" data-prefix="fas"
                                            data-icon="dollar-sign" role="img" xmlns="http://www.w3.org/2000/svg"
                                            viewBox="0 0 320 512" data-fa-i2svg="">
                                            <path fill="currentColor"
                                                d="M146 0c17.7 0 32 14.3 32 32V67.7c1.6 .2 3.1 .4 4.7 .7c.4 .1 .7 .1 1.1 .2l48 8.8c17.4 3.2 28.9 19.9 25.7 37.2s-19.9 28.9-37.2 25.7l-47.5-8.7c-31.3-4.6-58.9-1.5-78.3 6.2s-27.2 18.3-29 28.1c-2 10.7-.5 16.7 1.2 20.4c1.8 3.9 5.5 8.3 12.8 13.2c16.3 10.7 41.3 17.7 73.7 26.3l2.9 .8c28.6 7.6 63.6 16.8 89.6 33.8c14.2 9.3 27.6 21.9 35.9 39.5c8.5 17.9 10.3 37.9 6.4 59.2c-6.9 38-33.1 63.4-65.6 76.7c-13.7 5.6-28.6 9.2-44.4 11V480c0 17.7-14.3 32-32 32s-32-14.3-32-32V445.1c-.4-.1-.9-.1-1.3-.2l-.2 0 0 0c-24.4-3.8-64.5-14.3-91.5-26.3C4.9 411.4-2.4 392.5 4.8 376.3s26.1-23.4 42.2-16.2c20.9 9.3 55.3 18.5 75.2 21.6c31.9 4.7 58.2 2 76-5.3c16.9-6.9 24.6-16.9 26.8-28.9c1.9-10.6 .4-16.7-1.3-20.4c-1.9-4-5.6-8.4-13-13.3c-16.4-10.7-41.5-17.7-74-26.3l-2.8-.7 0 0C105.4 279.3 70.4 270 44.4 253c-14.2-9.3-27.5-22-35.8-39.6C.3 195.4-1.4 175.4 2.5 154.1C9.7 116 38.3 91.2 70.8 78.3c13.3-5.3 27.9-8.9 43.2-11V32c0-17.7 14.3-32 32-32z">
                                            </path>
                                        </svg><!-- <i class="fas fa-dollar-sign fa-2x text-gray-200"></i> Font Awesome fontawesome.com -->
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>



            </div>
        </div>
    @endforeach

    @section('custom_scripts')
        <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
        {{-- <script src="{{ asset('assets/vendor/apexcharts/apexcharts.min.js') }}"></script> --}}
        <script>
            let chart;

            async function fetchMonthlySummary() {
                try {
                    const response = await fetch('/api/fetch-monthly-summary', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute(
                                'content')
                        }
                    });

                    const data = await response.json() || {};
                    const {
                        billingSummary = [], permissions = []
                    } = data ?? {};

                    if (!permissions.length) {
                        document.querySelector("#chartWrapper1").innerHTML =
                            "<div class='text-center p-4 text-muted'>Tidak ada data yang bisa ditampilkan.</div>";
                        return;
                    }

                    // Extract billing summary data
                    const months = billingSummary.map(item => item.month_name || '');
                    const totalPaid = billingSummary.map(item => item.total_paid || 0);
                    const totalUnpaid = billingSummary.map(item => item.total_unpaid || 0);
                    const totalPaylater = billingSummary.map(item => item.total_paylater || 0);
                    const totalCustomers = billingSummary.map(item => item.total_customers || 0);

                    // Set up the chart series based on user permissions
                    let series = [];

                    if (permissions.includes('view-billings-chart-nominal-total')) {
                        // If user can see total nominal
                        series = [{
                                name: 'Belum Bayar (Rp)',
                                data: totalUnpaid,
                                color: '#dc3545'
                            },
                            {
                                name: 'Sudah Bayar (Rp)',
                                data: totalPaid,
                                color: '#28a745'
                            }
                        ];
                    } else if (permissions.includes('view-billings-chart-nominal-related')) {
                        // If user can see related nominal only
                        series = [{
                                name: 'Belum Bayar (Rp)',
                                data: totalUnpaid,
                                color: '#dc3545'
                            },
                            {
                                name: 'Sudah Bayar (Rp)',
                                data: totalPaid,
                                color: '#28a745'
                            }
                        ];
                    } else if (permissions.includes('view-billings-chart-customer-count')) {
                        // If user can only see customer counts
                        series = [{
                                name: 'Belum Bayar',
                                data: totalUnpaid,
                                color: '#dc3545'
                            },
                            {
                                name: 'Paylater',
                                data: totalPaylater,
                                color: '#ffc107'
                            },
                            {
                                name: 'Sudah Bayar',
                                data: totalPaid,
                                color: '#28a745'
                            }
                        ];
                    }

                    const yAxisMax = Math.max(...totalPaid, ...totalUnpaid, ...totalCustomers) * 1.25;

                    const options = {
                        chart: {
                            type: 'bar',
                            height: 350,
                            stacked: true,
                            toolbar: {
                                show: false
                            }
                        },
                        title: {
                            text: 'Rekap Customer Bulanan',
                            align: 'center',
                            style: {
                                fontSize: '18px',
                                fontWeight: 'bold',
                                color: '#333'
                            }
                        },
                        series,
                        xaxis: {
                            categories: months,
                            labels: {
                                rotate: -45
                            }
                        },
                        yaxis: {
                            min: 0,
                            max: yAxisMax,
                            labels: {
                                formatter: value =>
                                    permissions.includes('view-billings-chart-nominal-total') || permissions.includes(
                                        'view-billings-chart-nominal-related') ?
                                    'Rp ' + value.toLocaleString() : `${Math.round(value)} pelanggan`
                            }
                        },
                        tooltip: {
                            y: {
                                formatter: value =>
                                    permissions.includes('view-billings-chart-nominal-total') || permissions.includes(
                                        'view-billings-chart-nominal-related') ?
                                    'Rp ' + value.toLocaleString() : `${Math.round(value)} pelanggan`
                            }
                        },
                        dataLabels: {
                            enabled: false
                        },
                        legend: {
                            position: 'top',
                            horizontalAlign: 'center'
                        }
                    };

                    if (chart) chart.destroy();
                    chart = new ApexCharts(document.querySelector("#chartWrapper1"), options);
                    chart.render();

                } catch (error) {
                    console.error('Error fetching data: ', error);
                }
            }

            fetchMonthlySummary();
            setInterval(fetchMonthlySummary, 300000);
        </script>

        <script>
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            $(document).on('click', '#check-online', function(e) {
                e.preventDefault();
                const selectedRouterId = $(this).data('id');
                const url = "{{ route('check-online', ':id') }}".replace(':id', selectedRouterId);
                $.ajax({
                    url: url,
                    method: "POST",
                    cache: false,
                    data: {
                        'id': selectedRouterId,
                    },
                    success: function(response) {
                        new Notify({
                            status: response.status,
                            title: response.title,
                            text: response.message,
                            effect: 'slide',
                            speed: 500,
                            showCloseButton: true,
                            autotimeout: 5000,
                            autoclose: true
                        });
                    },
                    error: function(data) {
                        new Notify({
                            status: 'error',
                            title: 'Gagal terkoneksi!',
                            text: data.responseJSON && data.responseJSON.message ? data.responseJSON
                                .message : 'Terjadi kesalahan',
                            effect: 'slide',
                            speed: 500,
                            showCloseButton: true,
                            autotimeout: 5000,
                            autoclose: true
                        });
                    }
                });
            });
        </script>

        @if ($onlineRouters)
            @foreach ($onlineRouters as $onlineRouter)
                <script>
                    var idServer = {{ $onlineRouter->id }};
                    $("#interface-{{ $onlineRouter->slug }}").html('');
                    $.ajax({

                        url: "{{ url('api/fetch-interfaces') }}",
                        type: "POST",
                        data: {
                            mikrotik_id: idServer,
                            _token: '{{ csrf_token() }}'
                        },
                        dataType: 'json',
                        success: function(result_server) {

                            $.each(result_server, function(key, value) {
                                $("#interface-{{ $onlineRouter->slug }}").append('<option value="' +
                                    value.name + '">' + value.name + '</option>');
                            });
                        }

                    });
                </script>
                <script>
                    $(document).ready(function() {
                        $("#loading_ppp_secret-{{ $onlineRouter->slug }}").removeClass("d-none");
                        $("#loading_ppp_non_active-{{ $onlineRouter->slug }}").removeClass("d-none");
                        $("#loading_ppp_active-{{ $onlineRouter->slug }}").removeClass("d-none");
                        //Get PPP Secret
                        var allPppSecretsCount = '';
                        var allPppActiveCount = '';

                        function updateCountPppSecret() {
                            // var offLineCustomersCount = '';
                            $.ajax({
                                url: "{{ route('get.ppp.secrets', ['mikrotik' => $onlineRouter]) }}",
                                method: "GET",
                                success: function(response) {
                                    allPppSecretsCount = response.allPppSecretsCount;
                                    $("#ppp-secrets-count-{{ $onlineRouter->slug }}").text(allPppSecretsCount);
                                },
                                error: function(xhr, status, error) {
                                    console.error(error);
                                }
                            });
                        }

                        function updateCountPppActive() {
                            $.ajax({
                                url: "{{ route('get.ppp.active', ['mikrotik' => $onlineRouter]) }}",
                                method: "GET",
                                success: function(response) {
                                    //const countPppActive = response.allPppActiveCount;
                                    allPppActiveCount = response.allPppActiveCount;
                                    // const allPppActive = Math.round(allPppActiveCount / allPppSecretsCount * 100);
                                    $("#ppp-secrets-active-count-{{ $onlineRouter->slug }}").text(
                                        allPppActiveCount);
                                    //$('#ppp-secrets-active-progress-{{ $onlineRouter->slug }}').css('width', allPppActive + '%').html(allPppActive + '%' + ' ' + allPppSecretsCount + 'Users');
                                    //  const total = allPppSecretsCount - allPppActiveCount;
                                    //  $("#all-ppp-secrets-non-active-count").text(total);
                                },
                                error: function(xhr, status, error) {
                                    console.error(error);
                                }
                            });
                        }

                        function updateCountPppNonActive() {
                            const total = allPppSecretsCount - allPppActiveCount;
                            $("#ppp-secrets-non-active-count-{{ $onlineRouter->slug }}").text(total);
                        }

                        function updateSystemResources() {
                            $.ajax({
                                url: "{{ route('mikrotik.resources', ['mikrotik' => $onlineRouter]) }}",
                                method: "GET",
                                success: function(response) {
                                    // console.log("API Response:", response); // log seluruh response ke console

                                    if (response.resources && response.resources.length > 0) {
                                        const resource = response.resources[0];
                                        // console.log("First Resource:", resource); // log data resource pertama

                                        const memoryUse = resource.totalMemory - resource.freeMemory;
                                        const memoryPercentUse = Math.round(memoryUse / resource.totalMemory * 100);
                                        const hddUse = resource.totalHdd - resource.freeHdd;
                                        const hddPercentUse = Math.round(hddUse / resource.totalHdd * 100);
                                        const uptimeRouter = resource.uptime;

                                        $('#cpu-{{ $onlineRouter->slug }}').css('width', resource.cpuLoad + '%')
                                            .html(resource.cpuLoad + '%' + ' ' + resource.cpuFrequency + 'Mhz');

                                        $('#memory-{{ $onlineRouter->slug }}').css('width', memoryPercentUse + '%')
                                            .html(resource.memoryUsage + '/' + resource.totalMemorySpace);

                                        $('#hdd-{{ $onlineRouter->slug }}').css('width', hddPercentUse + '%')
                                            .html(resource.hddUsage + '/' + resource.totalHddSpace);

                                        $('#frequency').html(resource.cpuFrequency);
                                        $('#boardname-{{ $onlineRouter->slug }}').html(resource.boardName);
                                        $('#uptime-{{ $onlineRouter->slug }}').html(uptimeRouter);
                                        $("#status-{{ $onlineRouter->slug }}").removeClass("text-danger").addClass(
                                            "text-success");
                                    } else {
                                        console.warn("No resources found in response:", response);
                                    }
                                },
                                global: false,
                                dataType: 'json',
                                error: function(xhr, status, error) {
                                    console.error("AJAX Error:", error);
                                    console.error("XHR:", xhr);
                                    console.error("Status:", status);
                                }
                            });
                        }
                        const options = {
                            chart: {
                                height: 200,
                                type: "line",
                                toolbar: {
                                    show: !1
                                }
                            },
                            dataLabels: {
                                enabled: false,
                                textAnchor: 'start',
                                formatter: function(value) {
                                    const sizes = ['bps', 'kbps', 'Mbps', 'Gbps', 'Tbps'];
                                    if (value === 0) return '0 bps';
                                    const i = parseInt(Math.floor(Math.log(value) / Math.log(1024)));
                                    return parseFloat((value / Math.pow(1024, i)).toFixed(2)) + ' ' + sizes[i];
                                },
                            },
                            series: [{
                                    name: 'Download',
                                    data: [],
                                    type: "area",
                                },
                                {
                                    name: 'Upload',
                                    data: [],
                                    type: "area",
                                },
                            ],
                            title: {
                                text: '',
                            },
                            yaxis: {
                                labels: {
                                    formatter: function(value) {
                                        const sizes = ['bps', 'kbps', 'Mbps', 'Gbps', 'Tbps'];
                                        if (value === 0) return '0 bps';
                                        const i = parseInt(Math.floor(Math.log(value) / Math.log(1024)));
                                        return parseFloat((value / Math.pow(1024, i)).toFixed(2)) + ' ' + sizes[i];
                                    },
                                },
                            },
                            colors: ["#0090fc", "#ff6666"],
                            stroke: {
                                curve: "smooth",
                                width: 2,
                                dashArray: [0, 0, 3]
                            },
                            fill: {
                                type: "soft",
                                opacity: [.15, .05, 1]
                            },
                            tooltip: {
                                y: {
                                    formatter: function(value) {
                                        const sizes = ['bps', 'kbps', 'Mbps', 'Gbps', 'Tbps'];
                                        if (value === 0) return '0 bps';
                                        const i = parseInt(Math.floor(Math.log(value) / Math.log(1024)));
                                        return parseFloat((value / Math.pow(1024, i)).toFixed(2)) + ' ' + sizes[i];
                                    },
                                }
                            },
                            markers: {
                                size: 3,
                                strokeWidth: 3,
                                hover: {
                                    size: 4,
                                    sizeOffset: 2
                                }
                            },
                            xaxis: {
                                range: 15,
                            },
                            noData: {
                                text: 'Loading data...'
                            },
                        };
                        const chart = new ApexCharts(
                            document.querySelector("#chart-{{ $onlineRouter->slug }}"),
                            options
                        );
                        chart.render();
                        const updateChart = function() {
                            const iface = $('#interface-{{ $onlineRouter->slug }}').val();
                            const url = "{{ route('mikrotik.traffic_interface', $onlineRouter) }}";

                            $.ajax({
                                url: url,
                                data: {
                                    'interface': iface
                                },
                                success: function(response) {
                                    // Periksa jika response ada dan memiliki data yang diperlukan
                                    if (response && Array.isArray(response) && response.length > 0) {
                                        const data = response[0]; // Ambil data pertama
                                        // console.log("Rx (Download):", data.Rx); // Pastikan ada data 'Rx'
                                        // console.log("Tx (Upload):", data.Tx); // Pastikan ada data 'Tx'

                                        // Jika data Rx atau Tx tidak ada atau null, set default value ke 0
                                        const downloadData = data.Rx !== null && data.Rx !== undefined ? data
                                            .Rx : 0;
                                        const uploadData = data.Tx !== null && data.Tx !== undefined ? data.Tx :
                                            0;

                                        // Cek jika elemen grafik sudah ada sebelum mencoba merender
                                        const chartWrapper = document.querySelector("#chartWrapper2");
                                        if (chartWrapper) {
                                            // Pastikan chart hanya dirender sekali
                                            if (typeof chart === 'undefined') {
                                                chart = new ApexCharts(chartWrapper, options);
                                                chart.render();
                                            } else {
                                                chart.appendData([{
                                                        name: 'Download',
                                                        data: downloadData
                                                    },
                                                    {
                                                        name: 'Upload',
                                                        data: uploadData
                                                    },
                                                ]);
                                            }
                                        } else {
                                            console.error('Elemen chartWrapper tidak ditemukan di DOM.');
                                        }
                                    } else {
                                        // console.error("Data response tidak sesuai atau kosong:", response);
                                        // Jika data tidak sesuai, tampilkan grafik kosong atau data default
                                        const chartWrapper = document.querySelector("#chartWrapper2");
                                        if (chartWrapper && typeof chart !== 'undefined') {
                                            chart.appendData([{
                                                    name: 'Download',
                                                    data: [0]
                                                },
                                                {
                                                    name: 'Upload',
                                                    data: [0]
                                                },
                                            ]);
                                        }
                                    }
                                },
                                global: false,
                                dataType: 'json',
                            });
                        };
                        async function updateData() {
                            await updateCountPppActive();
                            await updateCountPppSecret();
                            await updateCountPppNonActive();
                            await updateSystemResources();
                        }
                        updateData()
                            .then(() => {
                                //console.log("Data update completed.");
                            })
                            .catch((error) => {
                                console.error("An error occurred while updating data:", error);
                            });
                        setInterval(function() {
                            updateSystemResources();
                        }, 10000);
                        setInterval(function() {
                            updateCountPppNonActive();
                            updateChart();
                        }, 5000);
                        setInterval(function() {
                            updateCountPppSecret();
                            updateCountPppActive();
                        }, 10000);
                        $(document).ajaxStop(function() {
                            $("#loading_ppp_secret-{{ $onlineRouter->slug }}").addClass("d-none");
                            $("#loading_ppp_non_active-{{ $onlineRouter->slug }}").addClass("d-none");
                            $("#loading_ppp_active-{{ $onlineRouter->slug }}").addClass("d-none");
                        });
                    });
                </script>
            @endforeach
        @endif
    @endsection
</x-layouts.app-layout>
