<x-layouts.cpanel.customers.app-layout title="Detail Customer">

    <div class="card-header py-3">
        <h5 class="m-0 font-weight-bold text-primary">{{ __('customer.header.show') }}</h5>
    </div>

    <div class="card-body col-lg-12">
        <div style="overflow-x: auto;">
            <table id="serverTable" cellspacing="0" width="100%" class="table no-footer">
                <tr class="table-borderless">
                    <td class="py-1" width="150px"><strong>{{ __('customer.label.id') }}</strong></td>
                    <td class="py-1" width="380px">{{ __(':') }} {{ $customer->customer_id ?? 'No ID' }}</td>

                </tr>
                <tr class="table-borderless">
                    <td class="py-1" width="150px"><strong>{{ __('customer.label.name') }}</strong></td>
                    <td class="py-1" width="380px">{{ __(':') }} {{ $customer->name }}</td>
                    <td class="py-1" width="150px"><strong>{{ __('customer.label.email') }}</strong></td>
                    <td class="py-1" width="380px">{{ __(':') }} {{ $customer->email }}</td>
                </tr>
                <tr class="table-borderless">
                    <td class="py-1"><strong>{{ __('customer.label.address') }}</strong></td>
                    <td class="py-1">{{ __(':') }} {{ $customer->address }}</td>
                    <td class="py-1"><strong>{{ __('customer.label.contact') }}</strong></td>
                    <td class="py-1">{{ __(':') }} {{ $customer_contact }}</td>
                </tr>
                <tr class="table-borderless">
                    <td class="py-1"><strong>{{ __('customer.label.paket') }}</strong></td>
                    <td class="py-1">{{ __(':') }} {{ $customer->paket->name ?? 'No Data' }}</td>

                    <td class="py-1"><strong>{{ __('customer.label.activation-date') }}</strong></td>
                    <td class="py-1">{{ __(':') }}
                        @if ($customer->activation == 'true')
                            {{ Carbon\Carbon::parse($customer->activation_date)->translatedFormat('l, d F Y') ?? 'No Data' }}
                        @else
                            <i class="text-danger">{{ __('Not Acitaved') }}</i>
                        @endif
                    </td>
                </tr>
                <tr class="table-borderless">
                    <td class="py-1"><strong>{{ __('customer.label.bill') }}</strong></td>
                    <td class="py-1">{{ __(':') }}
                        <strong>
                            @if ($customer->paket->price * ((100 - $customer->discount) / 100) > 0)
                                @moneyIDR($customer->paket->price * ((100 - $customer->discount) / 100))
                            @else
                                {{ __('Free') }}
                            @endif
                        </strong>
                    </td>
                    <td class="py-1"><strong>{{ __('customer.label.discount') }}</strong></td>
                    <td class="py-1">{{ __(':') }}
                        @if ($customer->discount > 0)
                            {{ $customer->discount ?? 'No Data' }} %
                        @else
                            {{ __('No Discount') }}
                        @endif
                    </td>
                </tr>
                @if ($billingLastPayment)
                    @php
                        $activationDate = Carbon\Carbon::parse($customer->activation_date)->format('d');
                        $totalDaysInMonth = Carbon\Carbon::parse(
                            $billingLastPayment->payment_time ?? '1970-01-01',
                        )->addMonth()->daysInMonth;
                        if ($activationDate > $totalDaysInMonth) {
                            $activationDate = $totalDaysInMonth;
                        }
                    @endphp

                    <tr class="table-borderless">
                        <td class="py-1"><strong>{{ __('customer.label.last-payment') }}</strong></td>
                        <td class="py-1">{{ __(':') }}
                            {{ Carbon\Carbon::parse($billingLastPayment->payment_time ?? '1970-01-01')->format('d F Y') ?? 'No Payment' }}
                        </td>
                        <td class="py-1"><strong>{{ __('customer.label.next-payment') }}</strong></td>
                        <td class="py-1">{{ __(':') }}
                            {{ $activationDate .' ' .Carbon\Carbon::parse($billingLastPayment->payment_time ?? '1970-01-01')->addMonth()->format('F Y') }}
                        </td>
                    </tr>
                @endif
                @if ($notYetPaid > 0)
                    <tr class="table-borderless">
                        <td class="py-1"><strong>{{ __('customer.label.not-yet-paid') }}</strong></td>
                        <td class="py-1">{{ __(':') }}
                            @moneyIDR($notYetPaid)
                        </td>
                    </tr>
                @endif
            </table>
        </div>
        <div class="row align-items-center">
            <div class="col-md-12 mb-3">
                @can('customer-edit')
                    <a class="btn btn-primary btn-sm " href="{{ route('customers.edit', $customer->slug) }}"><i
                            class="fa fa-edit"></i> &nbsp {{ __('customer.button.edit') }}</a>
                @endcan
                <a class="btn btn-secondary btn-sm " href="{{ route('customers.index') }}"><i
                        class="fa fa-arrow-left"></i> &nbsp {{ __('customer.button.back') }}</a>

            </div>
        </div>
    </div>

    @if ($serverDisconnect == 'false')
        @if ($customer->internet_service == 'ppp')
            <div class="card-header py-3">
                <h5 class="m-0 font-weight-bold text-primary">{{ __('customer.header.show-server') }}</h5>
                <span class="text-success">
                    {{ $info_msg }}
                </span>
                @if ($pppsecret['name'] != $customer->username || $pppsecret['password'] != $customer->password_ppp)
                    <div class="alert alert-danger">
                        {{ __('Warning username or password on server different with database!') }}<br>
                        On Database :<br>
                        Username: {{ $customer->username }}<br>
                        Password: {{ $customer->password_ppp }}
                    </div>
                @endif
            </div>
            <div class="card-body col-lg-12">
                <div style="overflow-x: auto;">
                    <table id="serverTable" cellspacing="0" width="100%" class="table no-footer">
                        <tr class="table-borderless">
                            <td class="py-1" width="150px">
                                <strong>{{ __('customer.label.server') }}</strong>
                            </td>
                            <td class="py-1" width="380px">{{ __(':') }}
                                <span class="badge text-white bg-success">
                                    <i class="fas fa-server"></i> {{ $customer->mikrotik->name }}
                                </span>
                            </td>
                            <td class="py-1" width="150px">
                                <strong>{{ __('customer.label.profile') }}</strong>
                            </td>
                            <td class="py-1" width="380px">
                                {{ __(':') }}
                                <span class="text-primary">{{ $pppsecret['profile'] ?? 'No Profile' }}</span>
                            </td>
                        </tr>

                        <tr class="table-borderless">
                            <td class="py-1"><strong>{{ __('customer.label.username') }}</strong></td>
                            <td class="py-1">
                                {{ __(':') }} <span
                                    class="@if ($pppsecret['name'] != $customer->username) alert alert-danger @else text-primary @endif">
                                    {{ $pppsecret['name'] ?? 'No Data' }}</span>
                            </td>
                            <td class="py-1"><strong>{{ __('customer.label.password-ppp') }}</strong></td>
                            <td class="py-1">
                                {{ __(':') }} <span
                                    class="@if ($pppsecret['password'] != $customer->password_ppp) alert alert-danger @else text-primary @endif">
                                    {{ $pppsecret['password'] ?? 'No Data' }}</span>
                            </td>
                        </tr>

                        <tr class="table-borderless">
                            <td class="py-1"><strong>{{ __('customer.label.remote-address') }}</strong></td>
                            <td class="py-1">{{ __(':') }}
                                <span class="text-primary"> {{ $secretActive['address'] ?? 'Offline' }}</span>
                                @if (isset($secretActive['address']))
                                    <span>
                                        <a class="mt-1 text-danger"
                                            href="http://{{ $secretActive['address'] }}:{{ $ont->port ?? '80' }}"
                                            target="_blank">
                                            <span
                                                class="badge text-white bg-primary">{{ __('customer.remote') }}</span>
                                        </a>
                                    </span>
                                @endif
                            </td>
                            <td class="py-1"><strong>{{ __('customer.label.service') }}</strong></td>
                            <td class="py-1">
                                {{ __(':') }} <span class="text-primary">
                                    {{ $customer->ppp_type->name }}</span>
                            </td>
                        </tr>

                        <tr class="table-borderless">
                            <td class="py-1"><strong>{{ __('customer.label.state') }}</strong></td>
                            <td class="py-1">{{ __(':') }}

                                @if ($customer->activation == 'false')
                                    <span class="badge text-white bg-danger">
                                        <i class="fa fa-times text-warning me-2"></i>
                                        {{ __('customer.info.not-activation') }}
                                    </span>
                                @else
                                    @if ($pppsecret['disabled'] === \App\Enums\MikrotikStatus::PPPOE_ENABLE->value)
                                        <span class="badge text-white bg-success">
                                            <i class="fas fa-lock-open"></i> {{ __('pppoe.secret.enabled') }}
                                        </span>
                                    @else
                                        <span class="badge text-white bg-danger">
                                            <i class="fas fa-lock"></i> {{ __('pppoe.secret.disabled') }}
                                        </span>
                                    @endif
                                @endif
                            </td>
                            @if (isset($secretActive))
                                <td class="py-1"><strong>{{ __('customer.label.uptime') }}</strong></td>
                                <td class="py-1">
                                    {{ __(':') }} <span
                                        class="text-primary">{{ $secretActive['uptime'] ?? 'No Data' }}</span>
                                </td>
                            @endif
                        </tr>

                        <tr class="table-borderless">
                            <td class="py-1"><strong>{{ __('customer.label.caller-id') }}</strong></td>
                            <td class="py-1">{{ __(':') }}
                                <span class="text-primary">
                                    {{ $customer->caller_id ?? 'No Caller ID' }}</span>
                            </td>
                            <td class="py-1"><strong>{{ __('customer.label.last-caller-id') }}</strong></td>
                            <td class="py-1">{{ __(':') }}
                                <span class="text-primary">
                                    {{ $pppsecret['last-caller-id'] ?? 'No Caller ID' }}</span>
                            </td>
                        </tr>
                        <tr class="table-borderless">

                            <td class="py-1"><strong>{{ __('customer.label.comment') }}</strong></td>
                            <td class="py-1">{{ __(':') }}
                                <span class="text-primary"> {{ $pppsecret['comment'] ?? 'No Comment' }}</span>
                            </td>
                        </tr>
                    </table>
                </div>
                <div class="row align-items-center">
                    <div class="col-md-12 mb-3">
                        @can('secret-edit')
                            @if (isset($pppsecret['last-caller-id']))
                                <a class="btn btn-sm btn-primary"
                                    href="{{ route('customers.edit.ppp', [$customer->slug, 'caller_id' => $pppsecret['last-caller-id']]) }}"><i
                                        class="fa fa-edit"></i>&nbsp{{ __('customer.button.edit-ppp') }}</a>
                            @else
                                <a class="btn btn-sm btn-primary"
                                    href="{{ route('customers.edit.ppp', [$customer->slug]) }}"><i
                                        class="fa fa-edit"></i>&nbsp{{ __('customer.button.edit-ppp') }}</a>
                            @endif
                        @endcan

                        @canany('customer-edit', 'customer-create')
                            @if ($customer->activation == 'false')
                                <form method="POST" action="{{ route('customers.activation', $customer->slug) }}"
                                    class="d-inline">
                                    @csrf
                                    @method('put')
                                    <button type="submit" class="btn btn-sm btn-success" type="submit">
                                        <i class="fa fa-check me-2 text-white"></i>
                                        {{ __('customer.action.activation') }}
                                    </button>
                                </form>
                            @endif
                        @endcanany
                        @can('secret-disable')
                            @if ($customer->activation == 'true')
                                @if ($pppsecret['disabled'] === \App\Enums\MikrotikStatus::PPPOE_DISABLE->value)
                                    <form method="POST" class="d-inline"
                                        action="{{ route('customers.enable', $customer->slug) }}">
                                        @csrf
                                        @method('put')
                                        <button type="submit" class="btn btn-sm btn-success">
                                            <i class="fa fa-check me-2"></i>{{ __('pppoe.secret.enable') }}
                                        </button>

                                    </form>
                                @else
                                    <form method="POST" class="d-inline"
                                        action="{{ route('customers.disable', $customer->slug) }}">
                                        @csrf
                                        @method('put')
                                        <button type="submit" class="btn btn-sm btn-danger">
                                            <i class="fa fa-times me-2"></i> {{ __('pppoe.secret.disable') }}
                                        </button>
                                    </form>
                                @endif
                            @endif
                        @endcan
                    </div>
                </div>
            </div>
        @elseif ($customer->internet_service == 'ip_static')
            <div class="card-header py-3">
                <h5 class="m-0 font-weight-bold text-primary">{{ __('customer.header.show-server') }}</h5>
                Ip Address: {{ $customer->ip_static }}<br>
                MAC Address: {{ $customer->mac_address }}<br>
                Interface: {{ $customer->interface }}<br>
                Limitasi Name: {{ $customer->username }}<br>
            </div>
        @endif
    @else
        <div class="card-header py-3">
            <h5 class="m-0 font-weight-bold text-primary">{{ __('customer.header.show-server') }}</h5>
            <span class="text-danger">
                {{ $info_msg }}
            </span>
        </div>

        <div class="card-body col-lg-8">
            <div style="overflow-x: auto;">
                <table id="serverTable" cellspacing="0" width="100%" class="table no-footer">
                    <tr class="table-borderless">
                        <td class="py-1" width="150px"><strong>{{ __('customer.label.server') }}</strong>
                        </td>
                        <td class="py-1" width="380px">{{ __(':') }} {{ $customer->mikrotik->name }}
                        </td>
                        <td class="py-1" width="150px"><strong>{{ __('customer.label.profile') }}</strong>
                        </td>
                        <td class="py-1" width="380px">{{ __(':') }} {{ $customer->paket->profile }}
                        </td>
                    </tr>
                    <tr class="table-borderless">
                        <td class="py-1"><strong>{{ __('customer.label.service') }}</strong></td>
                        <td class="py-1">
                            {{ __(':') }} <span class="text-primary">
                                {{ $customer->ppp_type->name ?? 'No Service' }}</span>
                        </td>
                        <td class="py-1"><strong>{{ __('customer.label.state') }}</strong></td>
                        <td class="py-1">
                            {{ __(':') }} <span class="text-primary">
                                @if ($customer->activation == 'false')
                                    <span class="badge text-white bg-danger">
                                        <i class="fa fa-times text-warning me-2"></i>
                                        {{ __('customer.info.not-activation') }}
                                    </span>
                                @else
                                    @if ($customer->disabled === \App\Enums\MikrotikStatus::PPPOE_ENABLE->value)
                                        <span class="badge text-white bg-success">
                                            <i class="fas fa-lock-open"></i> {{ __('pppoe.secret.enabled') }}
                                        </span>
                                    @elseif ($customer->disabled === \App\Enums\MikrotikStatus::PPPOE_DISABLE->value)
                                        <span class="badge text-white bg-danger">
                                            <i class="fas fa-lock"></i> {{ __('pppoe.secret.disabled') }}
                                        </span>
                                    @endif
                                @endif
                            </span>
                        </td>
                    </tr>
                    <tr class="table-borderless">
                        <td class="py-1"><strong>{{ __('customer.label.username') }}</strong></td>
                        <td class="py-1">
                            {{ __(':') }} <span class="text-primary">
                                {{ $customer->username ?? 'No Data' }}</span>
                        </td>
                        <td class="py-1"><strong>{{ __('customer.label.password-ppp') }}</strong></td>
                        <td class="py-1">
                            {{ __(':') }} <span class="text-primary">
                                {{ $customer->password_ppp ?? 'No Data' }}</span>
                        </td>
                    </tr>
                </table>
            </div>
            <div class="col-xs-12 col-sm-12 col-md-12">
                @canany('customer-edit', 'customer-create')
                    @if ($customer->activation == 'false')
                        <form method="POST" action="{{ route('customers.activation', $customer->slug) }}"
                            class="d-inline">
                            @csrf
                            @method('put')
                            <button type="submit" class="btn btn-sm btn-success" type="submit">
                                <i class="fa fa-check me-2 text-white"></i>
                                {{ __('customer.action.activation') }}
                            </button>
                        </form>
                    @endif
                @endcanany
            </div>
        </div>
    @endif

    <div class="card-header py-3">
        <h5 class="m-0 font-weight-bold text-primary">{{ __('customer.header.show-ont') }}</h5>
    </div>

    <div class="card-body col-lg-12">
        <div style="overflow-x: auto;">
            <table id="serverTable" cellspacing="0" width="100%" class="table no-footer">
                <tr class="table-borderless">
                    <td class="py-1" width="150px"><strong>{{ __('customer.label.router') }}</strong></td>
                    <td class="py-1" width="380px">{{ __(':') }}
                        <span class="badge text-white bg-success">
                            <i class="fas fa-server"></i> {{ $ont->merk_ont ?? 'Not Set' }}
                            {{ $ont->type_ont ?? '' }}
                        </span>
                    </td>
                </tr>
                <tr class="table-borderless">
                    <td class="py-1"><strong>{{ __('customer.label.username-ont') }}</strong></td>
                    <td class="py-1">
                        {{ __(':') }} <span class="text-primary">{{ $ont->username ?? 'Not Set' }}</span>
                    </td>
                    <td class="py-1" width="150px"><strong>{{ __('customer.label.password-ont') }}</strong>
                    </td>
                    <td class="py-1" width="380px">
                        {{ __(':') }} <span class="text-primary">
                            {{ $ont->password ?? 'Not Set' }}</span>
                    </td>
                </tr>
                <tr class="table-borderless">
                    <td class="py-1"><strong>{{ __('customer.label.remote-port') }}</strong></td>
                    <td class="py-1">
                        {{ __(':') }} <span class="text-primary"> {{ $ont->port ?? 'Not Set' }}</span>
                    </td>
                    <td class="py-1"><strong>{{ __('customer.label.mac-address') }}</strong></td>
                    <td class="py-1">
                        {{ __(':') }} <span class="text-primary">
                            {{ $ont->mac_address ?? 'Not Set' }}</span>
                    </td>
                </tr>
                <tr class="table-borderless">
                    <td class="py-1"><strong>{{ __('customer.label.description') }}</strong></td>
                    <td class="py-1">
                        {{ __(':') }} <span class="text-primary">
                            {{ $ont->description ?? 'No Description' }}</span>
                    </td>
                </tr>
            </table>
        </div>

        <div class="row align-items-center">
            <div class="col-md-12 mb-3">
                @can('customer-edit')
                    <a class="btn btn-sm btn-primary" href="{{ route('customers.edit.router', $customer->slug) }}"><i
                            class="fas fa-user-edit"></i>&nbsp{{ __('customer.button.edit-router') }}</a>
                @endcan

                <a class="btn btn-sm btn-secondary" href="{{ URL::previous() }}"><i
                        class="fa fa-arrow-left"></i>&nbsp{{ __('customer.button.back') }}</a>

            </div>
        </div>


    </div>
    @if ($secretActive)
        <div class="card-header py-3">
            <h5 class="m-0 font-weight-bold text-primary">{{ __('customer.header.traffic-monitoring') }}</h5>
        </div>

        <div class="card-body col-lg-12">
            <div class="col-lg-10 mb-4">
                <div class="card mb-4">
                    <div class="card-header">
                        Interface : pppoe-{{ $customer->username }}
                    </div>

                    <div class="card-body">
                        <div class="apex-charts" id="chart" dir="ltr"></div>
                    </div>

                </div>
            </div>
        </div>

        @if ($customer->username)
            @section('custom_scripts')
                <script>
                    $(document).ready(function() {

                        const options = {
                            chart: {
                                height: 300,
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
                            document.querySelector("#chart"),
                            options
                        );
                        chart.render();
                        const updateChart = function() {
                            const iface = "<pppoe-{{ $customer->username }}>";
                            const url = "{{ route('mikrotik.traffic_interface', $mikrotik) }}";
                            $.ajax({
                                url: url,
                                data: {
                                    'interface': iface
                                },
                                success: function(response) {
                                    chart.appendData([{
                                            name: 'Download',
                                            data: response[0].Tx
                                        },
                                        {
                                            name: 'Upload',
                                            data: response[0].Rx
                                        },
                                    ])
                                },
                                global: false,
                                dataType: 'json',
                            });
                        };

                        setInterval(function() {
                            updateChart();
                        }, 2000);
                    });
                </script>
                <!--<script src="{{ asset('assets/vendor/apexcharts/apexcharts.min.js') }}"></script>-->
                <script src="{{ @asset('vendor/larapex-charts/apexcharts.js') }}"></script>
            @endsection
        @endif
    @endif
</x-layouts.cpanel.customers.app-layout>
