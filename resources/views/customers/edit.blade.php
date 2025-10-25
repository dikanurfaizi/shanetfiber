<x-layouts.cpanel.customers.app-layout title="Edit Customer">


    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">
            {{ $customer->name . ' - ( ID: ' . $customer->customer_id . ' )' }}
        </h6>
    </div>

    <div class="row">
        <div class="col-lg-12">
            {!! Form::model($customer, ['method' => 'PATCH', 'route' => ['customers.update', $customer->slug]]) !!}
            <div class="card-body col-lg-8">
                <div class="row align-items-center">
                    <div class="col-md-12 mb-3">
                        <label for="name"><strong>{{ __('customer.label.name') }}</strong></label>
                        {!! Form::text('name', null, ['placeholder' => __('customer.placeholder.name'), 'class' => 'form-control']) !!}
                        @if ($errors->has('name'))
                            <span class="text-danger">{{ $errors->first('name') }}</span>
                        @endif
                    </div>
                </div>
                <div class="row align-items-end mb-3">
                    <div class="col-md-9">
                        <label for="package"><strong>{{ __('customer.label.package') }}</strong></label>
                        @if ($serverDisconnect === 'false')
                            <select name="package_id" class="form-control dynamic" data-dependent="details"
                                onchange="updateMemberDropdown(this.value)" id="package-dropdown">
                                @foreach ($packages as $package)
                                    <option value="{{ $package->id }}" data-price="{{ $package->price }}"
                                        {{ $package->id == $customer->package_id ? 'selected' : '' }}>
                                        {{ $package->name }} - @moneyIDR($package->price)
                                    </option>
                                @endforeach
                            </select>
                            @if ($errors->has('package_id'))
                                <span class="text-danger">{{ $errors->first('package_id') }}</span>
                            @endif
                        @else
                            <select readonly name="package_id" class="form-control dynamic" data-dependent="details">
                                <option value="{{ $customer->package_id }}" selected>{{ $customer->package->name }} -
                                    @moneyIDR($customer->package->price)</option>
                            </select>
                            <span class="text-danger">{{ __('Server ') }} {{ $customer->mikrotik->name }}
                                {{ __('Offline or Disable!') }}</span>
                        @endif
                    </div>

                    <div class="col-md-3 text-end">
                        <label class="d-block">&nbsp;</label> {{-- Spasi untuk sejajarkan tombol --}}
                        <button type="submit" name="action" value="change_package"
                            class="btn btn-sm btn-success w-100">
                            <i class="fas fa-user-check"></i>&nbsp;{{ __('customer.button.submit') }}
                        </button>
                    </div>
                </div>
                <div class="row align-items-center">
                    <div class="col-md-12 mb-3">
                        <label for="member"><strong>{{ __('customer.label.member') }}</strong></label>
                        <select name="member_id" class="form-control dynamic" id="member-dropdown"
                            onchange="toggleDiscountField()">
                            <option value="non-member" {{ is_null($customer->member_id) ? 'selected' : '' }}>Non Member
                            </option>
                            @foreach ($members as $member)
                                <option value="{{ $member->id }}" data-discount="{{ $member->discount }}"
                                    @if ($member->discount > $customer->package->price) disabled @endif
                                    {{ $customer->member_id == $member->id ? 'selected' : '' }}>
                                    {{ $member->type }} - @moneyIDR($member->discount)
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <input type="hidden" name="member_id" id="hidden_member_id" value="{{ $customer->member_id ?? '' }}">

                <div class="row align-items-center" id="billing-options" style="display: none;">
                    <div class="col-md-12 mb-3">
                        <label><strong>{{ __('customer.label.billing-options') }}</strong></label><br>
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

                    {{-- Dropdown periode, hanya muncul jika pilih 1 Time Bill --}}
                    <div class="col-md-12 mb-3" id="periode-dropdown-wrapper" style="display: none;">
                        <label
                            for="periode_billing"><strong>{{ __('customer.label.select-billing-period') }}</strong></label>
                        <select name="periode_billing" class="form-control" id="periode_billing">
                            @foreach ($availablePeriodes as $periode)
                                <option value="{{ $periode }}"
                                    {{ old('periode_billing', $customer->billing_period) == $periode ? 'selected' : '' }}>
                                    {{ $periode }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="row align-items-center mb-3">
                    <div class="col-md-12 mb-3">
                        <label for="discount"><strong>{{ __('customer.label.discount') }}</strong></label>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="input-group">
                                    <span class="input-group-text">Rp</span>
                                    {!! Form::text('discount_nominal', null, [
                                        'placeholder' => __('customer.placeholder.discount_nominal'),
                                        'class' => 'form-control',
                                        'id' => 'discount_nominal',
                                        'oninput' => 'formatRupiah(this); calculateDiscount();',
                                    ]) !!}
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="input-group">
                                    {!! Form::text('discount', null, [
                                        'placeholder' => __('customer.placeholder.discount'),
                                        'class' => 'form-control',
                                        'id' => 'discount',
                                        'readonly' => true,
                                    ]) !!}
                                    <span class="input-group-text">%</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-12">
                        @if ($errors->has('discount'))
                            <span class="text-danger">{{ $errors->first('discount') }}</span>
                        @endif
                    </div>
                </div>
                <div class="row align-items-center">
                    <div class="col-md-12 mb-3">
                        <label for="email"><strong>{{ __('customer.label.email') }}</strong></label>
                        {!! Form::text('email', null, ['placeholder' => __('customer.placeholder.email'), 'class' => 'form-control']) !!}
                        @if ($errors->has('email'))
                            <span class="text-danger">{{ $errors->first('email') }}</span>
                        @endif
                    </div>
                </div>

                <div class="row align-items-center">
                    <div class="col-md-12 mb-3">
                        <label for="password"><strong>{{ __('customer.label.password') }}</strong></label>
                        {!! Form::password('password', [
                            'placeholder' => __('customer.placeholder.password'),
                            'class' => 'form-control',
                        ]) !!}
                        @if ($errors->has('password'))
                            <span class="text-danger">{{ $errors->first('password') }}</span>
                        @endif
                    </div>
                </div>

                <div class="row align-items-center">
                    <div class="col-md-12 mb-3">
                        <label
                            for="confirm-password"><strong>{{ __('customer.label.confirm-password') }}</strong></label>
                        {!! Form::password('password_confirmation', [
                            'placeholder' => __('customer.placeholder.confirm-password'),
                            'class' => 'form-control',
                        ]) !!}
                        @if ($errors->has('password_confirmation'))
                            <span class="text-danger">{{ $errors->first('password_confirmation') }}</span>
                        @endif
                    </div>
                </div>

                <div class="row align-items-center">
                    <div class="col-md-12 mb-3">
                        <label for="address"><strong>{{ __('customer.label.address') }}</strong></label>
                        {!! Form::text('address', null, [
                            'placeholder' => __('customer.placeholder.address'),
                            'class' => 'form-control',
                        ]) !!}
                        @if ($errors->has('address'))
                            <span class="text-danger">{{ $errors->first('address') }}</span>
                        @endif
                    </div>
                </div>
                <div class="row align-items-center">
                    <div class="col-md-12 mb-3">
                        <label for="contact"><strong>{{ __('customer.label.contact') }}</strong></label>

                        <input class="form-control contact" id="contact" name="contact" type="text"
                            placeholder="{{ trans('customer.placeholder.contact') }}"
                            value="{{ $customer_number }}">
                        @if ($errors->has('contact'))
                            <span class="text-danger">{{ $errors->first('contact') }}</span>
                        @endif
                    </div>
                </div>
                <div class="row align-items-center">
                    <div class="col-md-12 mb-3">
                        <label for="responsibility"><strong>{{ __('customer.label.responsibility') }}</strong></label>

                        <select name="user_id" class="form-control dynamic">
                            @if ($customer->user_id !== null)
                                <option value="{{ $customer->user_id }}" selected>
                                    {{ $customer->user->name }}
                                </option>
                            @endif
                            @foreach ($users as $user)
                                @if ($customer->user_id !== $user->id)
                                    <!-- Hindari duplikasi user -->
                                    <option value="{{ $user->id }}">{{ $user->name }}</option>
                                @endif
                            @endforeach
                        </select>

                        @if ($errors->has('user_id'))
                            <span class="text-danger">{{ $errors->first('user_id') }}</span>
                        @endif
                    </div>
                </div>

                <div class="row align-items-center mb-3">
                    <div class="col-md-6 text-end">
                        <label for="activation_date"
                            class="form-label"><strong>{{ __('customer.label.activation-date') }}</strong></label>
                    </div>
                    <div class="col-md-6">
                        @if ($serverDisconnect === 'false')
                            <input type="text" class="form-control" name="activation_date"
                                value="{{ $customer->activation_date ? \Carbon\Carbon::parse($customer->activation_date)->format('d F Y') : '' }}"
                                id="activation_date">
                            @if ($errors->has('activation_date'))
                                <span class="text-danger">{{ $errors->first('activation_date') }}</span>
                            @endif
                        @else
                            <input disabled type="text" value="{{ $customer->activation_date }}"
                                class="form-control">
                            <span class="text-danger">{{ __('Server ') }} {{ $customer->mikrotik->name }}
                                {{ __('Offline or Disable!') }}</span>
                        @endif
                    </div>
                </div>

                <div id="map"></div>

            </div>

            <div class="card-footer">
                <button type="submit" name="action" value="update_data" class="btn btn-sm btn-success">
                    <i class="fas fa-user-check"></i>&nbsp{{ __('customer.button.update') }}
                </button>
                <a class="btn btn-sm btn-primary" href="{{ route('customers.edit.router', $customer->slug) }}"><i
                        class="fas fa-user-edit"></i>&nbsp{{ __('customer.button.edit-router') }}</a>
                @if ($customer->internet_service == 'ppp')
                    <a class="btn btn-sm btn-primary" href="{{ route('customers.edit.ppp', $customer->slug) }}"><i
                            class="fas fa-user-edit"></i>&nbsp{{ __('customer.button.edit-ppp') }}</a>
                @elseif ($customer->internet_service == 'ip_static')
                    <a class="btn btn-sm btn-primary" href="{{ route('customers.edit.ppp', $customer->slug) }}"><i
                            class="fas fa-user-edit"></i>&nbsp{{ __('customer.button.edit-ip-static') }}</a>
                @endif
                <a class="btn btn-sm btn-secondary" href="{{ route('customers.index') }}"><i
                        class="fa fa-arrow-left"></i>&nbsp{{ __('customer.button.back') }}</a>
            </div>
            {!! Form::close() !!}
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

                togglePeriodeDropdown(); // Panggil ini juga saat billing option muncul
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

                // Bind event ke radio billing_option
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
