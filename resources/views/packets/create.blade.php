<x-layouts.app-layout title="Create Paket">
    <div class="card-header py-3">
        <h5 class="m-0 font-weight-bold text-primary"> {{ __('paket.header.create') }}</h5>
    </div>

    <div class="row">
        <div class="col-lg-12">
            {!! Form::open(['route' => 'packets.store', 'method' => 'POST']) !!}
            <div class="card-body col-lg-8">

                <div class="row align-items-center">
                    <div class="col-md-12 mb-3">
                        <label for="name"><strong>{{ __('paket.label.name') }}</strong></label>
                        {!! Form::text('name', null, ['placeholder' => __('paket.placeholder.name'), 'class' => 'form-control']) !!}
                        @if ($errors->has('name'))
                            <span class="text-danger">{{ $errors->first('name') }}</span>
                        @endif
                    </div>
                </div>

                <div class="row align-items-center">
                    <div class="col-md-12 mb-3">
                        <label for="profile"><strong>{{ __('paket.label.profile') }}</strong></label>
                        <select id="profile-paket-dropdown" name="profile" class="form-control">
                            @foreach ($ppp_profiles as $pppprofile)
                                <option value="{{ $pppprofile->profile_name ?? 'no data' }}">
                                    {{ $pppprofile->profile_name ?? 'no data' }}
                                </option>
                            @endforeach
                        </select>
                        @if ($errors->has('profile'))
                            <span class="text-danger">{{ $errors->first('profile') }}</span>
                        @endif
                    </div>
                </div>

                <div class="row align-items-center">
                    <div class="col-md-6 mb-3">
                        <label for="speed_up_to"><strong>{{ __('paket.label.speed_up_to') }}</strong></label>
                        {!! Form::number('speed_up_to', null, [
                            'placeholder' => 'Enter speed number',
                            'class' => 'form-control',
                            'min' => 0,
                        ]) !!}
                        @if ($errors->has('speed_up_to'))
                            <span class="text-danger">{{ $errors->first('speed_up_to') }}</span>
                        @endif
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="speed_unit"><strong>{{ __('paket.label.speed_unit') }}</strong></label>
                        <select name="speed_unit" class="form-control">
                            <option value="Mbps">Mbps</option>
                            <option value="Gbps">Gbps</option>
                        </select>
                        @if ($errors->has('speed_unit'))
                            <span class="text-danger">{{ $errors->first('speed_unit') }}</span>
                        @endif
                    </div>
                </div>

                <div class="row align-items-center">
                    <div class="col-md-6 mb-3">
                        <label
                            for="recommended_device_min"><strong>{{ __('paket.label.recommended_device_min') }}</strong></label>
                        {!! Form::number('recommended_device_min', null, [
                            'placeholder' => 'Enter minimum device number',
                            'class' => 'form-control',
                            'min' => 0,
                        ]) !!}
                        @if ($errors->has('recommended_device_min'))
                            <span class="text-danger">{{ $errors->first('recommended_device_min') }}</span>
                        @endif
                    </div>
                    <div class="col-md-6 mb-3">
                        <label
                            for="recommended_device_max"><strong>{{ __('paket.label.recommended_device_max') }}</strong></label>
                        {!! Form::number('recommended_device_max', null, [
                            'placeholder' => 'Enter maximum device number',
                            'class' => 'form-control',
                            'min' => 0,
                        ]) !!}
                        @if ($errors->has('recommended_device_max'))
                            <span class="text-danger">{{ $errors->first('recommended_device_max') }}</span>
                        @endif
                    </div>
                </div>

                <div class="row align-items-center">
                    <div class="col-md-12 mb-3">
                        <label for="quota"><strong>{{ __('paket.label.quota') }}</strong></label>
                        {!! Form::text('quota', null, [
                            'placeholder' => 'Enter quota',
                            'class' => 'form-control',
                            'pattern' => '[a-zA-Z0-9\s]+',
                            'title' => 'No special characters allowed',
                        ]) !!}
                        @if ($errors->has('quota'))
                            <span class="text-danger">{{ $errors->first('quota') }}</span>
                        @endif
                    </div>
                </div>

                <div class="row align-items-center">
                    <div class="col-md-6 mb-3">
                        <label for="include_modem"><strong>{{ __('paket.label.incl_modem') }}</strong></label><br>
                        <div class="d-flex align-items-center">
                            <label class="switch me-2">
                                {!! Form::checkbox('include_modem', 1, false, [
                                    'class' => 'form-check-input toggle-switch',
                                    'id' => 'include_modem_toggle',
                                ]) !!}
                                <span class="slider round"></span>
                            </label>
                            <span id="include_modem_label" class="toggle-label">No</span>
                        </div>
                        @if ($errors->has('include_modem'))
                            <span class="text-danger">{{ $errors->first('include_modem') }}</span>
                        @endif
                    </div>
                    <div class="col-md-6 mb-3">
                        <label
                            for="is_installation_free"><strong>{{ __('paket.label.installation_free') }}</strong></label><br>
                        <div class="d-flex align-items-center">
                            <label class="switch me-2">
                                {!! Form::checkbox('is_installation_free', 1, false, [
                                    'class' => 'form-check-input toggle-switch',
                                    'id' => 'is_installation_free_toggle',
                                ]) !!}
                                <span class="slider round"></span>
                            </label>
                            <span id="is_installation_free_label" class="toggle-label">No</span>
                        </div>
                        @if ($errors->has('is_installation_free'))
                            <span class="text-danger">{{ $errors->first('is_installation_free') }}</span>
                        @endif
                    </div>
                </div>

                <div class="row align-items-center">
                    <div class="col-md-12 mb-3">
                        <label for="installation_fee"><strong>{{ __('paket.label.installation_fee') }}</strong></label>
                        <div class="input-group">
                            <span class="input-group-text">Rp</span>
                            {!! Form::text('installation_fee_formatted', null, [
                                'placeholder' => 'Enter installation fee',
                                'class' => 'form-control currency-input',
                                'id' => 'installation_fee_formatted',
                            ]) !!}
                            {!! Form::hidden('installation_fee', null, ['id' => 'installation_fee']) !!}
                        </div>
                        @if ($errors->has('installation_fee'))
                            <span class="text-danger">{{ $errors->first('installation_fee') }}</span>
                        @endif
                    </div>
                </div>

                <div class="row align-items-center">
                    <div class="col-md-12 mb-3">
                        <label for="price"><strong>{{ __('paket.label.price') }}</strong></label>
                        <div class="input-group">
                            <span class="input-group-text">Rp</span>
                            {!! Form::text('price_formatted', null, [
                                'placeholder' => __('paket.placeholder.price'),
                                'class' => 'form-control currency-input',
                                'id' => 'price_formatted',
                            ]) !!}
                            {!! Form::hidden('price', null, ['id' => 'price']) !!}
                        </div>
                        @if ($errors->has('price'))
                            <span class="text-danger">{{ $errors->first('price') }}</span>
                        @endif
                    </div>
                </div>

                <div class="row align-items-center">
                    <div class="col-md-6 mb-3">
                        <label
                            for="package_category_id"><strong>{{ __('paket.label.package_category') }}</strong></label>
                        <select name="paket_category_id" class="form-control">
                            @foreach ($category as $cat)
                                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                            @endforeach
                        </select>
                        @if ($errors->has('paket_category_id'))
                            <span class="text-danger">{{ $errors->first('paket_category_id') }}</span>
                        @endif
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="billing_cycle_id"><strong>{{ __('paket.label.billing_cycle') }}</strong></label>
                        <select name="billing_cycle_id" class="form-control">
                            @foreach ($billingCycle as $cycle)
                                <option value="{{ $cycle->id }}">{{ $cycle->code }}</option>
                            @endforeach
                        </select>
                        @if ($errors->has('billing_cycle_id'))
                            <span class="text-danger">{{ $errors->first('billing_cycle_id') }}</span>
                        @endif
                    </div>
                </div>

                <div class="row align-items-center">
                    <div class="col-md-12 mb-3">
                        <label for="description"><strong>{{ __('paket.label.description') }}</strong></label>
                        {!! Form::text('description', null, [
                            'placeholder' => __('paket.placeholder.description'),
                            'class' => 'form-control',
                        ]) !!}
                        @if ($errors->has('description'))
                            <span class="text-danger">{{ $errors->first('description') }}</span>
                        @endif
                    </div>
                </div>
            </div>
            <div class="card-footer">
                <button type="submit" class="btn btn-success"><i class="fa fa-save"></i> &nbsp
                    {{ __('paket.button.save') }}</button>
                <a class="btn btn-secondary" href="{{ route('packets.index') }}"><i class="fa fa-arrow-left"></i> &nbsp
                    {{ __('paket.button.cancel') }}</a>
            </div>
            {!! Form::close() !!}
        </div>
    </div>

    @section('custom_styles')
        <style>
            .toggle-label {
                font-weight: bold;
                min-width: 30px;
                display: inline-block;
                margin-left: 10px;
            }

            .switch {
                position: relative;
                display: inline-block;
                width: 60px;
                height: 34px;
            }

            .switch input {
                opacity: 0;
                width: 0;
                height: 0;
            }

            .slider {
                position: absolute;
                cursor: pointer;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background-color: #ccc;
                transition: .4s;
            }

            .slider:before {
                position: absolute;
                content: "";
                height: 26px;
                width: 26px;
                left: 4px;
                bottom: 4px;
                background-color: white;
                transition: .4s;
            }

            input:checked+.slider {
                background-color: #2196F3;
            }

            input:focus+.slider {
                box-shadow: 0 0 1px #2196F3;
            }

            input:checked+.slider:before {
                transform: translateX(26px);
            }

            .slider.round {
                border-radius: 34px;
            }

            .slider.round:before {
                border-radius: 50%;
            }
        </style>
    @endsection

    @section('custom_scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // Toggle switches
                function updateToggleLabel(checkboxId, labelId) {
                    const checkbox = document.getElementById(checkboxId);
                    const label = document.getElementById(labelId);

                    checkbox.addEventListener('change', function() {
                        label.textContent = this.checked ? 'Yes' : 'No';
                    });
                }

                updateToggleLabel('include_modem_toggle', 'include_modem_label');
                updateToggleLabel('is_installation_free_toggle', 'is_installation_free_label');

                // Currency formatting
                document.querySelectorAll('.currency-input').forEach(input => {
                    const hiddenInput = document.getElementById(input.id.replace('_formatted', ''));

                    input.addEventListener('input', function(e) {
                        let value = this.value.replace(/[^\d]/g, '');
                        hiddenInput.value = value;
                        this.value = value.length > 0 ? formatCurrency(value) : '';
                    });

                    input.addEventListener('focus', function() {
                        this.select();
                    });
                });

                function formatCurrency(number) {
                    return number.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
                }
            });
        </script>
    @endsection
</x-layouts.app-layout>
