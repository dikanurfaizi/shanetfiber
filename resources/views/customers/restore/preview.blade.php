<x-layouts.cpanel.customers.app-layout title="Restore Customer">

    <div class="card shadow-sm border-0">

        {{-- =======================
        HEADER
        ======================== --}}
        <div class="card-header py-3 d-flex justify-content-between align-items-center bg-white border-bottom-0">
            <h5 class="m-0 font-weight-bold text-warning">
                <i class="fas fa-undo-alt me-1"></i>
                Restore & Billing Configuration
            </h5>

            <a href="{{ route('customers.trashed') }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
                <i class="fa fa-arrow-left me-1"></i> Back
            </a>
        </div>

        {{-- =======================
        BODY
        ======================== --}}
        <div class="card-body">

            {{-- WARNING --}}
            <div class="alert alert-warning d-flex align-items-start gap-2 mb-4">
                <i class="fas fa-exclamation-triangle mt-1 mr-1"></i>
                <div>
                    <strong>Restore Customer</strong><br>
                    Customer ini sedang dalam status <strong>deleted</strong>.
                    Silakan pastikan konfigurasi subscription, membership, dan addon sebelum melanjutkan restore.
                </div>
            </div>

            <form id="customer-restore-form" method="POST"
                action="{{ route('customers.restore.confirm', $customer->slug) }}">
                @csrf

                <input type="hidden" name="restore_intent" value="CONFIRM_RESTORE">

                {{-- =======================
                MAIN FORM GRID
                ======================== --}}
                <div class="row">

                    {{-- LEFT COLUMN --}}
                    <div class="col-md-6">

                        {{-- PACKAGE --}}
                        <div class="form-group mb-3">
                            <label class="fw-semibold">
                                Package <span class="text-danger">*</span>
                            </label>
                            <select name="package_id" id="package-dropdown" class="form-control" required>
                                <option value="">Select Package</option>
                                @foreach ($packages as $package)
                                    <option value="{{ $package->id }}"
                                        {{ $package->id == $current_package_id ? 'selected' : '' }}>
                                        {{ $package->name }} - @moneyIDR($package->price)
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- DURATION --}}
                        <div id="duration-wrapper" class="form-group mb-3 {{ $current_duration_id ? '' : 'd-none' }}">
                            <label class="fw-semibold">Duration</label>
                            <select name="package_duration_id" id="duration-select" class="form-control">
                                @if ($current_duration_id)
                                    <option value="{{ $current_duration_id }}" selected>
                                        {{ $current_duration_name }}
                                    </option>
                                @endif
                            </select>
                        </div>

                        {{-- MEMBERSHIP --}}
                        <div class="form-group mb-3">
                            <label class="fw-semibold">Membership</label>
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

                    </div>

                    {{-- RIGHT COLUMN --}}
                    <div class="col-md-6">

                        {{-- BILLING OPTION --}}
                        <div class="form-group mb-3">
                            <label class="fw-semibold">Billing Option</label><br>

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

                        {{-- BILLING PERIODE --}}
                        <div id="periode-wrapper"
                            class="form-group mb-3 {{ $billing_option === 'one_time' ? '' : 'd-none' }}">
                            <label class="fw-semibold">Billing Period</label>
                            <select name="billing_periode_name" class="form-control"
                                {{ $billing_option === 'one_time' ? '' : 'disabled' }}>
                                <option value="">-- Select Period --</option>
                                @foreach ($availablePeriodes as $periode)
                                    <option value="{{ $periode['name'] }}">
                                        {{ $periode['label'] ?? $periode['name'] }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- DISCOUNT PREVIEW --}}
                        <div class="form-group mb-3">
                            <label class="fw-semibold">Estimated Discount</label>
                            <input type="text" id="discount_display" class="form-control bg-light fw-bold" readonly
                                value="{{ $discountEstimate
                                    ? 'Rp ' .
                                        number_format($discountEstimate['effective_discount']) .
                                        ' (' .
                                        number_format($discountEstimate['effective_percent'], 2) .
                                        '%)'
                                    : '' }}">
                            <small class="text-muted">
                                Nilai final dihitung ulang saat billing digenerate
                            </small>
                        </div>

                    </div>
                </div>

                {{-- =======================
                ADDONS SECTION
                ======================== --}}
                <hr class="my-4">

                <h6 class="fw-bold text-secondary mb-3">
                    <i class="fas fa-puzzle-piece me-1"></i>
                    Add-on Services
                </h6>

                <div class="addons-desktop d-none d-md-block">
                    <table class="table table-sm align-middle">
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
                                    <td><strong>{{ $addon->name }}</strong></td>
                                    <td>Rp {{ number_format($addon->price) }}</td>
                                    <td>
                                        <input type="number" min="1"
                                            class="form-control form-control-sm addon-qty"
                                            name="addons[{{ $addon->id }}][qty]"
                                            value="{{ $activeAddon?->qty ?? 1 }}"
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
                ADDONS (MOBILE)
                ======================= --}}
                <div class="addons-mobile d-md-none">

                    @foreach ($addons as $addon)
                        @php $activeAddon = $activeCustomerAddons->get($addon->id); @endphp

                        <div class="addon-card {{ $activeAddon ? 'active' : '' }}" data-addon="{{ $addon->id }}">

                            {{-- HEADER --}}
                            <div class="addon-header">
                                <label class="addon-check">
                                    <input type="checkbox" class="addon-toggle-mirror"
                                        {{ $activeAddon ? 'checked' : '' }}>
                                    <span class="addon-name">{{ $addon->name }}</span>
                                </label>

                                <span class="addon-price">
                                    Rp {{ number_format($addon->price) }}
                                </span>
                            </div>

                            {{-- BODY (hidden when inactive) --}}
                            <div class="addon-body">
                                <div class="addon-field">
                                    <label>Qty</label>
                                    <input type="number" min="1" class="addon-qty-mirror"
                                        value="{{ $activeAddon?->qty ?? 1 }}" {{ $activeAddon ? '' : 'disabled' }}>
                                </div>

                                <div class="addon-field">
                                    <label>Subtotal</label>
                                    <strong class="addon-subtotal-mirror">
                                        Rp {{ number_format($activeAddon?->subtotal ?? 0) }}
                                    </strong>
                                </div>
                            </div>

                        </div>
                    @endforeach

                </div>


                {{-- =======================
                FOOTER ACTION
                ======================= --}}
                <div class="restore-footer mt-5">
                    <div class="d-flex flex-column flex-md-row align-items-stretch gap-3">

                        {{-- PRIMARY ACTION --}}
                        <button type="submit"
                            class="btn btn-success px-5 rounded-pill fw-semibold d-flex align-items-center gap-2 justify-content-center mr-1 mb-2">
                            <i class="fa fa-undo mr-1"></i>
                            Restore & Generate Billing
                        </button>

                        {{-- SECONDARY --}}
                        <a href="{{ route('customers.trashed') }}"
                            class="btn btn-outline-secondary px-4 rounded-pill text-center mb-2">
                            Cancel
                        </a>

                    </div>
                </div>

            </form>
        </div>
    </div>

    @section('custom_styles')
        <style>
            /* ============================
                                                RESTORE FOOTER ACTION
                                                ============================ */
            .restore-footer {
                border-top: 1px solid #e5e7eb;
                padding-top: 24px;
            }

            .restore-footer .btn {
                min-height: 44px;
                font-size: 15px;
            }

            /* Desktop: keep natural width */
            @media (min-width: 768px) {
                .restore-footer {
                    display: flex;
                    justify-content: flex-start;
                }
            }

            /* Mobile: full width stack */
            @media (max-width: 767px) {
                .restore-footer .btn {
                    width: 100%;
                }
            }

            @media (max-width: 767px) {

                .addons-mobile {
                    display: flex;
                    flex-direction: column;
                    gap: 12px;
                }

                .addon-card {
                    border: 1px solid #e5e7eb;
                    border-radius: 14px;
                    padding: 12px 14px;
                    background: #ffffff;
                    transition: all .2s ease;
                }

                /* Inactive state */
                .addon-card:not(.active) {
                    background: #f9fafb;
                    opacity: .65;
                }

                /* Header */
                .addon-header {
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                    gap: 10px;
                }

                .addon-check {
                    display: flex;
                    align-items: center;
                    gap: 8px;
                    font-weight: 600;
                    font-size: 15px;
                }

                .addon-price {
                    font-size: 13px;
                    color: #6b7280;
                    white-space: nowrap;
                }

                /* Body (detail) */
                .addon-body {
                    display: none;
                    margin-top: 12px;
                    gap: 12px;
                }

                .addon-card.active .addon-body {
                    display: flex;
                }

                .addon-field {
                    flex: 1;
                }

                .addon-field label {
                    font-size: 11px;
                    color: #9ca3af;
                    text-transform: uppercase;
                    margin-bottom: 4px;
                    display: block;
                }

                .addon-qty-mirror {
                    width: 100%;
                    height: 44px;
                    border-radius: 12px;
                    border: 1.5px solid #d1d5db;
                    padding: 0 14px;
                    font-size: 16px;
                    font-weight: 600;
                }

                .addon-subtotal-mirror {
                    display: block;
                    height: 44px;
                    padding: 10px 14px;
                    border-radius: 12px;
                    background: #f3f4f6;
                    font-weight: 700;
                    font-size: 15px;
                }
            }
        </style>
    @endsection

    {{-- =======================
    JS INIT
    ======================== --}}
    @section('custom_scripts')
        <script>
            document.addEventListener("DOMContentLoaded", function() {
                if (typeof window.initBillingRestoreForm === "function") {
                    window.initBillingRestoreForm();
                }
            });
        </script>
    @endsection

</x-layouts.cpanel.customers.app-layout>
