<x-layouts.app-layout title="KPI Report">

    @php
        $currentYear = now()->year;
        $years = [$currentYear - 2, $currentYear - 1, $currentYear];
    @endphp

    <div class="card-header py-3 d-flex justify-content-between align-items-center">
        <h5 class="m-0 font-weight-bold text-primary">
            KPI Report {{ $year }}
        </h5>

        <form method="GET" action="{{ route('reports.kpi') }}" class="d-flex align-items-center">
            <label for="year" class="me-2 mb-0 fw-bold mr-2">Year</label>
            <select name="year" id="year" class="form-control" onchange="this.form.submit()">
                @foreach ($years as $y)
                    <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>
                        {{ $y }}
                    </option>
                @endforeach
            </select>
        </form>
    </div>

    <div class="card-body">

        <div class="card mb-4">
            <div class="card-header">
                <strong>Active Customers Trend – {{ $year }}</strong>
            </div>
            <div class="card-body">
                <canvas id="activeCustomerChart" height="100"></canvas>
            </div>
        </div>

        <div class="accordion" id="kpiAccordion">

            @foreach ($mikrotiks as $idx => $m)
                <div class="card mb-2">
                    <div class="card-header" id="heading{{ $idx }}">
                        <h6 class="mb-0">
                            <button class="btn btn-link text-primary" data-toggle="collapse"
                                data-target="#collapse{{ $idx }}">
                                {{ $m['mikrotik_name'] }}
                            </button>
                        </h6>
                    </div>

                    <div id="collapse{{ $idx }}" class="collapse" data-parent="#kpiAccordion">

                        <div class="card-body p-0" style="overflow-x:auto">
                            <table class="table table-bordered table-striped table-hover">
                                <thead class="table-secondary text-center">

                                    {{-- HEADER LEVEL 1 --}}
                                    <tr>
                                        <th rowspan="3" class="align-middle">Month</th>

                                        <th colspan="6">Billing</th>
                                        <th colspan="4">Subscriptions</th>
                                        <th rowspan="3" class="align-middle">Expenses</th>
                                        <th rowspan="3" class="align-middle">Net Income</th>
                                        <th rowspan="3" class="align-middle">Total Customers</th>
                                        <th rowspan="3" class="align-middle">Total Billings</th>
                                    </tr>

                                    {{-- HEADER LEVEL 2 --}}
                                    <tr>
                                        <th colspan="2">Payment</th>
                                        <th colspan="2">Unpayment</th>
                                        <th colspan="2">Paylater</th>

                                        <th colspan="2">Active</th>
                                        <th colspan="2">Unsub</th>
                                    </tr>

                                    {{-- HEADER LEVEL 3 --}}
                                    <tr>
                                        <th>Cust</th>
                                        <th>Rp</th>

                                        <th>Cust</th>
                                        <th>Rp</th>

                                        <th>Cust</th>
                                        <th>Rp</th>

                                        <th>Cust</th>
                                        <th>Rp</th>

                                        <th>Cust</th>
                                        <th>Rp</th>
                                    </tr>

                                </thead>

                                <tbody>
                                    @foreach ($m['months'] as $row)
                                        <tr>
                                            <td>{{ $row['label'] }}</td>

                                            <td class="text-center">{{ $row['payment']['cust'] }}</td>
                                            <td class="text-end">@moneyIDR($row['payment']['amount'])</td>

                                            <td class="text-center">{{ $row['unpayment']['cust'] }}</td>
                                            <td class="text-end">@moneyIDR($row['unpayment']['amount'])</td>

                                            <td class="text-center">{{ $row['paylater']['cust'] }}</td>
                                            <td class="text-end">@moneyIDR($row['paylater']['amount'])</td>

                                            <td class="text-center">{{ $row['subscription']['cust'] }}</td>
                                            <td class="text-end">@moneyIDR($row['subscription']['nominal'])</td>

                                            <td class="text-center">{{ $row['unsubscription']['cust'] }}</td>
                                            <td class="text-end">@moneyIDR($row['unsubscription']['nominal'])</td>

                                            <td class="text-end">@moneyIDR($row['expenses'])</td>

                                            <td
                                                class="text-end {{ $row['net_income'] < 0 ? 'text-danger' : 'text-success' }}">
                                                @moneyIDR($row['net_income'])
                                            </td>

                                            <td class="text-center">{{ $row['total_customers'] }}</td>
                                            <td class="text-end">@moneyIDR($row['total_billings'])</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                    </div>
                </div>
            @endforeach

        </div>

    </div>

    @php
        $labels = collect($mikrotiks[0]['months'])->pluck('label');

        $datasets = collect($mikrotiks)->map(function ($m) {
            return [
                'label' => $m['mikrotik_name'],
                'data' => collect($m['months'])->pluck('subscription.cust'),
            ];
        });
    @endphp

    @section('custom_scripts')
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

        <script>
            const ctx = document.getElementById('activeCustomerChart');

            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: @json($labels),
                    datasets: @json($datasets).map(ds => ({
                        ...ds,
                        tension: 0.3,
                        fill: false,
                        borderWidth: 2
                    }))
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        },
                        tooltip: {
                            callbacks: {
                                label: ctx => `${ctx.dataset.label}: ${ctx.parsed.y} customers`
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: 'Active Customers'
                            }
                        }
                    }
                }
            });
        </script>
    @endsection

</x-layouts.app-layout>
