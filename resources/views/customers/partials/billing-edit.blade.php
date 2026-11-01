<form id="billing-edit-form" method="POST" action="{{ route('customers.update.billing', $customer->slug) }}"
    class="mt-4">

    @csrf
    @method('PATCH')

    <h5 class="mb-3">
        <i class="fa fa-edit"></i> {{ __('billing.header.edit') }}
    </h5>

    <div class="card border-0 shadow-sm">
        <div class="card-body">

            {{-- =======================
            SELECT PACKAGE
            ======================== --}}
            <div class="form-group mb-3">
                <label><strong>{{ __('customer.wizard.label.package') }}</strong> <span
                        class="text-danger">*</span></label>
                <select name="package_id" id="package-dropdown" class="form-control" required>
                    <option value="">{{ __('customer.wizard.placeholder.select_package') }}</option>
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
                <label><strong>{{ __('customer.wizard.label.duration') }}</strong></label>
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
                <label><strong>{{ __('customer.wizard.label.membership') }}</strong></label>
                <select name="member_id" id="member-dropdown" class="form-control">
                    <option value="">{{ __('customer.wizard.placeholder.non_member') }}</option>
                    @foreach ($members as $member)
                        <option value="{{ $member->id }}"
                            {{ $member->id == $current_membership_id ? 'selected' : '' }}>
                            {{ $member->name }}
                            ({{ __('customer.wizard.label.discount') }}
                            {{ $member->discount_type === 'amount' ? 'Rp ' . number_format($member->discount_value) : (int) $member->discount_value . '%' }})
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- =======================
            BILLING OPTION
            ======================== --}}
            <div class="form-group mb-3">
                <label><strong>{{ __('customer.wizard.label.billing_option') }}</strong></label><br>

                <label class="mr-3">
                    <input type="radio" name="billing_option" value="monthly"
                        {{ $billing_option === 'monthly' ? 'checked' : '' }}>
                    {{ __('customer.wizard.label.monthly') }}
                </label>

                <label>
                    <input type="radio" name="billing_option" value="one_time"
                        {{ $billing_option === 'one_time' ? 'checked' : '' }}>
                    {{ __('customer.wizard.label.one_time') }}
                </label>
            </div>

            {{-- =======================
            BILLING PERIODE
            ======================== --}}
            <div id="periode-wrapper" class="form-group mb-3 {{ $billing_option === 'one_time' ? '' : 'd-none' }}">
                <label><strong>{{ __('customer.wizard.label.select_billing_period') }}</strong></label>
                <select name="billing_periode_name" class="form-control"
                    {{ $billing_option === 'one_time' ? '' : 'disabled' }}>
                    <option value="">-- {{ __('customer.wizard.placeholder.select_billing_period') }} --</option>
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
                <label><strong>{{ __('customer.wizard.label.discount_applied') }}</strong></label>

                <input type="text" id="discount_display" class="form-control" readonly
                    value="{{ $discountEstimate
                        ? 'Rp ' .
                            number_format($discountEstimate['effective_discount']) .
                            ' (' .
                            number_format($discountEstimate['effective_percent'], 2) .
                            '%)'
                        : '' }}">

                <small class="text-muted">
                    * {{ __('billing.info.recalculate_discount_note') }}
                </small>
            </div>

            {{-- =======================
            ADDONS (DESKTOP)
            ======================== --}}
            <div class="addons-desktop d-none d-md-block mb-3">
                <table class="table table-sm align-middle addons-table mb-0">
                    <thead class="table-light">
                        <tr>
                            <th width="50"></th>
                            <th>{{ __('customer.wizard.label.addon_name') }}</th>
                            <th width="140">{{ __('customer.wizard.label.price') }}</th>
                            <th width="120">{{ __('customer.wizard.label.qty') }}</th>
                            <th width="160">{{ __('customer.wizard.label.subtotal') }}</th>
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
            ======================== --}}
            <div class="addons-mobile d-md-none mb-3">
                @foreach ($addons as $addon)
                    @php $activeAddon = $activeCustomerAddons->get($addon->id); @endphp

                    <div class="addon-card {{ $activeAddon ? 'active' : '' }}" data-addon="{{ $addon->id }}">

                        <div class="addon-header">
                            <label class="addon-check mb-0">
                                <input type="checkbox" class="addon-toggle-mirror" {{ $activeAddon ? 'checked' : '' }}>
                                <span>{{ $addon->name }}</span>
                            </label>

                            <span class="addon-price">
                                Rp {{ number_format($addon->price) }}
                            </span>
                        </div>

                        <div class="addon-body">
                            <div class="field">
                                <label>{{ __('customer.wizard.label.qty') }}</label>
                                <input type="text" min="1" class="addon-qty-mirror"
                                    value="{{ $activeAddon?->qty ?? 1 }}" {{ $activeAddon ? '' : 'disabled' }}>
                            </div>

                            <div class="field">
                                <label>{{ __('customer.wizard.label.subtotal') }}</label>
                                <strong class="addon-subtotal-mirror">
                                    Rp {{ number_format($activeAddon?->subtotal ?? 0) }}
                                </strong>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

        </div>

        {{-- ACTION BUTTONS --}}
        <div class="card-footer bg-white border-top-0 pt-0 pb-4">
            <div class="action-group">
                <button type="submit" class="btn-soft btn-green mb-2 mb-md-0">
                    <i class="fa fa-save mr-1"></i> {{ __('billing.button.save_regenerate') }}
                </button>

                <a href="{{ route('customers.show', [$customer->slug, 'tab' => 'billing']) }}"
                    class="btn-soft btn-gray">
                    {{ __('billing.button.cancel') }}
                </a>
            </div>
        </div>

    </div>
</form>

@section('custom_styles')
    <style>
        /* ===== BASE CUSTOM BUTTONS ===== */
        .btn-soft {
            border: none;
            border-radius: 6px;
            padding: 10px 20px;
            font-size: 14px;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            text-decoration: none !important;
            cursor: pointer;
            transition: all 0.2s ease-in-out;
            box-sizing: border-box !important;
            min-height: 44px;
            line-height: 1.3;
        }

        .btn-green {
            background-color: #28a745;
            color: #ffffff !important;
        }

        .btn-green:hover {
            background-color: #218838;
            box-shadow: 0 3px 8px rgba(40, 167, 69, 0.3);
        }

        .btn-gray {
            background-color: #6c757d;
            color: #ffffff !important;
        }

        .btn-gray:hover {
            background-color: #5a6268;
            box-shadow: 0 3px 8px rgba(108, 117, 125, 0.3);
        }

        /* ===== ACTION GROUP (DESKTOP) ===== */
        .action-group {
            display: flex;
            align-items: center;
            justify-content: flex-start;
            gap: 12px;
        }

        /* ===== RESPONSIVE MOBILE (BS4 MAX-WIDTH 767.98px) ===== */
        @media (max-width: 767.98px) {
            .action-group {
                display: flex !important;
                flex-direction: column !important;
                align-items: stretch !important;
                /* Paksa seluruh child menyamakan lebar full */
                width: 100% !important;
            }

            .action-group .btn-soft {
                display: flex !important;
                width: 100% !important;
                /* Paksa tag <a> dan <button> selebar 100% */
                max-width: 100% !important;
                justify-content: center !important;
                align-items: center !important;
                text-align: center !important;
                box-sizing: border-box !important;
                margin-left: 0 !important;
                margin-right: 0 !important;
                min-height: 44px !important;
            }

            .action-group .btn-green {
                margin-bottom: 0.5rem !important;
                /* Jarak mb-2 antar tombol di mobile */
            }

            .action-group .btn-gray {
                margin-bottom: 0 !important;
            }

            /* ========== ADDON MOBILE CARD ========== */
            .addon-card {
                border: 1px solid #dee2e6;
                border-radius: 12px;
                padding: 16px 16px 18px;
                margin-bottom: 14px;
                background: #fff;
                text-align: left;
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
                text-align: left;
            }

            .addon-body label {
                color: #6c757d;
                margin-bottom: 4px;
                display: block;
                font-size: 12px;
                letter-spacing: .4px;
                text-transform: uppercase;
                text-align: left;
            }

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
                text-align: left;
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
                text-align: left;
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
    </style>
@endsection
