{{-- DESKTOP VIEW --}}
<div class="d-none d-md-block">
    @include('customers.partials.table-open')

    <tr>
        <td><strong>{{ __('customer.label.id') }}</strong></td>
        <td>: {{ $customer_id }}</td>

        <td><strong>{{ __('customer.label.responsibility') }}</strong></td>
        <td>: {{ $responsibility }}</td>
    </tr>

    <tr>
        <td><strong>{{ __('customer.label.name') }}</strong></td>
        <td>: {{ $name }}</td>

        <td><strong>{{ __('customer.label.email') }}</strong></td>
        <td>: {{ $email }}</td>
    </tr>

    <tr>
        <td><strong>{{ __('customer.label.address') }}</strong></td>
        <td>: {{ $address }}</td>

        <td><strong>{{ __('customer.label.contact') }}</strong></td>
        <td>: {{ $contact }}</td>
    </tr>

    <tr>
        <td><strong>{{ __('customer.label.activation_date') }}</strong></td>
        <td>
            : {{ $activation_date ? \Carbon\Carbon::parse($activation_date)->format('d F Y') : '-' }}
        </td>
        <td><strong>{{ __('customer.label.coordinates') }}</strong></td>
        <td>: {{ $latitude ?? '-' }}, {{ $longitude ?? '-' }}</td>
    </tr>

    @include('customers.partials.table-close')
</div>

{{-- MOBILE VIEW --}}
<div class="d-block d-md-none mobile-summary">
    <div class="summary-row">
        <span>{{ __('customer.label.id') }}</span>
        <strong>{{ $customer_id }}</strong>
    </div>

    <div class="summary-row">
        <span>{{ __('customer.label.responsibility') }}</span>
        <strong>{{ $responsibility }}</strong>
    </div>

    <div class="summary-row">
        <span>{{ __('customer.label.name') }}</span>
        <strong>{{ $name }}</strong>
    </div>

    <div class="summary-row">
        <span>{{ __('customer.label.email') }}</span>
        <strong class="text-break">{{ $email }}</strong>
    </div>

    <div class="summary-row">
        <span>{{ __('customer.label.contact') }}</span>
        <strong>{{ $contact }}</strong>
    </div>

    <div class="summary-row">
        <span>{{ __('customer.label.address') }}</span>
        <strong class="text-break">{{ $address }}</strong>
    </div>

    <div class="summary-row">
        <span>{{ __('customer.label.activation_date') }}</span>
        <strong>
            {{ $activation_date ? \Carbon\Carbon::parse($activation_date)->format('d M Y') : '-' }}
        </strong>
    </div>

    <div class="summary-row">
        <span>{{ __('customer.label.coordinates') }}</span>
        <strong>
            {{ $latitude ?? '-' }}, {{ $longitude ?? '-' }}
        </strong>
    </div>
</div>

{{-- MAP CONTAINER --}}
@if (!empty($latitude) && !empty($longitude))
    <div class="customer-map-wrapper mt-3">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <label class="font-weight-bold mb-0">{{ __('customer.label.location_map') }}</label>
            <a href="https://maps.google.com/?q={{ $latitude }},{{ $longitude }}" target="_blank"
                rel="noopener noreferrer" class="btn btn-sm btn-outline-secondary">
                <i class="fa fa-external-link-alt mr-1"></i> {{ __('customer.button.open_google_maps') }}
            </a>
        </div>

        {{-- Simpan koordinat & detail ke data attributes --}}
        <div id="customer-map" data-lat="{{ $latitude }}" data-lng="{{ $longitude }}"
            data-name="{{ $name }}" data-address="{{ $address }}"
            style="height: 280px; width: 100%; border-radius: 8px; border: 1px solid #e5e7eb; z-index: 1;"></div>
    </div>
@endif

<div class="mt-3 text-right">
    @can('customer-edit')
        <a href="{{ route('customers.show', [$slug, 'tab' => 'info', 'edit' => 1]) }}" class="btn-soft btn-blue">
            <i class="fa fa-edit mr-1"></i> {{ __('customer.button.edit_customer') }}
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
