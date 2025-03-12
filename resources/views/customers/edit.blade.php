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
                <div class="row align-items-center">
                    <div class="col-md-12 mb-3">
                        <label for="paket"><strong>{{ __('customer.label.package') }}</strong></label>
                        @if ($serverDisconnect === 'false')
                            <select name="paket_id" class="form-control dynamic" data-dependent="details"
                                onchange="updateMemberDropdown(this.value)" id="paket-dropdown">
                                @foreach ($pakets as $paket)
                                    <option value="{{ $paket->id }}" data-price="{{ $paket->price }}"
                                        {{ $paket->id == $customer->paket_id ? 'selected' : '' }}>
                                        {{ $paket->name }} - @moneyIDR($paket->price)
                                    </option>
                                @endforeach
                            </select>
                            @if ($errors->has('paket_id'))
                                <span class="text-danger">{{ $errors->first('paket_id') }}</span>
                            @endif
                        @else
                            <select readonly name="paket_id" class="form-control dynamic" data-dependent="details">
                                <option value="{{ $customer->paket_id }}" selected>{{ $customer->paket->name }} -
                                    @moneyIDR($customer->paket->price)</option>
                            </select>
                            <span class="text-danger">{{ __('Server ') }} {{ $customer->mikrotik->name }}
                                {{ __('Offline or Disable!') }}</span>
                        @endif
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
                                    @if ($member->discount > $customer->paket->price) disabled @endif
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
                        <label><strong>Billing Options</strong></label><br>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="billing_option" id="monthly"
                                value="1"
                                {{ $customer->billing_option == 1 || $customer->billing_option == null || $customer->billing_option == 0 ? 'checked' : '' }}>
                            <label class="form-check-label" for="monthly">Monthly</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="billing_option" id="one-time"
                                value="2" {{ $customer->billing_option == 2 ? 'checked' : '' }}>
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
                            placeholder="{{ trans('customer.placeholder.contact') }}" value="{{ $customer_number }}">
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
                            <input type="datetime-local" class="form-control" name="activation_date"
                                value="{{ $customer->activation_date ? Carbon\Carbon::parse($customer->activation_date)->format('Y-m-d\TH:i') : '' }}">
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
                <button type="submit" class="btn btn-sm btn-success"><i
                        class="fas fa-user-check"></i>&nbsp{{ __('customer.button.update') }}</button>
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
            config = {
                enableTime: false,
                dateFormat: 'd F Y',
                //format:'DD/MM/YYYY HH:mm:ss'
            }
            flatpickr("input[type=datetime-local]", config);
        </script>
    @endsection
</x-layouts.cpanel.customers.app-layout>

<script>
    function formatRupiah(input) {
        let value = input.value.replace(/[^,\d]/g, ''); // Hapus karakter selain angka dan koma
        let paketDropdown = document.getElementById('paket-dropdown');
        let selectedPaket = paketDropdown.options[paketDropdown.selectedIndex];
        let paketPrice = parseFloat(selectedPaket.getAttribute('data-price')) || 0;

        // Ubah value ke number biar bisa dibandingkan
        let numericValue = parseFloat(value.replace(/\./g, ''));

        if (numericValue > paketPrice) {
            numericValue = paketPrice;
        }

        input.value = new Intl.NumberFormat('id-ID').format(numericValue);
    }

    function toggleDiscountField() {
        const memberDropdown = document.getElementById('member-dropdown');
        const discountNominalField = document.getElementById('discount_nominal');
        const billingOptions = document.getElementById('billing-options');
        const selectedMember = memberDropdown.options[memberDropdown.selectedIndex];
        const isNonMember = selectedMember.value === 'non-member';

        const hiddenMemberInput = document.getElementById('hidden_member_id');
        if (!hiddenMemberInput) {
            console.log('Element hidden_member_id tidak ditemukan');
            return;
        }

        if (isNonMember) {
            discountNominalField.removeAttribute('readonly');
            discountNominalField.removeAttribute('disabled');
            discountNominalField.value = ''; // Biarkan kosong biar user bisa input
            billingOptions.style.display = 'none'; // Sembunyiin billing options
            billingOptions.value = '0';

            // Set hidden input untuk menyimpan member_id sebagai null
            hiddenMemberInput.value = '';
        } else {
            discountNominalField.setAttribute('readonly', true);
            discountNominalField.setAttribute('disabled', true);
            const discount = parseFloat(selectedMember.getAttribute('data-discount')) || 0;
            discountNominalField.value = discount.toLocaleString('id-ID');
            billingOptions.style.display = 'block'; // Ini udah bener sekarang!

            // Set hidden input untuk menyimpan member_id yang dipilih
            hiddenMemberInput.value = selectedMember.value;
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        calculateNominal();
        toggleDiscountField()
        updateMemberDropdown(document.getElementById('paket-dropdown').value);
    });

    function calculateNominal() {
        const discountPercentInput = document.getElementById('discount').value;
        const discountPercent = parseFloat(discountPercentInput) || 0;

        const dropdown = document.getElementById('paket-dropdown');
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
        const paketDropdown = document.getElementById('paket-dropdown');
        const selectedPaket = paketDropdown.options[paketDropdown.selectedIndex];
        const paketPrice = parseFloat(selectedPaket.getAttribute('data-price'));

        const memberDropdown = document.getElementById('member-dropdown');
        const selectedMember = memberDropdown.options[memberDropdown.selectedIndex];
        const isNonMember = selectedMember.value === 'non-member';
        let memberDiscount = parseFloat(selectedMember.getAttribute('data-discount')) || 0;

        const discountNominalField = document.getElementById('discount_nominal');

        // Hanya set memberDiscount kalau bukan non-member
        if (!isNonMember) {
            discountNominalField.value = memberDiscount.toLocaleString('id-ID');
        }

        const discountField = document.getElementById('discount');
        const discountNominal = parseFloat(discountNominalField.value.replace(/\./g, '').replace(',', '.')) || 0;

        // Hitung persentase diskon
        const discountPercentage = paketPrice > 0 ? (discountNominal / paketPrice) * 100 : 0;
        discountField.value = discountPercentage.toFixed(2);
    }

    // Event listeners
    document.getElementById('paket-dropdown').addEventListener('change', () => {
        updateMemberDropdown();
    });

    document.getElementById('member-dropdown').addEventListener('change', () => {
        calculateDiscount();
    });

    function updateMemberDropdown(selectedPaketId) {
        const paketDropdown = document.getElementById('paket-dropdown');
        const selectedPaket = paketDropdown.options[paketDropdown.selectedIndex];
        const paketPrice = parseFloat(selectedPaket.getAttribute('data-price'));

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
            if (memberDiscount > paketPrice) {
                members[i].disabled = true;
            } else {
                members[i].disabled = false;
                if (memberDiscount > maxValidDiscount) {
                    maxValidDiscount = memberDiscount;
                    bestValidIndex = i;
                }
            }
        }

        if (paketPrice === 0) {
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
</script>
