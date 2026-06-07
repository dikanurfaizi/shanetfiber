<x-layouts.cpanel.billings.app-layout title="Billings">

    <div class="card-header py-3">

        <h5 class="mb-2 font-weight-bold text-primary">{{ __('billing.header.history-index') }}</h5>
        @if (isset($message))
            <div class="alert alert-danger mt-2">
                <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
                {{ $message }}
            </div>
        @else
            <div class="alert alert-success mt-2">
                <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
                {{ __('billing.info.billing-history-found', ['count' => $data->total()]) }}
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
            <div class="collapse @if (request()->routeIs('billingHistory.index')) show @endif" id="collapseSearchCustomer">
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

                        @canany(['billing-export-selected', 'billing-export-all'])
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
                        @endcan

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
        {{-- Controls (atas tabel) --}}
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
            {{-- Pagination --}}
            <div class="mb-2">
                {{ $data->withQueryString()->links('vendor.pagination.simple-numeric') }}
            </div>

            @canany(['billing-export-selected', 'billing-export-all'])
                {{-- Container Export dengan Margin Left otomatis di Desktop agar ke kanan --}}
                <div class="d-flex flex-wrap align-items-center">

                    @can('billing-export-selected')
                        {{-- Form Filter Periode --}}
                        <form action="{{ route('billingHistory.index') }}" method="GET" id="periodeForm"
                            class="form-inline mb-2 mr-3">

                            <label for="periode_id" class="font-weight-bold text-primary mb-0 mr-2">Periode</label>

                            {!! Form::select('periode_id', $periode, request('periode_id'), [
                                'placeholder' => __('billing.placeholder.all-period'),
                                'class' => 'form-control form-control-sm w-auto',
                                'onchange' => 'this.form.submit()',
                                'id' => 'periode_id',
                            ]) !!}
                        </form>

                        {{-- Form Export Selected --}}
                        <form action="{{ route('billingHistory.export.selected') }}" method="GET" class="mb-2 mr-3">
                            <input type="hidden" name="status" value="{{ request('status') }}">
                            <input type="hidden" name="periode_id" value="{{ request('periode_id') }}">
                            <button type="submit" class="btn btn-sm btn-success text-nowrap">
                                <i class="fas fa-file-excel"></i> {{ __('billing.button.export-selected-period') }}
                            </button>
                        </form>
                    @endcan

                    @can('billing-export-all')
                        {{-- Form Export Per Tahun (Peningkatan dari Export All lama) --}}
                        <form action="{{ route('billingHistory.export.year') }}" method="GET"
                            class="form-inline mb-2 align-items-center">
                            <label for="export_year"
                                class="font-weight-bold text-dark mb-0 mr-2">{{ __('billing.label.export-year') }}</label>

                            {!! Form::select('export_year', $years, request('export_year', now()->year), [
                                'class' => 'form-control form-control-sm w-auto mr-2',
                                'id' => 'export_year',
                            ]) !!}

                            <button type="submit" class="btn btn-sm btn-dark text-nowrap">
                                <i class="fas fa-download"></i> {{ __('billing.button.export-year') }}
                            </button>
                        </form>
                    @endcan

                </div>
            @endcanany
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
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($data as $key => $billing)
                            {{-- @dd($billing->total_amount) --}}
                            @php
                                $i++;
                                $periodeText = $billing->billingSubscription?->billingPeriode->name
                                    ? \Carbon\Carbon::parse(
                                        $billing->billingSubscription->billingPeriode->name,
                                    )->format('M Y')
                                    : 'No Periode';

                                $billAmount = (float) ($billing->total_amount ?? $billing->base_price);
                                $badgeClass = 'badge bg-blue-soft ';
                                if ($billing->status === 'LS') {
                                    $badgeClass .= 'text-success';
                                } elseif ($billing->status === 'VD') {
                                    $badgeClass .= 'text-muted';
                                } elseif ($billing->status === 'PL') {
                                    $badgeClass .= 'text-warning';
                                } else {
                                    $badgeClass .= 'text-danger';
                                }
                            @endphp
                            {{-- <pre>{{ var_export($billing, true) }}</pre> --}}

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
                            </tr>

                            {{-- ✅ Mobile View: Parent Row --}}
                            <tr class="d-table-row d-sm-none" onclick="toggleExpand('{{ $billing->slug }}')">
                                <td colspan="8" style="cursor: pointer;">
                                    <div class="mobile-row-parent">
                                        <div>
                                            <strong>#{{ $i }}</strong> - <span
                                                class="text-primary">{{ $billing->customer->name }}</span><br>
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
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="alert alert-danger mt-2">
                {{ __('billing.billing-history-not-found') }}
            </div>
        @endif
        {{-- Footer (pagination + show per page) --}}
        <div class="table-footer d-flex flex-wrap justify-content-between align-items-center mt-3 gap-2">
            {{-- Pagination --}}
            <div class="d-inline-flex align-items-center">
                {{ $data->withQueryString()->links('vendor.pagination.simple-numeric') }}
            </div>

            {{-- Show per page --}}
            <form method="GET" id="paginationForm" class="d-inline-flex align-items-center mb-3">
                <label for="perPage"
                    class="fw-bold text-primary me-2 mb-0 mr-1">{{ __('billing.label.show') }}</label>
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

                .table-footer nav,
                .table-footer .pagination {
                    margin-top: 0 !important;
                    margin-bottom: 0 !important;
                }
            }
        </style>
    @endsection
</x-layouts.cpanel.billings.app-layout>
