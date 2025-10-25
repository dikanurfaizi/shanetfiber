<x-layouts.cpanel.customers.app-layout title="Create Customer">

    <div class="card-header py-3">
        <h5 class="m-0 font-weight-bold text-primary">{{ __('customer.header.create') }}</h5>
    </div>

    <div class="row">
        <div class="col-lg-12">
            {!! Form::open(['route' => 'customers.store', 'method' => 'POST']) !!}
            <div class="card-body col-lg-8">
                <div class="row align-items-center">
                    <div class="col-md-12 mb-3">
                        <label for="user_id"><strong>{{ __('customer.label.responsibility') }}</strong><span
                                class="text-danger">*</span></label>
                        <div class="input-group">
                            {!! Form::select('user_id', $users->pluck('name', 'id'), null, [
                                'placeholder' => __('customer.placeholder.responsibility'),
                                'class' => 'form-control',
                            ]) !!}
                            <div class="input-group-append">
                                <div class="input-group-text">
                                    <span class="fas fa-user"></span>
                                </div>
                            </div>
                        </div>
                        @if ($errors->has('user_id'))
                            <span class="text-danger">{{ $errors->first('user_id') }}</span>
                        @endif
                    </div>
                </div>

                <div class="row align-items-center">
                    <div class="col-md-12 mb-3">
                        <label for="server"><strong>{{ __('customer.label.server') }}</strong><span
                                class="text-danger">*</span></label>
                        {!! Form::select('mikrotik_id', $mikrotiks, null, [
                            'placeholder' => 'Select Server',
                            'class' => 'form-control',
                            'id' => 'server-dropdown',
                        ]) !!}
                        @if ($errors->has('mikrotik_id'))
                            <span class="text-danger">{{ $errors->first('mikrotik_id') }}</span>
                        @endif
                    </div>
                </div>
                <div class="row align-items-center">
                    <div class="col-md-12 mb-3">
                        {!! Form::select('package_id', $pppprofiles, null, [
                            'placeholder' => 'Select package',
                            'class' => 'form-control d-none', // awalnya disembunyikan
                            'id' => 'package-dropdown',
                            'onchange' => 'updateMemberDropdown(this.value); calculateDiscount();',
                        ]) !!}

                        @if ($errors->has('package_id'))
                            <span class="text-danger">{{ $errors->first('package_id') }}</span>
                        @endif
                    </div>
                </div>

                <div class="row align-items-center">
                    <div class="col-md-12 mb-3">
                        <label for="internet_service"><strong>{{ __('customer.label.internet_service') }}</strong><span
                                class="text-danger">*</span></label>
                        <select name="internet_service" id="service-dropdown" class="form-control">
                            <option value="">Select Service</option>
                            <option value="ppp">PPP</option>
                            <option value="ip_static">IP Static</option>
                        </select>
                        @if ($errors->has('internet_service'))
                            <span class="text-danger">{{ $errors->first('internet_service') }}</span>
                        @endif
                    </div>
                </div>

                <div id="input-ppp" class="row align-items-center d-none">
                    <div class="col-md-12 mb-3">

                        {!! Form::select('ppp_type_id', $pppservices, null, [
                            'placeholder' => 'Select PPP Type',
                            'class' => 'form-control',
                            'id' => 'ppp_types_id',
                        ]) !!}
                        @if ($errors->has('ppp_type_id'))
                            <span class="text-danger">{{ $errors->first('ppp_type_id') }}</span>
                        @endif
                    </div>
                </div>


                <div id="input-ip-static" class="row align-items-center d-none">
                    <div class="col-md-12 mb-3">
                        <label for="interface"><strong>{{ __('customer.label.interface') }}</strong><span
                                class="text-danger">*</span></label>
                        <select name="interface" id="interface-dropdown" class="form-control">
                            <option value="">Select Interface</option>
                        </select>
                        @if ($errors->has('interface'))
                            <span class="text-danger">{{ $errors->first('interface') }}</span>
                        @endif
                    </div>
                    <div class="col-md-12 mb-3">
                        <div class="input-group">
                            {!! Form::text('ip_static', null, [
                                'placeholder' => __('customer.placeholder.ip_static'),
                                'class' => 'form-control',
                            ]) !!}
                            <div class="input-group-append">
                                <div class="input-group-text">
                                    <span class="far fa-keyboard"></span>
                                </div>
                            </div>
                        </div>
                        @if ($errors->has('ip_static'))
                            <span class="text-danger">{{ $errors->first('ip_static') }}</span>
                        @endif
                    </div>

                    <div class="col-md-12 mb-3">

                        <div class="input-group">
                            {!! Form::text('mac_address', null, [
                                'placeholder' => __('customer.placeholder.mac_address'),
                                'class' => 'form-control',
                            ]) !!}
                            <div class="input-group-append">
                                <div class="input-group-text">
                                    <span class="far fa-keyboard"></span>
                                </div>
                            </div>
                        </div>
                        @if ($errors->has('mac_address'))
                            <span class="text-danger">{{ $errors->first('mac_address') }}</span>
                        @endif
                    </div>
                </div>


                <div class="row align-items-center">
                    <div class="col-md-12 mb-3">
                        <label for="name"><strong>{{ __('customer.label.name') }}</strong><span
                                class="text-danger">*</span></label>
                        <div class="input-group ">
                            {!! Form::text('name', null, ['placeholder' => __('customer.placeholder.name'), 'class' => 'form-control']) !!}
                            <div class="input-group-append">
                                <div class="input-group-text">
                                    <span class="fas fa-user"></span>
                                </div>
                            </div>
                        </div>
                        @if ($errors->has('name'))
                            <span class="text-danger">{{ $errors->first('name') }}</span>
                        @endif
                    </div>
                </div>
                <div class="row align-items-center">
                    <div class="col-md-12 mb-3">
                        <label for="contact"><strong>{{ __('customer.label.contact') }}</strong></label>
                        <div class="input-group">
                            {!! Form::text('contact', null, [
                                'placeholder' => __('customer.placeholder.contact'),
                                'class' => 'form-control',
                            ]) !!}
                            <div class="input-group-append">
                                <div class="input-group-text">
                                    <span class="far fa-address-book"></span>
                                </div>
                            </div>
                        </div>
                        @if ($errors->has('contact'))
                            <span class="text-danger">{{ $errors->first('contact') }}</span>
                        @endif
                    </div>
                </div>
                <div class="row align-items-center">
                    <div class="col-md-12 mb-3">
                        <label for="email"><strong>{{ __('customer.label.email') }}</strong><span
                                class="text-danger">*</span></label>
                        <div class="input-group">
                            {!! Form::text('email', null, ['placeholder' => __('customer.placeholder.email'), 'class' => 'form-control']) !!}
                            <div class="input-group-append">
                                <div class="input-group-text">
                                    <span class="fas fa-envelope"></span>
                                </div>
                            </div>
                        </div>
                        @if ($errors->has('email'))
                            <span class="text-danger">{{ $errors->first('email') }}</span>
                        @endif
                    </div>
                </div>

                <div class="row align-items-center">
                    <div class="col-md-12 mb-3">
                        <label for="password"><strong>{{ __('customer.label.password') }}</strong></label>
                        <div class="input-group">
                            {!! Form::password('password', [
                                'placeholder' => __('customer.placeholder.password'),
                                'class' => 'form-control',
                            ]) !!}
                            <div class="input-group-append">
                                <div class="input-group-text">
                                    <span class="fas fa-lock"></span>
                                </div>
                            </div>
                        </div>
                        @if ($errors->has('password'))
                            <span class="text-danger">{{ $errors->first('password') }}</span>
                        @endif
                    </div>
                </div>

                <div class="row align-items-center">
                    <div class="col-md-12 mb-3">
                        <label
                            for="password_confirmation"><strong>{{ __('customer.label.confirm-password') }}</strong></label>
                        <div class="input-group">
                            {!! Form::password('password_confirmation', [
                                'placeholder' => __('customer.placeholder.confirm-password'),
                                'class' => 'form-control',
                            ]) !!}
                            <div class="input-group-append">
                                <div class="input-group-text">
                                    <span class="fas fa-lock"></span>
                                </div>
                            </div>
                        </div>
                        @if ($errors->has('password_confirmation'))
                            <span class="text-danger">{{ $errors->first('password_confirmation') }}</span>
                        @endif
                    </div>
                </div>
                <div class="row align-items-center">
                    <div class="col-md-12 mb-3">
                        <label for="address"><strong>{{ __('customer.label.address') }}</strong></label>
                        <div class="input-group">
                            {!! Form::text('address', null, [
                                'placeholder' => __('customer.placeholder.address'),
                                'class' => 'form-control',
                            ]) !!}
                            <div class="input-group-append">
                                <div class="input-group-text">
                                    <span class="fas fa-home"></span>
                                </div>
                            </div>
                        </div>
                        @if ($errors->has('address'))
                            <span class="text-danger">{{ $errors->first('address') }}</span>
                        @endif
                    </div>
                </div>
                <div class="row align-items-center">
                    <div class="col-md-12 mb-3">
                        <label for="member"><strong>{{ __('customer.label.member') }}</strong></label>
                        <select name="member_id" class="form-control dynamic" id="member-dropdown"
                            onchange="toggleDiscountField()">
                            <option value="non-member">Non Member</option>
                            @foreach ($members as $member)
                                <option value="{{ $member->id }}" data-discount="{{ $member->discount }}">
                                    {{ $member->type }} - @moneyIDR($member->discount)
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <input type="hidden" name="member_id" id="hidden_member_id"
                    value="{{ $customer->member_id ?? '' }}">

                <div class="row align-items-center" id="billing-options" style="display: none;">
                    <div class="col-md-12 mb-3">
                        <label><strong>Billing Options</strong></label><br>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="billing_option" id="monthly"
                                value="1">
                            <label class="form-check-label" for="monthly">Monthly</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="billing_option" id="one-time"
                                value="2">
                            <label class="form-check-label" for="one-time">1 Time Bill</label>
                        </div>
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
            </div>


            <div class="card-footer">
                <button type="submit" class="btn btn-sm btn-success"><i class="fas fa-user-plus"></i> &nbsp
                    {{ __('customer.button.save') }}</button>
                <a class="btn btn-sm btn-secondary" href="{{ route('customers.index') }}"><i
                        class="fa fa-arrow-left"></i> &nbsp {{ __('customer.button.back') }}</a>
            </div>
            {!! Form::close() !!}
        </div>
    </div>

    @section('custom_scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const packageDropdown = document.getElementById('package-dropdown');
                const memberDropdown = document.getElementById('member-dropdown');
                const discountInput = document.getElementById('discount_nominal');

                packageDropdown.addEventListener('change', () => {
                    updateMemberDropdown();
                    calculateDiscount();
                });

                memberDropdown.addEventListener('change', () => {
                    toggleDiscountField();
                    calculateDiscount();
                });

                discountInput.addEventListener('input', () => {
                    formatRupiah(discountInput);
                    calculateDiscount();
                });

                // Trigger awal saat load
                updateMemberDropdown();
                toggleDiscountField();
                calculateDiscount();

                $('#server-dropdown').on('change', function() {
                    const idServer = this.value;

                    if (idServer) {
                        $('#package-dropdown').removeClass('d-none');
                    } else {
                        $('#package-dropdown').addClass('d-none');
                    }
                });

                $('#service-dropdown').on('change', function() {
                    var idService = this.value;
                    if (idService == 'ppp') {
                        $("#input-ppp").removeClass('d-none');
                        $("#input-ip-static").addClass('d-none');
                    } else if (idService == 'ip_static') {
                        $("#input-ppp").addClass('d-none');
                        $("#input-ip-static").removeClass('d-none');

                        $("#interface-dropdown").html('');
                        const url_interface = "{{ route('fetch.interface') }}";
                        $.ajax({

                            url: url_interface,
                            type: "POST",
                            data: {
                                mikrotik_id: idServer,
                                _token: '{{ csrf_token() }}'
                            },
                            dataType: 'json',
                            success: function(result_server) {
                                $('#interface-dropdown').html(
                                    '<option value="">Select Interface</option>');
                                $.each(result_server, function(key, value) {
                                    $("#interface-dropdown").append('<option value="' +
                                        value.name + '">' + value.name + '</option>');
                                });
                            }

                        });

                    } else {
                        $("#input-ppp").addClass('d-none');
                        $("#input-ip-static").addClass('d-none');
                    }
                });
            });

            function getPackagePrice() {
                const packageDropdown = document.getElementById('package-dropdown');
                const selectedOption = packageDropdown.options[packageDropdown.selectedIndex];
                return selectedOption && selectedOption.value ? getCleanPrice(selectedOption.text) : 0;
            }

            function getCleanPrice(text) {
                const match = text.match(/Rp\.?\s?([\d.,]+)/i);
                return match ? Number(match[1].replace(/[.,]/g, '')) : 0;
            }

            function formatRupiah(input) {
                let value = input.value.replace(/[^,\d]/g, '');
                let packagePrice = getPackagePrice();

                if (packagePrice === 0) {
                    input.value = '0';
                    return;
                }

                let numericValue = parseFloat(value.replace(/\./g, ''));
                if (numericValue > packagePrice) {
                    numericValue = packagePrice;
                }

                input.value = new Intl.NumberFormat('id-ID').format(numericValue);
            }

            function toggleDiscountField() {
                const memberDropdown = document.getElementById('member-dropdown');
                const discountNominalField = document.getElementById('discount_nominal');
                const billingOptions = document.getElementById('billing-options');
                const monthlyOption = document.getElementById('monthly');
                const oneTimeOption = document.getElementById('one-time');
                const hiddenMemberInput = document.getElementById('hidden_member_id');

                const selectedMember = memberDropdown.options[memberDropdown.selectedIndex];
                const isNonMember = selectedMember.value === 'non-member';

                if (!hiddenMemberInput) return;

                if (isNonMember) {
                    discountNominalField.removeAttribute('readonly');
                    discountNominalField.removeAttribute('disabled');
                    discountNominalField.value = '';
                    billingOptions.style.display = 'none';
                    monthlyOption.checked = false;
                    oneTimeOption.checked = false;
                    hiddenMemberInput.value = '';
                } else {
                    discountNominalField.setAttribute('readonly', true);
                    discountNominalField.setAttribute('disabled', true);
                    const discount = parseFloat(selectedMember.getAttribute('data-discount')) || 0;
                    discountNominalField.value = discount.toLocaleString('id-ID');
                    billingOptions.style.display = 'block';
                    hiddenMemberInput.value = selectedMember.value;
                }
            }

            function calculateDiscount() {
                let packagePrice = getPackagePrice();
                const memberDropdown = document.getElementById('member-dropdown');
                const selectedMember = memberDropdown.options[memberDropdown.selectedIndex];
                const isNonMember = selectedMember.value === 'non-member';
                const discountNominalField = document.getElementById('discount_nominal');
                const discountField = document.getElementById('discount');

                if (!isNonMember) {
                    const memberDiscount = parseFloat(selectedMember.getAttribute('data-discount')) || 0;
                    discountNominalField.value = memberDiscount.toLocaleString('id-ID');
                }

                const discountNominal = parseFloat(discountNominalField.value.replace(/\./g, '').replace(',', '.')) || 0;
                const discountPercentage = packagePrice > 0 ? (discountNominal / packagePrice) * 100 : 0;
                discountField.value = discountPercentage.toFixed(2);
            }

            function updateMemberDropdown() {
                const packagePrice = getPackagePrice();
                const memberDropdown = document.getElementById('member-dropdown');
                const members = memberDropdown.options;

                const discountField = document.getElementById('discount');
                const discountNominalField = document.getElementById('discount_nominal');

                // Selalu pastikan non-member (index 0) enabled
                members[0].disabled = false;

                for (let i = 1; i < members.length; i++) {
                    const discount = +members[i].getAttribute('data-discount') || 0;
                    members[i].disabled = discount > packagePrice;
                }

                // Reset ke non-member tapi tidak mengganti selectedIndex jika user sudah pilih
                // Jadi, kita **tidak** mengubah memberDropdown.selectedIndex di sini

                // Reset input diskon
                discountNominalField.value = 0;
                discountNominalField.readOnly = true;
                discountField.value = 0;

                toggleDiscountField();
                calculateDiscount();
            }
        </script>
    @endsection
</x-layouts.cpanel.customers.app-layout>
