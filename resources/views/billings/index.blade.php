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
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mt-3">
            <div>
                {{ $data->appends(['perPage' => request('perPage')])->links('vendor.pagination.simple-numeric') }}
            </div>

            <form method="GET" id="paginationForm" class="d-flex align-items-center" style="margin: 5px;">
                <div class="d-flex align-items-center gap-2">
                    <span class="font-weight-bold text-primary">Show</span>
                    <select name="perPage" id="perPage" class="form-control"
                        onchange="document.getElementById('paginationForm').submit();"
                        style="width: auto; min-width: 70px; height: 35px; padding: 2px 8px; text-align: center; margin: 0 10px;">
                        <option value="25" {{ request('perPage') == 25 ? 'selected' : '' }}>25</option>
                        <option value="50" {{ request('perPage') == 50 ? 'selected' : '' }}>50</option>
                        <option value="100" {{ request('perPage') == 100 ? 'selected' : '' }}>100</option>
                    </select>
                </div>

                <a href="{{ route('receipts.export', request()->query()) }}"
                    class="btn btn-success btn-sm d-flex align-items-center" style="height: 35px;">
                    <i class="fas fa-file-excel me-1 mr-2"></i>Export Receipts
                </a>
            </form>
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
                            <th class="text-center align-middle">{{ __('billing.table.payment-methode') }}</th>
                            <th class="text-center align-middle">{{ __('customer.table.action') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($data as $key => $billing)
                            @php
                                $i++;
                                $periodeText = Carbon\Carbon::parse($billing->billing_periode->name)->format('M Y');
                                $billAmount = $billing->paket_price === 0 ? 0 : $billing->paket_price + $billing->tax;
                                $badgeClass = 'badge bg-blue-soft ';
                                if ($billing->status === 'LS') {
                                    $badgeClass .= 'text-success';
                                } elseif ($billing->status === 'NP') {
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
                                            class="badge text-primary">{{ $billing->customer_name ?? 'no name' }}</span></a>
                                </td>
                                <td><span
                                        class="badge text-primary">{{ $billing->customer->address ?? 'no address' }}</span>
                                </td>
                                <td><span
                                        class="badge text-primary">{{ Carbon\Carbon::parse($billing->billing_periode->name)->format('M Y') ?? 'no periode' }}</span>
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
                                <td>{!! \App\Helpers\BillingHelper::getBillingActionButton($billing) !!}</td>
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
                                            <div style="flex: 1;">
                                                <strong>{{ __('customer.table.action') }}:</strong><br>
                                                {!! \App\Helpers\BillingHelper::getBillingActionButton($billing) !!}
                                            </div>
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

        {{ $data->withQueryString()->links('vendor.pagination.simple-numeric') }}
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
            }
        </style>
    @endsection
</x-layouts.cpanel.billings.app-layout>
