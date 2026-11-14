<x-layouts.cpanel.customers.app-layout title="Edit Customer">

    <div class="card-header py-3">
        <h6 class="m-0 fw-bold text-primary">
            {{ $customer->name . ' - ( ID: ' . $customer->customer_id . ' )' }}
        </h6>
    </div>

    <div class="row">
        <div class="col-lg-12">
            <form action="{{ route('customers.update', $customer->slug) }}" method="POST">
                @csrf
                @method('PATCH')
            <div class="card-body col-lg-8">

                {{-- Input Nama --}}
                <div class="mb-3">
                    <label for="name" class="form-label fw-bold">{{ __('customer.label.name') }}</label>
                    <input type="text" name="name" id="name" class="form-control" placeholder="{{ __('customer.placeholder.name') }}" value="{{ old('name', $customer->name) }}">
                    @if ($errors->has('name'))
                        <div class="invalid-feedback d-block">{{ $errors->first('name') }}</div>
                    @endif
                </div>

                {{-- Dropdown Package & Submit Package Button --}}
                <div class="row align-items-end mb-3">
                    <div class="col-md-9">
                        <label for="package-dropdown" class="form-label fw-bold">
                            {{ __('customer.label.package') }}
                        </label>

                        @if ($serverDisconnect === 'false')
                            <select name="package_id" class="form-select dynamic" data-dependent="details"
                                onchange="updateMemberDropdown(this.value)" id="package-dropdown">
                                @foreach ($packages as $package)
                                    <option value="{{ $package->id }}" data-price="{{ $package->price }}"
                                        {{ $package->id == ($customer->package_id ?? $customer->activeSubscription?->package_id) ? 'selected' : '' }}>
                                        {{ $package->name }} - @moneyIDR($package->price)
                                    </option>
                                @endforeach
                            </select>
                            @if ($errors->has('package_id'))
                                <div class="invalid-feedback d-block">{{ $errors->first('package_id') }}</div>
                            @endif
                        @else
                            @php
                                $currentPkg =
                                    $customer->activeSubscription?->package ??
                                    $customer->currentSubscription()?->package;
                            @endphp
                            <select disabled name="package_id" class="form-select bg-light dynamic"
                                data-dependent="details">
                                <option value="{{ $currentPkg?->id }}" selected>
                                    {{ $currentPkg?->name ?? 'Tanpa Paket' }} - @moneyIDR($currentPkg?->price ?? 0)
                                </option>
                            </select>
                            <div class="form-text text-danger mt-1">
                                {{ __('Server ') }} {{ $customer->mikrotik?->name }} {{ __('Offline or Disable!') }}
                            </div>
                        @endif
                    </div>

                    <div class="col-md-3 mt-2 mt-md-0">
                        <button type="submit" name="action" value="change_package" class="btn btn-success w-100">
                            <i class="fas fa-user-check me-1"></i>{{ __('customer.button.submit') }}
                        </button>
                    </div>
                </div>

                {{-- Dropdown Member --}}
                <div class="mb-3">
                    <label for="member-dropdown" class="form-label fw-bold">{{ __('customer.label.member') }}</label>
                    <select name="member_id" class="form-select dynamic" id="member-dropdown"
                        onchange="toggleDiscountField()">
                        <option value="non-member" {{ is_null($customer->member_id) ? 'selected' : '' }}>Non Member
                        </option>
                        @foreach ($members as $member)
                            <option value="{{ $member->id }}" data-discount="{{ $member->discount }}"
                                @if ($package_price > 0 && $member->discount > $package_price) disabled @endif
                                {{ $customer->member_id == $member->id ? 'selected' : '' }}>
                                {{ $member->type }} - @moneyIDR($member->discount)
                            </option>
                        @endforeach
                    </select>
                </div>

                <input type="hidden" name="member_id" id="hidden_member_id" value="{{ $customer->member_id ?? '' }}">

                {{-- Billing Options --}}
                <div class="row" id="billing-options" style="display: none;">
                    <div class="col-md-12 mb-3">
                        <label class="form-label fw-bold">{{ __('customer.label.billing-options') }}</label><br>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="billing_option" id="monthly"
                                value="1"
                                {{ $customer->billing_option == 1 || $customer->billing_option == null || $customer->billing_option == 0 ? 'checked' : '' }}>
                            <label class="form-check-label" for="monthly">{{ __('customer.label.monthly') }}</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="billing_option" id="one-time"
                                value="2" {{ $customer->billing_option == 2 ? 'checked' : '' }}>
                            <label class="form-check-label"
                                for="one-time">{{ __('customer.label.1-time-bill') }}</label>
                        </div>
                    </div>

                    {{-- Dropdown Periode --}}
                    <div class="col-md-12 mb-3" id="periode-dropdown-wrapper" style="display: none;">
                        <label for="periode_billing"
                            class="form-label fw-bold">{{ __('customer.label.select-billing-period') }}</label>
                        <select name="periode_billing" class="form-select" id="periode_billing">
                            @foreach ($availablePeriodes as $periode)
                                <option value="{{ $periode }}"
                                    {{ old('periode_billing', $customer->billing_period) == $periode ? 'selected' : '' }}>
                                    {{ $periode }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- Discount Inputs --}}
                <div class="mb-3">
                    <label for="discount_nominal"
                        class="form-label fw-bold">{{ __('customer.label.discount') }}</label>
                    <div class="row g-2">
                        <div class="col-md-6">
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="text" name="discount_nominal" id="discount_nominal" class="form-control" placeholder="{{ __('customer.placeholder.discount_nominal') }}" value="{{ old('discount_nominal', $customer->customerSubscription->discount_nominal ?? '') }}" oninput="formatRupiah(this); calculateDiscount();">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="input-group">
                                <input type="text" name="discount" id="discount" class="form-control" placeholder="{{ __('customer.placeholder.discount') }}" value="{{ old('discount', $customer->customerSubscription->discount ?? '') }}" readonly>
                                <span class="input-group-text">%</span>
                            </div>
                        </div>
                    </div>
                    @if ($errors->has('discount'))
                        <div class="invalid-feedback d-block">{{ $errors->first('discount') }}</div>
                    @endif
                </div>

                {{-- Email --}}
                <div class="mb-3">
                    <label for="email" class="form-label fw-bold">{{ __('customer.label.email') }}</label>
                    <input type="text" name="email" id="email" class="form-control" placeholder="{{ __('customer.placeholder.email') }}" value="{{ old('email', $customer->email) }}">
                    @if ($errors->has('email'))
                        <div class="invalid-feedback d-block">{{ $errors->first('email') }}</div>
                    @endif
                </div>

                {{-- Password --}}
                <div class="mb-3">
                    <label for="password" class="form-label fw-bold">{{ __('customer.label.password') }}</label>
                    <input type="password" name="password" id="password" class="form-control" placeholder="{{ __('customer.placeholder.password') }}">
                    @if ($errors->has('password'))
                        <div class="invalid-feedback d-block">{{ $errors->first('password') }}</div>
                    @endif
                </div>

                {{-- Confirm Password --}}
                <div class="mb-3">
                    <label for="password_confirmation"
                        class="form-label fw-bold">{{ __('customer.label.confirm-password') }}</label>
                    <input type="password" name="password_confirmation" id="password_confirmation" class="form-control" placeholder="{{ __('customer.placeholder.confirm-password') }}">
                    @if ($errors->has('password_confirmation'))
                        <div class="invalid-feedback d-block">{{ $errors->first('password_confirmation') }}</div>
                    @endif
                </div>

                {{-- Address --}}
                <div class="mb-3">
                    <label for="address" class="form-label fw-bold">{{ __('customer.label.address') }}</label>
                    <input type="text" name="address" id="address" class="form-control" placeholder="{{ __('customer.placeholder.address') }}" value="{{ old('address', $customer->address) }}">
                    @if ($errors->has('address'))
                        <div class="invalid-feedback d-block">{{ $errors->first('address') }}</div>
                    @endif
                </div>

                {{-- Contact --}}
                <div class="mb-3">
                    <label for="contact" class="form-label fw-bold">{{ __('customer.label.contact') }}</label>
                    <input class="form-control contact" id="contact" name="contact" type="text"
                        placeholder="{{ trans('customer.placeholder.contact') }}" value="{{ $customer_number }}">
                    @if ($errors->has('contact'))
                        <div class="invalid-feedback d-block">{{ $errors->first('contact') }}</div>
                    @endif
                </div>

                {{-- Responsibility (PIC/User) --}}
                <div class="mb-3">
                    <label for="user_id"
                        class="form-label fw-bold">{{ __('customer.label.responsibility') }}</label>
                    <select name="user_id" id="user_id" class="form-select dynamic">
                        @if ($customer->user_id !== null)
                            <option value="{{ $customer->user_id }}" selected>
                                {{ $customer->user->name }}
                            </option>
                        @endif
                        @foreach ($users as $user)
                            @if ($customer->user_id !== $user->id)
                                <option value="{{ $user->id }}">{{ $user->name }}</option>
                            @endif
                        @endforeach
                    </select>

                    @if ($errors->has('user_id'))
                        <div class="invalid-feedback d-block">{{ $errors->first('user_id') }}</div>
                    @endif
                </div>

                {{-- Activation Date --}}
                <div class="mb-3">
                    <label for="activation_date" class="form-label fw-bold">
                        {{ __('customer.label.activation-date') }}
                    </label>

                    @if ($serverDisconnect === 'false')
                        <input type="text" class="form-control" name="activation_date"
                            value="{{ $customer->activation_date ? \Carbon\Carbon::parse($customer->activation_date)->format('d F Y') : '' }}"
                            id="activation_date">
                        @if ($errors->has('activation_date'))
                            <div class="invalid-feedback d-block">{{ $errors->first('activation_date') }}</div>
                        @endif
                    @else
                        <input disabled type="text" value="{{ $customer->activation_date }}"
                            class="form-control bg-light">
                        <div class="form-text text-danger mt-1">
                            {{ __('Server ') }} {{ $customer->mikrotik?->name }} {{ __('Offline or Disable!') }}
                        </div>
                    @endif
                </div>

                <div id="map"></div>

            </div>

            {{-- Card Footer Actions --}}
            <div class="card-footer d-flex flex-wrap gap-2 justify-content-start align-items-center">
                <button type="submit" name="action" value="update_data" class="btn btn-success">
                    <i class="fas fa-user-check me-1"></i>{{ __('customer.button.update') }}
                </button>

                <a class="btn btn-primary" href="{{ route('customers.edit.router', $customer->slug) }}">
                    <i class="fas fa-user-edit me-1"></i>{{ __('customer.button.edit-router') }}
                </a>

                @if ($customer->internet_service == 'ppp')
                    <a class="btn btn-primary" href="{{ route('customers.edit.ppp', $customer->slug) }}">
                        <i class="fas fa-user-edit me-1"></i>{{ __('customer.button.edit-ppp') }}
                    </a>
                @elseif ($customer->internet_service == 'ip_static')
                    <a class="btn btn-primary" href="{{ route('customers.edit.ppp', $customer->slug) }}">
                        <i class="fas fa-user-edit me-1"></i>{{ __('customer.button.edit-ip-static') }}
                    </a>
                @endif

                <a class="btn btn-secondary" href="{{ route('customers.index') }}">
                    <i class="fa fa-arrow-left me-1"></i>{{ __('customer.button.back') }}
                </a>
            </div>
            </form>
        </div>
    </div>

    @section('custom_styles')
        <link href="{{ asset('css/flatpickr.min.css') }}" rel="stylesheet">
        <style>
            #map {
                height: 100%;
            }
        </style>
    @endsection

    @section('custom_scripts')
        <script src="{{ asset('js/flatpickr') }}"></script>
        <script>
            flatpickr("#activation_date", {
                enableTime: true,
                dateFormat: "d F Y",
            });
        </script>
        <script>
            function formatRupiah(input) {
                let value = input.value.replace(/[^,\d]/g, '');
                let packageDropdown = document.getElementById('package-dropdown');
                let selectedPackage = packageDropdown.options[packageDropdown.selectedIndex];
                let packagePrice = parseFloat(selectedPackage.getAttribute('data-price')) || 0;

                let numericValue = parseFloat(value.replace(/\./g, ''));
                if (numericValue > packagePrice) numericValue = packagePrice;

                input.value = new Intl.NumberFormat('id-ID').format(numericValue);
            }

            function toggleDiscountField() {
                const memberDropdown = document.getElementById('member-dropdown');
                const discountNominalField = document.getElementById('discount_nominal');
                const billingOptions = document.getElementById('billing-options');
                const selectedMember = memberDropdown.options[memberDropdown.selectedIndex];
                const isNonMember = selectedMember.value === 'non-member';

                const hiddenMemberInput = document.getElementById('hidden_member_id');
                if (!hiddenMemberInput) return;

                if (isNonMember) {
                    discountNominalField.removeAttribute('readonly');
                    discountNominalField.removeAttribute('disabled');
                    discountNominalField.value = '';
                    billingOptions.style.display = 'none';
                    billingOptions.value = '0';
                    hiddenMemberInput.value = '';
                } else {
                    discountNominalField.setAttribute('readonly', true);
                    discountNominalField.setAttribute('disabled', true);
                    const discount = parseFloat(selectedMember.getAttribute('data-discount')) || 0;
                    discountNominalField.value = discount.toLocaleString('id-ID');
                    billingOptions.style.display = 'block';
                    hiddenMemberInput.value = selectedMember.value;
                }

                togglePeriodeDropdown();
            }

            function togglePeriodeDropdown() {
                const monthlyRadio = document.getElementById('monthly');
                const oneTimeRadio = document.getElementById('one-time');
                const periodeWrapper = document.getElementById('periode-dropdown-wrapper');

                if (oneTimeRadio && oneTimeRadio.checked) {
                    periodeWrapper.style.display = 'block';
                } else {
                    periodeWrapper.style.display = 'none';
                }
            }

            function calculateNominal() {
                const discountPercentInput = document.getElementById('discount').value;
                const discountPercent = parseFloat(discountPercentInput) || 0;

                const dropdown = document.getElementById('package-dropdown');
                const selectedOption = dropdown.options[dropdown.selectedIndex].text;
                const match = selectedOption.match(/Rp\.\s*([\d,.]+)/);

                if (match) {
                    const priceString = match[1].replace(/\./g, '').replace(/,/g, '');
                    const price = parseInt(priceString.slice(0, -2)) || 0;
                    const discountNominal = price ? (discountPercent / 100) * price : 0;
                    document.getElementById('discount_nominal').value = new Intl.NumberFormat('id-ID').format(discountNominal);
                }
            }

            function calculateDiscount() {
                const packageDropdown = document.getElementById('package-dropdown');
                const selectedPackage = packageDropdown.options[packageDropdown.selectedIndex];
                const packagePrice = parseFloat(selectedPackage.getAttribute('data-price'));

                const memberDropdown = document.getElementById('member-dropdown');
                const selectedMember = memberDropdown.options[memberDropdown.selectedIndex];
                const isNonMember = selectedMember.value === 'non-member';
                let memberDiscount = parseFloat(selectedMember.getAttribute('data-discount')) || 0;

                const discountNominalField = document.getElementById('discount_nominal');

                if (!isNonMember) {
                    discountNominalField.value = memberDiscount.toLocaleString('id-ID');
                }

                const discountField = document.getElementById('discount');
                const discountNominal = parseFloat(discountNominalField.value.replace(/\./g, '').replace(',', '.')) || 0;

                const discountPercentage = packagePrice > 0 ? (discountNominal / packagePrice) * 100 : 0;
                discountField.value = discountPercentage.toFixed(2);
            }

            function updateMemberDropdown(selectedPackageId) {
                const packageDropdown = document.getElementById('package-dropdown');
                const selectedPackage = packageDropdown.options[packageDropdown.selectedIndex];
                const packagePrice = parseFloat(selectedPackage.getAttribute('data-price'));

                const memberDropdown = document.getElementById('member-dropdown');
                const members = memberDropdown.options;

                let bestValidIndex = -1;
                let maxValidDiscount = 0;

                for (let i = 0; i < members.length; i++) {
                    const memberDiscount = parseFloat(members[i].getAttribute('data-discount')) || 0;
                    if (members[i].value === "non-member") {
                        members[i].disabled = false;
                        continue;
                    }
                    if (memberDiscount > packagePrice) {
                        members[i].disabled = true;
                    } else {
                        members[i].disabled = false;
                        if (memberDiscount > maxValidDiscount) {
                            maxValidDiscount = memberDiscount;
                            bestValidIndex = i;
                        }
                    }
                }

                if (packagePrice === 0) {
                    memberDropdown.selectedIndex = 0;
                    document.getElementById('discount_nominal').value = 0;
                    document.getElementById('discount_nominal').readOnly = true;
                    document.getElementById('discount').value = 0;
                } else if (memberDropdown.value === "non-member") {
                    document.getElementById('discount_nominal').readOnly = false;
                } else {
                    if (bestValidIndex !== -1) {
                        memberDropdown.selectedIndex = bestValidIndex;
                    } else {
                        memberDropdown.selectedIndex = 0;
                    }
                    document.getElementById('discount_nominal').readOnly = true;
                }

                calculateDiscount();
            }

            document.addEventListener('DOMContentLoaded', function() {
                calculateNominal();
                toggleDiscountField();

                const monthlyRadio = document.getElementById('monthly');
                const oneTimeRadio = document.getElementById('one-time');

                if (monthlyRadio && oneTimeRadio) {
                    monthlyRadio.addEventListener('change', togglePeriodeDropdown);
                    oneTimeRadio.addEventListener('change', togglePeriodeDropdown);
                }
            });

            document.getElementById('package-dropdown').addEventListener('change', () => {
                updateMemberDropdown();
            });

            document.getElementById('member-dropdown').addEventListener('change', () => {
                calculateDiscount();
            });
        </script>
    @endsection
</x-layouts.cpanel.customers.app-layout>
