<x-layouts.app-layout title="Monthly Internet Report">

    <div class="card-header py-3">
        <h5 class="m-0 fw-bold text-primary mb-2">
            Monthly Internet Report
        </h5>

        <div class="alert alert-success mb-0">
            Periode:
            <strong>{{ $periode->name ?? 'Periode #' . $periode->id }}</strong>
            — Responsibility:
            <strong>{{ $responsibility->name }}</strong>
        </div>
    </div>

    <div class="card-body">

        {{-- (Opsional) Filter sederhana (kalau mau) --}}
        <div class="mb-3">
            <form method="GET" class="row g-2">
                <div class="col-md-3">
                    <input type="number" name="billing_periode_id" class="form-control"
                        value="{{ request('billing_periode_id') }}" placeholder="billing_periode_id">
                </div>
                <div class="col-md-3">
                    <input type="number" name="user_id" class="form-control" value="{{ request('user_id') }}"
                        placeholder="user_id">
                </div>
                <div class="col-md-3">
                    <input type="number" name="mikrotik_id" class="form-control" value="{{ request('mikrotik_id') }}"
                        placeholder="mikrotik_id (optional)">
                </div>
                <div class="col-md-3">
                    <button class="btn btn-success btn-sm" type="submit">
                        <i class="fas fa-search"></i> Apply
                    </button>
                </div>
            </form>
        </div>

        @foreach ($mikrotiks as $m)
            <div class="mb-4">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h6 class="fw-bold text-primary mb-0">
                        Mikrotik: {{ $m['mikrotik_name'] }}
                    </h6>
                </div>

                <div style="overflow-x:auto;">
                    <table cellspacing="0" width="100%" class="table table-bordered table-striped table-hover">

                        <thead>
                            <tr class="table-secondary text-center">
                                <th rowspan="2" class="align-middle"><span
                                        class="badge bg-danger-soft text-primary">No</span></th>
                                <th rowspan="2" class="align-middle"><span
                                        class="badge bg-danger-soft text-primary">PAKET INTERNET</span>
                                </th>
                                <th rowspan="2" class="align-middle"><span
                                        class="badge bg-danger-soft text-primary">HARGA PAKET</span>
                                </th>
                                <th rowspan="2" class="align-middle"><span
                                        class="badge bg-danger-soft text-primary">Responsibility</span>
                                </th>

                                <th rowspan="2" class="align-middle"><span
                                        class="badge bg-danger-soft text-primary">Total Cust</span>
                                </th>

                                <th colspan="8"><span class="badge bg-danger-soft text-primary">Billing</span></th>

                                <th colspan="2"><span class="badge bg-danger-soft text-primary">Subscriptions</span>
                                </th>
                                <th colspan="2"><span
                                        class="badge bg-danger-soft text-primary">Unsubscriptions</span></th>
                            </tr>

                            <tr class="table-secondary text-center">
                                <th><span class="badge bg-danger-soft text-primary">Bill</span></th>
                                <th><span class="badge bg-danger-soft text-primary">Cust</span></th>

                                <th><span class="badge bg-danger-soft text-primary">Payment</span></th>
                                <th><span class="badge bg-danger-soft text-primary">Cust</span></th>

                                <th><span class="badge bg-danger-soft text-primary">Unpayment</span></th>
                                <th><span class="badge bg-danger-soft text-primary">Cust</span></th>

                                <th><span class="badge bg-danger-soft text-primary">Paylater</span></th>
                                <th><span class="badge bg-danger-soft text-primary">Cust</span></th>

                                <th><span class="badge bg-danger-soft text-primary">Cust</span></th>
                                <th><span class="badge bg-danger-soft text-primary">Nominal</span></th>

                                <th><span class="badge bg-danger-soft text-primary">Cust</span></th>
                                <th><span class="badge bg-danger-soft text-primary">Nominal</span></th>
                            </tr>
                        </thead>

                        <tbody>
                            @foreach ($m['rows'] as $i => $r)
                                <tr class="text-primary">
                                    <td class="text-center">
                                        <span class="badge bg-danger-soft">{{ $i + 1 }}</span>
                                    </td>

                                    <td>
                                        <span class="badge bg-danger-soft">
                                            {{ $r['package_name'] }}
                                        </span>
                                    </td>

                                    <td class="text-end">
                                        <span class="badge bg-danger-soft">
                                            Rp {{ number_format($r['price'], 0, ',', '.') }}
                                        </span>
                                    </td>

                                    <td class="text-center">
                                        <span class="badge bg-danger-soft">
                                            {{ $responsibility->name }}
                                        </span>
                                    </td>

                                    <td class="text-center">
                                        <span class="badge bg-danger-soft">
                                            {{ $r['total_cust'] }}
                                        </span>
                                    </td>

                                    {{-- Billing --}}
                                    <td class="text-end">
                                        <span class="badge bg-danger-soft">
                                            Rp {{ number_format($r['billing']['amount'], 0, ',', '.') }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-danger-soft">
                                            {{ $r['billing']['cust'] }}
                                        </span>
                                    </td>

                                    <td class="text-end">
                                        <span class="badge bg-danger-soft">
                                            Rp {{ number_format($r['payment']['amount'], 0, ',', '.') }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-danger-soft">
                                            {{ $r['payment']['cust'] }}
                                        </span>
                                    </td>

                                    <td class="text-end">
                                        <span class="badge bg-danger-soft">
                                            Rp {{ number_format($r['unpayment']['amount'], 0, ',', '.') }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-danger-soft">
                                            {{ $r['unpayment']['cust'] }}
                                        </span>
                                    </td>

                                    <td class="text-end">
                                        <span class="badge bg-danger-soft">
                                            Rp {{ number_format($r['paylater']['amount'], 0, ',', '.') }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-danger-soft">
                                            {{ $r['paylater']['cust'] }}
                                        </span>
                                    </td>

                                    {{-- Subs --}}
                                    <td class="text-center">
                                        <span class="badge bg-danger-soft">
                                            {{ $r['subscription']['cust'] }}
                                        </span>
                                    </td>
                                    <td class="text-end">
                                        <span class="badge bg-danger-soft">
                                            Rp {{ number_format($r['subscription']['nominal'], 0, ',', '.') }}
                                        </span>
                                    </td>

                                    {{-- Unsubs --}}
                                    <td class="text-center">
                                        <span class="badge bg-danger-soft">
                                            {{ $r['unsubscription']['cust'] }}
                                        </span>
                                    </td>
                                    <td class="text-end">
                                        <span class="badge bg-danger-soft">
                                            Rp {{ number_format($r['unsubscription']['nominal'], 0, ',', '.') }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>

                        {{-- FOOTER TOTAL seperti screenshot --}}
                        <tfoot>
                            <tr class="table-secondary">
                                <th colspan="4" class="text-start">
                                    TOTAL PELANGGAN
                                </th>

                                <th class="text-center">
                                    {{ $m['totals']['total_cust'] }}
                                </th>

                                <th class="text-end">
                                    Rp {{ number_format($m['totals']['billing_amount'], 0, ',', '.') }}
                                </th>
                                <th class="text-center">
                                    {{ $m['totals']['billing_cust'] }}
                                </th>

                                <th class="text-end">
                                    Rp {{ number_format($m['totals']['payment_amount'], 0, ',', '.') }}
                                </th>
                                <th class="text-center">
                                    {{ $m['totals']['payment_cust'] }}
                                </th>

                                <th class="text-end">
                                    Rp {{ number_format($m['totals']['unpayment_amount'], 0, ',', '.') }}
                                </th>
                                <th class="text-center">
                                    {{ $m['totals']['unpayment_cust'] }}
                                </th>

                                <th class="text-end">
                                    Rp {{ number_format($m['totals']['paylater_amount'], 0, ',', '.') }}
                                </th>
                                <th class="text-center">
                                    {{ $m['totals']['paylater_cust'] }}
                                </th>

                                <th class="text-center">
                                    {{ $m['totals']['subscription_cust'] }}
                                </th>
                                <th class="text-end">
                                    Rp {{ number_format($m['totals']['subscription_nominal'], 0, ',', '.') }}
                                </th>

                                <th class="text-center">
                                    {{ $m['totals']['unsubscription_cust'] }}
                                </th>
                                <th class="text-end">
                                    Rp {{ number_format($m['totals']['unsubscription_nominal'], 0, ',', '.') }}
                                </th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            <div class="row mt-3">
                <div class="col-md-6 offset-md-6">
                    <table class="table table-bordered table-sm">
                        <thead class="table-secondary text-center">
                            <tr>
                                <th colspan="3">Monthly Expenses — {{ $m['mikrotik_name'] }}</th>
                            </tr>
                            <tr>
                                <th>No</th>
                                <th>Description</th>
                                <th>Nominal</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($m['expenses'] as $i => $e)
                                <tr>
                                    <td class="text-center">{{ $i + 1 }}</td>
                                    <td>{{ $e['label'] }}</td>
                                    <td class="text-end">
                                        Rp {{ number_format($e['amount'], 0, ',', '.') }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="table-secondary">
                            <tr>
                                <th colspan="2" class="text-end">Total Expense</th>
                                <th class="text-end">
                                    Rp {{ number_format($m['expense_total'], 0, ',', '.') }}
                                </th>
                            </tr>
                            <tr>
                                <th colspan="2" class="text-end">Net Income This Month</th>
                                <th class="text-end {{ $m['net_income'] < 0 ? 'text-danger' : 'text-success' }}">
                                    Rp {{ number_format($m['net_income'], 0, ',', '.') }}
                                </th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        @endforeach

        {{-- (Opsional) TOTAL GLOBAL semua mikrotik --}}
        @if (!empty($grand_totals))
            <div class="alert alert-info">
                <strong>TOTAL GLOBAL</strong> —
                Subs Cust: <strong>{{ $grand_totals['subscription_cust'] }}</strong>,
                Subs Nominal: <strong>Rp
                    {{ number_format($grand_totals['subscription_nominal'], 0, ',', '.') }}</strong>
            </div>
        @endif

    </div>

</x-layouts.app-layout>
