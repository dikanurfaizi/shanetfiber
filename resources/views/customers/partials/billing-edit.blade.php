<form id="billing-edit-form" method="POST" action="{{ route('customers.update.billing', $customer->slug) }}"
    class="mt-4">

    @csrf
    @method('PATCH')

    <h5 class="mb-3">
        <i class="fa fa-edit"></i> Edit Billing Configuration
    </h5>

    <div class="card border-0 shadow-sm">
        <div class="card-body">

            {{-- =======================
            SELECT PACKAGE
            ======================== --}}
            <div class="form-group mb-3">
                <label><strong>Package</strong> <span class="text-danger">*</span></label>
                <select name="package_id" id="package-dropdown" class="form-control" required>
                    <option value="">Select Package</option>
                    @foreach ($packages as $package)
                        <option value="{{ $package->id }}" {{ $package->id == $current_package_id ? 'selected' : '' }}>
                            {{ $package->name }} - @moneyIDR($package->price)
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- =======================
            PACKAGE DURATION
            ======================== --}}
            <div class="form-group mb-3 {{ $current_duration_id ? '' : 'd-none' }}" id="duration-wrapper">
                <label><strong>Duration</strong></label>
                <select name="package_duration_id" id="duration-select" class="form-control">
                    @if ($current_duration)
                        <option value="{{ $current_duration->id }}" selected>
                            {{ $current_duration->duration->name_en }}
                        </option>
                    @endif
                </select>
            </div>

            {{-- =======================
            MEMBERSHIP
            ======================== --}}
            <div class="form-group mb-3">
                <label><strong>Membership</strong></label>
                <select name="member_id" id="member-dropdown" class="form-control">
                    <option value="">Non Member</option>
                    @foreach ($members as $member)
                        <option value="{{ $member->id }}"
                            {{ $member->id == $current_membership_id ? 'selected' : '' }}>
                            {{ $member->name }}
                            (Diskon Rp {{ number_format($member->discount_value) }})
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- =======================
            BILLING OPTION
            ======================== --}}
            <div class="form-group mb-3">
                <label><strong>Billing Option</strong></label><br>

                <label class="me-3">
                    <input type="radio" name="billing_option" value="monthly"
                        {{ $billing_option === 'monthly' ? 'checked' : '' }}>
                    Monthly
                </label>

                <label>
                    <input type="radio" name="billing_option" value="one_time"
                        {{ $billing_option === 'one_time' ? 'checked' : '' }}>
                    One-Time Period
                </label>
            </div>

            {{-- =======================
            BILLING PERIODE
            ======================== --}}
            <div id="periode-wrapper" class="form-group mb-3 {{ $billing_option === 'one_time' ? '' : 'd-none' }}">
                <label><strong>Select Billing Period</strong></label>
                <select name="billing_periode_name" class="form-control"
                    {{ $billing_option === 'one_time' ? '' : 'disabled' }}>
                    <option value="">-- Select Period --</option>
                    @foreach ($availablePeriodes as $periode)
                        <option value="{{ $periode['name'] }}"
                            {{ $billing_period == $periode['name'] ? 'selected' : '' }}>
                            {{ $periode['name'] }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- =======================
                 DISCOUNT PREVIEW
            ======================== --}}
            <div class="form-group mb-3">
                <label><strong>Estimated Discount</strong></label>

                <input type="text" id="discount_display" class="form-control" readonly
                    value="{{ $discountEstimate
                        ? 'Rp ' .
                            number_format($discountEstimate['effective_discount']) .
                            ' (' .
                            number_format($discountEstimate['effective_percent'], 2) .
                            '%)'
                        : '' }}">

                <small class="text-muted">
                    * Nilai final dihitung ulang saat billing digenerate
                </small>
            </div>


            {{-- =======================
     ADDONS (DESKTOP)
======================= --}}
            <div class="addons-desktop d-none d-md-block">
                <table class="table table-sm align-middle addons-table">
                    <thead class="table-light">
                        <tr>
                            <th width="50"></th>
                            <th>Addon</th>
                            <th width="140">Price</th>
                            <th width="120">Qty</th>
                            <th width="160">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($addons as $addon)
                            @php $activeAddon = $activeCustomerAddons->get($addon->id); @endphp
                            <tr data-addon="{{ $addon->id }}">
                                <td class="text-center">
                                    <input type="checkbox" class="addon-toggle"
                                        name="addons[{{ $addon->id }}][addon_id]" value="{{ $addon->id }}"
                                        {{ $activeAddon ? 'checked' : '' }}>
                                </td>

                                <td>
                                    <strong>{{ $addon->name }}</strong>
                                </td>

                                <td>Rp {{ number_format($addon->price) }}</td>

                                <td>
                                    <input type="number" min="1" class="form-control form-control-sm addon-qty"
                                        name="addons[{{ $addon->id }}][qty]" value="{{ $activeAddon?->qty ?? 1 }}"
                                        {{ $activeAddon ? '' : 'disabled' }}>
                                </td>

                                <td>
                                    <strong class="addon-subtotal">
                                        Rp {{ number_format($activeAddon?->subtotal ?? 0) }}
                                    </strong>
                                </td>

                                <input type="hidden" name="addons[{{ $addon->id }}][unit_price]"
                                    value="{{ $addon->price }}">
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- =======================
     ADDONS (MOBILE VIEW)
======================= --}}
            <div class="addons-mobile d-md-none">
                @foreach ($addons as $addon)
                    @php $activeAddon = $activeCustomerAddons->get($addon->id); @endphp

                    <div class="addon-card {{ $activeAddon ? 'active' : '' }}" data-addon="{{ $addon->id }}">

                        <div class="addon-header">
                            <label class="addon-check">
                                <input type="checkbox" class="addon-toggle-mirror" {{ $activeAddon ? 'checked' : '' }}>
                                <span>{{ $addon->name }}</span>
                            </label>

                            <span class="addon-price">
                                Rp {{ number_format($addon->price) }}
                            </span>
                        </div>

                        <div class="addon-body">
                            <div class="field">
                                <label>Qty</label>
                                <input type="text" min="1" class="addon-qty-mirror"
                                    value="{{ $activeAddon?->qty ?? 1 }}" {{ $activeAddon ? '' : 'disabled' }}>
                            </div>

                            <div class="field">
                                <label>Subtotal</label>
                                <strong class="addon-subtotal-mirror">
                                    Rp {{ number_format($activeAddon?->subtotal ?? 0) }}
                                </strong>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

        </div>

        <div class="card-footer">
            <div class="d-flex flex-column flex-md-row">

                <button class="btn-soft btn-green w-100 btn-md-shrink px-4 mb-2 mb-md-0 mr-md-2">
                    <i class="fa fa-save"></i> Save & Regenerate Billing
                </button>

                <a href="{{ route('customers.show', [$customer->slug, 'tab' => 'billing']) }}"
                    class="btn-soft btn-gray w-100 btn-md-shrink px-4 text-center">
                    Cancel
                </a>

            </div>
        </div>

    </div>
</form>

@section('custom_styles')
    <style>
        @media (max-width: 768px) {

            /* ========== ADDON MOBILE CARD ========== */
            .addon-card {
                border: 1px solid #dee2e6;
                border-radius: 12px;
                padding: 16px 16px 18px;
                margin-bottom: 14px;
                background: #fff;
            }

            .addon-card:not(.active) {
                opacity: .5;
                background: #f8f9fa;
            }

            .addon-header {
                display: flex;
                justify-content: space-between;
                align-items: center;
                margin-bottom: 10px;
                gap: 10px;
                padding-bottom: 6px;
                border-bottom: 1px dashed #e9ecef;
            }

            .addon-check {
                display: flex;
                align-items: center;
                gap: 8px;
                font-weight: 600;
            }

            .addon-check span {
                font-size: 15px;
            }


            .addon-price {
                font-size: .85rem;
                color: #6c757d;
            }

            .addon-body {
                display: flex;
                gap: 16px;
                margin-top: 12px;
            }

            .addon-body .field {
                flex: 1;
            }

            .addon-body label {
                color: #6c757d;
                margin-bottom: 4px;
                display: block;
                font-size: 12px;
                letter-spacing: .4px;
                text-transform: uppercase;
            }

            /* FORCE MATCH AGAINST SB-ADMIN */
            .addons-mobile .addon-card input.addon-qty-mirror[type="number"] {
                -webkit-appearance: none;
                appearance: none;

                height: 48px !important;
                border-radius: 14px !important;
                border: 1.5px solid #dee2e6 !important;

                padding: 0 16px !important;
                font-size: 16px !important;
                font-weight: 600;

                background-color: #ffffff !important;
                box-shadow: inset 0 1px 2px rgba(0, 0, 0, .05);
            }

            .addons-mobile .addon-card input.addon-qty-mirror[type="number"]:disabled {
                background-color: #f1f3f5 !important;
                color: #adb5bd !important;
            }

            .addons-mobile .addon-card .addon-subtotal-mirror {
                display: block;
                min-height: 48px;
                padding: 12px 16px;

                border-radius: 14px;
                background: linear-gradient(180deg, #f8f9fa, #eef1f4);

                font-size: 16px;
                font-weight: 800;
                color: #212529;
            }

            .addons-mobile .addon-card .addon-subtotal-mirror::before {
                content: "≈ ";
                opacity: .6;
            }

            .addon-card.active {
                border-color: #0d6efd;
                box-shadow: 0 4px 10px rgba(13, 110, 253, .12);
            }
        }

        @media (min-width: 768px) {
            .btn-md-shrink {
                width: auto !important;
                flex: 0 0 auto !important;
                display: inline-flex;
                align-items: center;
            }
        }
    </style>
@endsection
