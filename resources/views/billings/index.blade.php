<x-layouts.cpanel.billings.app-layout title="Billings">

    <div class="card-header py-3">

        <h5 class="mb-2 font-weight-bold text-primary">{{ __('billing.header.index') }}</h5>
        @if (isset($message))
            <div class="alert alert-danger mt-2">
                <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
                {{ $message }}
            </div>
        @else
            <div class="alert alert-success mt-2">
                <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
                {{ $data->total() }} billings found.
            </div>
        @endif
        @if (session('warning'))
            <div class="alert alert-warning mt-2">
                <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
                {{ session('warning') }}
            </div>
        @endif
        <span>
            <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseSearchCustomer"
                aria-expanded="false" aria-controls="collapseSearchCustomer" style="padding-top: inherit;">
                <i class="fas fa-search"></i>
                <span>{{ __('billing.header.search') }}</span>
            </a>
            <div class="collapse @if (request()->routeIs('billings.index')) show @endif" id="collapseSearchCustomer">
                <div class="card-header py-3 rounded">
                    <form>
                        <div class="row gx-3 gy-2">
                            <!-- Search Customer Name -->
                            <div class="col-md-4 col-lg-3">
                                {!! Form::input('search', 'search_name', null, [
                                    'placeholder' => __('billing.placeholder.search-customer-name'),
                                    'class' => 'form-control',
                                ]) !!}
                            </div>
                            @canany(['view-customers-widget-total'])
                                <div class="col-md-4 col-lg-3">
                                    {!! Form::select('admin_name', $userAdmin, null, [
                                        'placeholder' => __('billing.placeholder.search-admin'),
                                        'class' => 'form-control',
                                    ]) !!}
                                </div>
                            @endcanany
                        </div>

                        <!-- Additional Options -->
                        <div class="row gx-3 gy-2 mt-3">
                            <div class="col-lg-3 col-md-6">
                                <div class="form-check">
                                    <input class="form-check-input" value="true" type="checkbox" name="cetak_pdf"
                                        id="cetakPdfCheckbox">
                                    <label class="form-check-label" for="cetakPdfCheckbox">
                                        {{ __('Cetak PDF?') }}
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- Search Button -->
                        <div class="row mt-3">
                            <div class="col text-end">
                                <button class="btn btn-success btn-sm" type="submit">
                                    <i class="fas fa-search"></i> {{ __('customer.button.search') }}
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </span>
    </div>



    <div class="card-body">
        <div class="row align-items-center gy-2 mt-3 justify-content-between">
            {{-- Pagination kiri --}}
            <div class="col-md-4 d-flex justify-content-md-start justify-content-center">
                {{ $data->appends(['status' => request('status')])->links('vendor.pagination.simple-numeric') }}
            </div>

            {{-- Filter + Export --}}
            <div class="col-md-6 d-flex justify-content-md-end justify-content-center flex-wrap gap-2">
                {{-- Filter status --}}
                <form action="{{ route('billings.index') }}" method="GET" id="statusForm"
                    class="d-flex align-items-center" style="margin-right:10px; margin-bottom:10px;">
                    <label for="status" class="fw-bold text-primary" style="margin-right:8px; margin-bottom:0;">
                        Status
                    </label>
                    {!! Form::select('status', $statuses, request('status'), [
                        'placeholder' => __('All Status'),
                        'class' => 'form-control form-control-sm w-auto',
                        'onchange' => 'this.form.submit()',
                        'id' => 'status',
                        'style' => 'margin-right:10px;',
                    ]) !!}
                </form>

                @can('billing-export')
                    {{-- Export Selected --}}
                    <form action="{{ route('billings.export.selected') }}" method="GET"
                        style="margin-bottom:10px; margin-right:10px;">
                        <input type="hidden" name="status" value="{{ request('status') }}">
                        <input type="hidden" name="periode" value="{{ $periode }}">
                        <button type="submit" class="btn btn-sm btn-success">Export Data</button>
                    </form>
                @endcan
            </div>
        </div>

        @if ($totalPrice > 0)
            <div class="alert alert-success mt-2">
                <strong> Total Bill: @moneyIDR($totalPrice) </strong>
            </div>
        @endif
        @if ($data->isNotEmpty())
            <!-- Table Wrapper for Horizontal Scrolling -->
            <div style="overflow-x: auto;">
                <table id="billingsTable" cellspacing="0" width="100%"
                    class="table table-sm table-bordered table-striped table-hover">
                    <thead class="thead-dark">
                        <tr class="text-center align-middle">
                            <th class="text-center align-middle">{{ __('billing.table.no') }}</th>
                            <th class="text-center align-middle">{{ __('billing.table.customer-name') }}</th>
                            <th class="text-center align-middle">{{ __('billing.table.address') }}</th>
                            <th class="text-center align-middle">{{ __('billing.table.periode') }}</th>
                            <th class="text-center align-middle">{{ __('billing.table.bill') }}</th>
                            <th class="text-center align-middle">{{ __('billing.table.bill-state') }}</th>
                            <th class="text-center align-middle action-col">{{ __('billing.table.payment-methode') }}
                            </th>
                            @can('billing-edit')
                                <th class="text-center align-middle">{{ __('billing.table.action') }}</th>
                            @endcan
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($data as $key => $billing)
                            @php
                                $i++;
                                $periodeName = $billing->billingSubscription->billingPeriode->name;
                                $periodeText = \Carbon\Carbon::parse($periodeName)->format('M Y');
                                $billAmount = $billing->total_amount === 0 ? 0 : $billing->total_amount + $billing->tax;
                                $badgeClass = 'badge bg-blue-soft ';
                                if ($billing->status === 'LS') {
                                    $badgeClass .= 'text-success';
                                } elseif ($billing->status === 'VD') {
                                    $badgeClass .= 'text-muted';
                                } else {
                                    $badgeClass .= 'text-danger';
                                }
                            @endphp

                            {{-- ✅ Desktop View --}}
                            <tr class="d-none d-sm-table-row align-middle">
                                <th class="text-center"><span class="badge text-primary">{{ $i }}</span>
                                </th>
                                <td><a href="{{ route('billings.show', $billing->slug) }}" data-bs-toggle="tooltip"
                                        data-bs-placement="top" title="Show Billing">
                                        <span
                                            class="badge text-primary">{{ $billing->customer->name ?? 'no name' }}</span></a>
                                </td>
                                <td><span
                                        class="badge text-primary">{{ $billing->customer->address ?? 'no address' }}</span>
                                </td>
                                <td><span class="badge text-primary">{{ $periodeText ?? 'no periode' }}</span>
                                </td>
                                <td><span class="{{ $badgeClass }}">
                                        @if ($billAmount > 0)
                                            @moneyIDR($billAmount)
                                        @else
                                            {{ __('Free') }}
                                        @endif
                                    </span></td>
                                <td>{!! \App\Helpers\BillingHelper::getBillingStatusBadge($billing, $data) !!}</td>
                                <td>{!! \App\Helpers\BillingHelper::getBillingPaymentSection($billing, $bank) !!}</td>
                                @can('billing-edit')
                                    <td class="text-center action-col">
                                        <div class="d-inline-flex gap-2 align-items-center flex-nowrap">
                                            {!! \App\Helpers\BillingHelper::getBillingActionButton($billing) !!}
                                        </div>
                                    </td>
                                @endcan
                            </tr>

                            {{-- ✅ Mobile View: Parent Row --}}
                            <tr class="d-table-row d-sm-none" onclick="toggleExpand('{{ $billing->slug }}')">
                                <td colspan="8" style="cursor: pointer;">
                                    <div class="mobile-row-parent">
                                        <div>
                                            <strong>#{{ $i }}</strong> - <span
                                                class="text-primary">{{ $billing->customer_name }}</span><br>
                                            <small>{!! \App\Helpers\BillingHelper::getBillingStatusBadge($billing, $data) !!}</small>
                                        </div>
                                        <div>
                                            <span class="{{ $badgeClass }}">@moneyIDR($billAmount)</span><br>
                                            <small>{{ $periodeText }}</small>
                                        </div>
                                    </div>
                                </td>
                            </tr>

                            {{-- ✅ Mobile View: Expand Row --}}
                            <tr id="expand-{{ $billing->slug }}" class="d-none">
                                <td colspan="8">
                                    <div class="mobile-expand-row">
                                        <strong>{{ __('billing.table.address') }}:</strong><br>
                                        {{ $billing->customer->address ?? 'no address' }}<br><br>

                                        <div
                                            style="display: flex; gap: 10px; justify-content: space-between; flex-wrap: wrap; align-items: center;">
                                            <div style="flex: 1;">
                                                <strong>{{ __('billing.table.payment-methode') }}:</strong><br>
                                                {!! \App\Helpers\BillingHelper::getBillingPaymentSection($billing, $bank) !!}
                                            </div>
                                            @can('billing-edit')
                                                <div style="flex: 1;">
                                                    <strong
                                                        class="d-block mb-1">{{ __('customer.table.action') }}:</strong>
                                                    <div class="d-inline-flex gap-2 align-items-center flex-nowrap">
                                                        {!! \App\Helpers\BillingHelper::getBillingActionButton($billing) !!}
                                                    </div>
                                                </div>
                                            @endcan
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="alert alert-danger mt-2">
                No billing found!
            </div>
        @endif
        <div class="d-flex flex-wrap justify-content-between align-items-center mt-3 gap-2">
            {{-- Pagination kiri --}}
            <div>
                {{ $data->appends(['status' => request('status')])->links('vendor.pagination.simple-numeric') }}
            </div>

            {{-- Dropdown perPage kanan --}}
            <form method="GET" id="paginationForm" class="d-flex align-items-center mb-3">
                <label for="perPage" class="fw-bold text-primary me-2 mb-0 mr-1">Show</label>
                {!! Form::select('perPage', [25 => 25, 50 => 50, 100 => 100], request('perPage'), [
                    'class' => 'form-control form-control-sm w-auto',
                    'onchange' => 'this.form.submit()',
                    'id' => 'perPage',
                ]) !!}
            </form>
        </div>
    </div>
    @section('custom_styles')
        <link href="{{ asset('css/flatpickr.min.css') }}" rel="stylesheet">
        <style>
            .table th.action-col,
            .table td.action-col {
                min-width: 180px;
                white-space: nowrap;
            }

            @media (max-width: 576px) {
                table.table thead {
                    display: none;
                }

                .mobile-row-parent {
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                    padding: 10px;
                    border-bottom: 1px solid #dee2e6;
                    background-color: #f8f9fa;
                }

                .mobile-row-parent:hover {
                    background-color: #e9ecef;
                }

                .mobile-expand-row {
                    padding: 10px;
                    background-color: #ffffff;
                    border-bottom: 1px solid #dee2e6;
                }
            }
        </style>
    @endsection
</x-layouts.cpanel.billings.app-layout>
