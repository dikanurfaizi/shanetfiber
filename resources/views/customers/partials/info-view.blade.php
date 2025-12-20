{{-- DESKTOP VIEW --}}
<div class="d-none d-md-block">
    @include('customers.partials.table-open')

    <tr>
        <td><strong>ID</strong></td>
        <td>: {{ $customer_id }}</td>

        <td><strong>Responsibility</strong></td>
        <td>: {{ $responsibility }}</td>
    </tr>

    <tr>
        <td><strong>Name</strong></td>
        <td>: {{ $name }}</td>

        <td><strong>Email</strong></td>
        <td>: {{ $email }}</td>
    </tr>

    <tr>
        <td><strong>Address</strong></td>
        <td>: {{ $address }}</td>

        <td><strong>Contact</strong></td>
        <td>: {{ $contact }}</td>
    </tr>

    <tr>
        <td><strong>Activation Date</strong></td>
        <td>
            :
            {{ $activation_date ? \Carbon\Carbon::parse($activation_date)->format('d F Y') : '-' }}
        </td>
        <td></td>
        <td></td>
    </tr>

    @include('customers.partials.table-close')
</div>

{{-- MOBILE VIEW --}}
<div class="d-block d-md-none mobile-summary">

    <div class="summary-row">
        <span>ID</span>
        <strong>{{ $customer_id }}</strong>
    </div>

    <div class="summary-row">
        <span>Responsibility</span>
        <strong>{{ $responsibility }}</strong>
    </div>

    <div class="summary-row">
        <span>Name</span>
        <strong>{{ $name }}</strong>
    </div>

    <div class="summary-row">
        <span>Email</span>
        <strong class="text-break">{{ $email }}</strong>
    </div>

    <div class="summary-row">
        <span>Contact</span>
        <strong>{{ $contact }}</strong>
    </div>

    <div class="summary-row">
        <span>Address</span>
        <strong class="text-break">{{ $address }}</strong>
    </div>

    <div class="summary-row">
        <span>Activation Date</span>
        <strong>
            {{ $activation_date ? \Carbon\Carbon::parse($activation_date)->format('d M Y') : '-' }}
        </strong>
    </div>

</div>

<div class="mt-3 text-end">
    @can('customer-edit')
        <a href="{{ route('customers.show', [$customer->slug, 'tab' => 'info', 'edit' => 1]) }}" class="btn-soft btn-blue">
            <i class="fa fa-edit"></i> Edit Customer Data
        </a>
    @endcan
</div>

<style>
    /* ===== MOBILE SUMMARY (REUSABLE) ===== */
    .mobile-summary {
        display: flex;
        flex-direction: column;
        gap: 10px;
        padding-bottom: 16px;
    }

    .summary-row {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        font-size: 14px;
    }

    .summary-row span {
        color: #6b7280;
        min-width: 110px;
    }

    .summary-row strong {
        font-weight: 600;
        text-align: right;
        font-variant-numeric: tabular-nums;
    }
</style>
