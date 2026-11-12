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
                    {{ $billing->customer->customer_id ?? '-' }}
                </span>
            </td>
        </tr>
        <tr>
            <th class="text-muted">Name</th>
            <td>{{ $billing->customer->name }}</td>
        </tr>
        <tr>
            <th class="text-muted">Address</th>
            <td>{{ $billing->customer->address ?? '-' }}</td>
        </tr>
    </table>

    {{-- =========================
    BILLING INFORMATION
    ========================= --}}
    <h6 class="text-primary mt-4 mb-3">
        <i class="fas fa-file-invoice-dollar"></i> Billing Information
    </h6>

    <table class="table table-sm table-borderless align-middle">
        <tr>
            <th style="width:30%" class="text-muted">Billing Number</th>
            <td>
                <span class="badge bg-primary text-white">
                    #{{ $billing->billing_number }}
                </span>
            </td>
        </tr>

        <tr>
            <th class="text-muted">Period</th>
            <td>
                {{ $billing->billingSubscription->billingPeriode->name ?? '-' }}
            </td>
        </tr>

        <tr>
            <th class="text-muted">Package</th>
            <td>{{ $billing->customer->active_package->name ?? '-' }}</td>
        </tr>

        <tr>
            <th class="text-muted">Created At</th>
            <td>
                {{ $billing->created_at->translatedFormat('d F Y, H:i') }}
            </td>
        </tr>
    </table>

    {{-- =========================
    PAYMENT SUMMARY
    ========================= --}}
    <h6 class="text-primary mt-4 mb-3">
        <i class="fas fa-money-bill-wave"></i> Payment Summary
    </h6>

    <table class="table table-sm table-borderless align-middle">
        <tr>
            <th style="width:30%" class="text-muted">Base Price</th>
            <td>@moneyIDR($billing->base_price)</td>
        </tr>

        @if ($websystem->enable_tax === 'true')
            <tr>
                <th class="text-muted">Tax (PPN)</th>
                <td>@moneyIDR($billing->tax)</td>
            </tr>
        @endif

        <tr>
            <th class="text-muted fw-bold">Total Amount</th>
            <td class="fw-bold text-success">
                @moneyIDR($billing->total_amount)
            </td>
        </tr>

        <tr>
            <th class="text-muted">Payment Status</th>
            <td>
                @if ($billing->status === 'LS')
                    <span class="badge bg-success text-white">
                        {{ __('billing.state.lunas') }}
                    </span>
                @else
                    <span class="badge bg-warning text-dark">
                        {{ __('billing.state.belum-lunas') }}
                    </span>
                @endif
            </td>
        </tr>
    </table>

</div>

<div class="card-footer">
    <a href="{{ url()->previous() }}" class="btn btn-sm btn-secondary">
        <i class="fas fa-arrow-left me-1"></i> Back
    </a>
</div>
