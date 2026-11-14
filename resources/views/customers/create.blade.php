<x-layouts.cpanel.customers.app-layout title="Create Customer">

    <div class="card-header py-3">
        <h5 class="m-0 fw-bold text-primary">{{ __('customer.header.create') }}</h5>
    </div>

    <div class="row">
        <div class="col-lg-12">
            <form action="{{ route('customers.store') }}" method="POST">
                @csrf
            <div class="card-body col-lg-8">
                <div class="row align-items-center">
                    <div class="col-md-12 mb-3">
                        <label for="user_id"><strong>{{ __('customer.label.responsibility') }}</strong><span
                                class="text-danger">*</span></label>
                        <div class="input-group">
                            <select name="user_id" class="form-control">
                                <option value="">{{ __('customer.placeholder.responsibility') }}</option>
                                @foreach ($users->pluck('name', 'id') as $id => $name)
                                    <option value="{{ $id }}" {{ old('user_id') == $id ? 'selected' : '' }}>{{ $name }}</option>
                                @endforeach
                            </select>
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
                        <select name="mikrotik_id" class="form-control" id="server-dropdown">
                            <option value="">Select Server</option>
                            @foreach ($mikrotiks as $id => $name)
                                <option value="{{ $id }}" {{ old('mikrotik_id') == $id ? 'selected' : '' }}>{{ $name }}</option>
                            @endforeach
                        </select>
                        @if ($errors->has('mikrotik_id'))
                            <span class="text-danger">{{ $errors->first('mikrotik_id') }}</span>
                        @endif
                    </div>
                </div>
                <div class="row align-items-center">
                    <div class="col-md-12 mb-3">
                        <select name="package_id" class="form-control d-none" id="package-dropdown" onchange="updateMemberDropdown(this.value); calculateDiscount();">
                            <option value="">Select package</option>
                            @foreach ($pppprofiles as $id => $name)
                                <option value="{{ $id }}" {{ old('package_id') == $id ? 'selected' : '' }}>{{ $name }}</option>
                            @endforeach
                        </select>

                        @if ($errors->has('package_id'))
                            <span class="text-danger">{{ $errors->first('package_id') }}</span>
                        @endif
                    </div>
                </div>
                <div class="row align-items-center d-none" id="package-duration-wrapper">
                    <div class="col-md-12 mb-3">
                        <label><strong>Package Duration</strong></label>
                        <select name="package_duration_id" id="package-duration" class="form-control">
                            <option value="">-- Select Duration --</option>
                        </select>
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
                        <select name="ppp_type_id" class="form-control" id="ppp_types_id">
                            <option value="">Select PPP Type</option>
                            @foreach ($pppservices as $id => $name)
                                <option value="{{ $id }}" {{ old('ppp_type_id') == $id ? 'selected' : '' }}>{{ $name }}</option>
                            @endforeach
                        </select>
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
                            <input type="text" name="ip_static" class="form-control" placeholder="{{ __('customer.placeholder.ip_static') }}" value="{{ old('ip_static') }}">
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
                            <input type="text" name="mac_address" class="form-control" placeholder="{{ __('customer.placeholder.mac_address') }}" value="{{ old('mac_address') }}">
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
                            <input type="text" name="name" class="form-control" placeholder="{{ __('customer.placeholder.name') }}" value="{{ old('name') }}">
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
                            <input type="text" name="contact" class="form-control" placeholder="{{ __('customer.placeholder.contact') }}" value="{{ old('contact') }}">
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
                            <input type="text" name="email" class="form-control" placeholder="{{ __('customer.placeholder.email') }}" value="{{ old('email') }}">
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
                            <input type="password" name="password" class="form-control" placeholder="{{ __('customer.placeholder.password') }}">
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
                            <input type="password" name="password_confirmation" class="form-control" placeholder="{{ __('customer.placeholder.confirm-password') }}">
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
                            <input type="text" name="address" class="form-control" placeholder="{{ __('customer.placeholder.address') }}" value="{{ old('address') }}">
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
                                <option value="{{ $member->id }}">
                                    {{ $member->name }} - @moneyIDR($member->discount_value)
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

                <div class="row align-items-center" id="periode-wrapper" style="display:none;">
                    <div class="col-md-12 mb-3">
                        <label><strong>Select Billing Period</strong></label>
                        <select name="member_billing_periode" id="periode-select" class="form-control">
                            <option value="">-- Select Period --</option>
                            @foreach ($availablePeriodes as $periode)
                                <option value="{{ $periode['name'] }}">{{ $periode['name'] }}</option>
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
                                    <input type="text" name="discount_nominal" id="discount_nominal" class="form-control" placeholder="{{ __('customer.placeholder.discount_nominal') }}" value="{{ old('discount_nominal') }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="input-group">
                                    <input type="text" name="discount" id="discount" class="form-control" placeholder="{{ __('customer.placeholder.discount') }}" value="{{ old('discount') }}" readonly>
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
            </form>
        </div>
    </div>

    @section('custom_scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {

                // =========================
                // ELEMENT SELECTION (single source)
                // =========================
                const packageDropdown = document.getElementById('package-dropdown');
                const memberDropdown = document.getElementById('member-dropdown');
                const discountPercent = document.getElementById('discount');
                const monthlyOption = document.getElementById('monthly');
                const oneTimeOption = document.getElementById('one-time');
                const periodeWrapper = document.getElementById('periode-wrapper');
                const billingOptions = document.getElementById('billing-options');
                const hiddenMemberInput = document.getElementById('hidden_member_id');
                const durationWrapper = document.getElementById('package-duration-wrapper');
                const durationSelect = document.getElementById('package-duration');

                // =========================
                // MAIN EVENT HANDLERS
                // =========================

                packageDropdown.addEventListener('change', function() {
                    updateMemberDropdown();
                    toggleDiscountField();
                    calculateDiscount();

                    const packageId = this.value;

                    if (!packageId) {
                        durationWrapper.classList.add('d-none');
                        durationSelect.innerHTML = '<option value="">-- Select Duration --</option>';
                        return;
                    }

                    fetch("{{ route('package.durations') }}", {
                            method: "POST",
                            headers: {
                                "Content-Type": "application/json",
                                "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content
                            },
                            body: JSON.stringify({
                                package_id: packageId
                            })
                        })
                        .then(res => res.json())
                        .then(data => {

                            let items = [];

                            // Jika response adalah object dengan key "duration"
                            if (Array.isArray(data.duration)) {
                                items = data.duration;

                                // Jika response adalah array langsung
                            } else if (Array.isArray(data)) {
                                items = data;
                            }

                            durationSelect.innerHTML = '<option value="">-- Select Duration --</option>';

                            items.forEach(item => {
                                durationSelect.innerHTML += `
        <option value="${item.id}" data-label="${item.duration?.name_en}">
            ${item.duration?.name_en}
        </option>
    `;
                            });


                            durationWrapper.classList.remove('d-none');
                        });
                });

                memberDropdown.addEventListener('change', () => {
                    toggleDiscountField();
                    calculateDiscount();
                });

                if (monthlyOption) {
                    monthlyOption.addEventListener('change', function() {
                        periodeWrapper.style.display = 'none';
                    });
                }

                if (oneTimeOption) {
                    oneTimeOption.addEventListener('change', function() {
                        periodeWrapper.style.display = 'block';
                    });
                }

                // =========================
                // SERVICE TYPE (PPP / STATIC)
                // =========================

                $('#service-dropdown').on('change', function() {
                    const idService = this.value;
                    const idServer = $('#server-dropdown').val();

                    if (idService === 'ppp') {
                        $("#input-ppp").removeClass('d-none');
                        $("#input-ip-static").addClass('d-none');
                        return;
                    }

                    if (idService === 'ip_static') {
                        $("#input-ppp").addClass('d-none');
                        $("#input-ip-static").removeClass('d-none');

                        $("#interface-dropdown").html('');
                        $.ajax({
                            url: "{{ route('fetch.interface') }}",
                            type: "POST",
                            data: {
                                mikrotik_id: idServer,
                                _token: '{{ csrf_token() }}'
                            },
                            dataType: 'json',
                            success: function(result) {
                                $('#interface-dropdown').html(
                                    '<option value="">Select Interface</option>');
                                $.each(result, function(_, val) {
                                    $("#interface-dropdown").append(
                                        `<option value="${val.name}">${val.name}</option>`
                                    );
                                });
                            }
                        });
                        return;
                    }

                    $("#input-ppp").addClass('d-none');
                    $("#input-ip-static").addClass('d-none');
                });

                // =========================
                // SERVER CHANGE → Show packages
                // =========================
                $('#server-dropdown').on('change', function() {
                    $('#package-dropdown').toggleClass('d-none', !this.value);
                });

                // =========================
                // INITIALIZE ON LOAD
                // =========================
                updateMemberDropdown();
                toggleDiscountField();
                calculateDiscount();

            });


            // =======================================================================
            // FUNCTIONS — Semua fungsi eksisting dipertahankan (tidak ada yang hilang)
            // =======================================================================

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
                const discountNominalField = document.getElementById('discount_nominal');
                if (!discountNominalField) return; // ⛔ FIX ERROR

                const memberDropdown = document.getElementById('member-dropdown');
                const billingOptions = document.getElementById('billing-options');
                const monthlyOption = document.getElementById('monthly');
                const oneTimeOption = document.getElementById('one-time');
                const hiddenMemberInput = document.getElementById('hidden_member_id');

                const selectedMember = memberDropdown.options[memberDropdown.selectedIndex];
                const isNonMember = selectedMember.value === 'non-member';

                if (!hiddenMemberInput) return;

                if (isNonMember) {

                    discountNominalField.value = '0';
                    discountNominalField.setAttribute('readonly', true);
                    discountNominalField.setAttribute('disabled', true);

                    billingOptions.style.display = 'none';
                    monthlyOption.checked = false;
                    oneTimeOption.checked = false;
                    hiddenMemberInput.value = '';

                } else {
                    discountNominalField.setAttribute('readonly', true);
                    discountNominalField.setAttribute('disabled', true);

                    billingOptions.style.display = 'block';
                    hiddenMemberInput.value = selectedMember.value;

                    calculateDiscount();
                }
            }

            function calculateDiscount() {
                if (!document.getElementById('discount_nominal')) return; // ⛔ FIX
                fetchDiscountFromServer();
            }

            function updateMemberDropdown() {
                toggleDiscountField();
                calculateDiscount();
            }

            async function fetchDiscountFromServer() {
                const packageId = document.getElementById('package-dropdown').value;
                const memberId = document.getElementById('member-dropdown').value;

                if (!packageId) return;

                const url = "{{ route('package.discount') }}";

                try {
                    const response = await fetch(url, {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/json",
                            "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify({
                            package_id: packageId,
                            member_id: memberId
                        })
                    });

                    const data = await response.json();
                    updateDiscountFields(data);

                } catch (err) {
                    console.error("DISCOUNT API ERROR:", err);
                }
            }

            function updateDiscountFields(data) {
                const discountNominal = document.getElementById('discount_nominal');
                const discountPercent = document.getElementById('discount');

                if (!discountNominal || !discountPercent) return; // ⛔ FIX

                discountNominal.value = new Intl.NumberFormat('id-ID')
                    .format(data.effective_discount);

                discountPercent.value = data.effective_percent.toFixed(2);
            }
        </script>
    @endsection
</x-layouts.cpanel.customers.app-layout>
