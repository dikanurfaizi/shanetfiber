<x-layouts.cpanel.billings.app-layout title="Receipt Detail">

    {{-- =========================
    HEADER
    ========================= --}}
    <div class="card-header py-3">
        <h5 class="m-0 font-weight-bold text-primary">
            <i class="fas fa-receipt"></i> Receipt Detail
        </h5>
    </div>

    <div class="card-body col-lg-8">

        {{-- =========================
        CUSTOMER INFORMATION
        ========================= --}}
        <h6 class="text-primary mb-3">
            <i class="fas fa-user"></i> Customer Information
        </h6>

        <table class="table table-sm table-borderless align-middle">
            <tr>
                <th style="width:30%" class="text-muted">Customer ID</th>
                <td>
                    <span class="badge bg-success text-white">
                        {{ $receipt->customer->customer_id ?? '-' }}
                    </span>
                </td>
            </tr>
            <tr>
                <th class="text-muted">Name</th>
                <td>{{ $receipt->customer_name }}</td>
            </tr>
            <tr>
                <th class="text-muted">Address</th>
                <td>{{ $receipt->customer_address }}</td>
            </tr>
        </table>

        {{-- =========================
        RECEIPT INFORMATION
        ========================= --}}
        <h6 class="text-primary mt-4 mb-3">
            <i class="fas fa-file-invoice"></i> Receipt Information
        </h6>

        <table class="table table-sm table-borderless align-middle">
            <tr>
                <th style="width:30%" class="text-muted">Receipt Number</th>
                <td>
                    <span class="badge bg-primary text-white">
                        {{ $receipt->number }}
                    </span>
                </td>
            </tr>

            <tr>
                <th class="text-muted">Period</th>
                <td>{{ $receipt->periode }}</td>
            </tr>

            <tr>
                <th class="text-muted">Created By</th>
                <td>
                    <span class="badge bg-danger-soft text-danger">
                        <i class="fas fa-id-card-alt"></i>
                        {{ $receipt->created_name ?? '-' }}
                    </span>
                </td>
            </tr>

            <tr>
                <th class="text-muted">Teller</th>
                <td>
                    <span class="badge bg-danger-soft text-danger">
                        <i class="fas fa-user"></i>
                        {{ $receipt->teller_name ?? '-' }}
                    </span>
                </td>
            </tr>
        </table>

        {{-- =========================
        ITEMS SUMMARY
        ========================= --}}
        <h6 class="text-primary mt-4 mb-3">
            <i class="fas fa-list"></i> Items
        </h6>

        <table class="table table-sm table-hover align-middle">
            <thead class="table-light">
                <tr>
                    <th>Item</th>
                    <th class="text-center">Type</th>
                    <th class="text-center">Qty</th>
                    <th class="text-end">Unit Price</th>
                    <th class="text-end">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($receiptItems as $item)
                    <tr class="{{ $item->type === 'discount' ? 'table-light text-danger' : '' }}">
                        <td>{{ $item->name }}</td>
                        <td class="text-center">
                            <span class="badge bg-secondary text-white">
                                {{ ucfirst($item->type) }}
                            </span>
                        </td>
                        <td class="text-center">{{ $item->qty }}</td>
                        <td class="text-end">@moneyIDR(abs($item->unit_price))</td>
                        <td class="text-end">@moneyIDR(abs($item->subtotal))</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        {{-- =========================
        PAYMENT INFORMATION
        ========================= --}}
        <h6 class="text-primary mt-4 mb-3">
            <i class="fas fa-money-bill-wave"></i> Payment Information
        </h6>

        <table class="table table-sm table-borderless align-middle">
            <tr>
                <th style="width:30%" class="text-muted">Payment Method</th>
                <td>{{ ucfirst($receipt->payment_methode) }}</td>
            </tr>

            <tr>
                <th class="text-muted">Payment Time</th>
                <td>
                    {{ \Carbon\Carbon::parse($receipt->payment_time)->translatedFormat('l, d M Y H:i:s') }}
                </td>
            </tr>

            @if (in_array($receipt->payment_methode, ['transfer', 'online']))
                <tr>
                    <th class="text-muted">Bank</th>
                    <td>{{ $receipt->bank_name }}</td>
                </tr>
                <tr>
                    <th class="text-muted">Account</th>
                    <td>
                        {{ $receipt->account_name }} <br>
                        <small class="text-muted">{{ $receipt->account_number }}</small>
                    </td>
                </tr>
            @endif

            <tr>
                <th class="text-muted">Subtotal</th>
                <td>@moneyIDR($subtotal)</td>
            </tr>

            <tr>
                <th class="text-muted">Tax (PPN)</th>
                <td>@moneyIDR($receipt->tax)</td>
            </tr>

            <tr>
                <th class="text-muted fw-bold">Total Paid</th>
                <td class="fw-bold text-success">@moneyIDR($total)</td>
            </tr>
        </table>

    </div>

    {{-- =========================
    ACTIONS
    ========================= --}}
    <div class="card-footer">
        <a class="btn btn-sm btn-secondary" href="{{ URL::previous() }}">
            <i class="fa fa-arrow-left"></i> Back
        </a>

        <a class="btn btn-success btn-sm" href="{{ route('receipts.print', $receipt->slug) }}" target="_blank">
            <i class="fa fa-file-pdf"></i> PDF
        </a>

        <a class="btn btn-outline-success btn-sm" href="{{ route('receipts.thermal', $receipt->slug) }}"
            target="_blank">
            <i class="fa fa-print"></i> Thermal
        </a>
    </div>

</x-layouts.cpanel.billings.app-layout>
