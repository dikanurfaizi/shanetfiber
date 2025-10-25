<x-layouts.app-layout title="Edit Package">

    <div class="row">
        <div class="col-lg-12">

            {!! Form::model($package, ['method' => 'PATCH', 'route' => ['packages.update', $package->slug]]) !!}
            <div class="card-body col-lg-8">

                <div class="row align-items-center">
                    <div class="col-md-12 mb-3">
                        <label for="name"><strong>{{ __('package.label.name') }}</strong></label>
                        {!! Form::text('name', null, ['placeholder' => __('package.placeholder.name'), 'class' => 'form-control']) !!}
                        @if ($errors->has('name'))
                            <span class="text-danger">{{ $errors->first('name') }}</span>
                        @endif
                    </div>
                </div>

                <div class="row align-items-center">
                    <div class="col-md-12 mb-3">
                        <label for="profile"><strong>{{ __('package.label.profile') }}</strong></label>

                        <select id="profile-package-dropdown" name="profile" class="form-control">
                            @foreach ($profiles as $profile)
                                <option value="{{ $profile->profile_name }}" @selected($profile->profile_name == $package->profile)>
                                    {{ $profile->profile_name }}
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
                        <label for="speed_up_to"><strong>{{ __('package.label.speed_up_to') }}</strong></label>
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
                        <label for="speed_unit"><strong>{{ __('package.label.speed_unit') }}</strong></label>
                        <select name="speed_unit" class="form-control">
                            <option value="Mbps" @selected($package->speed_unit == 'Mbps')>Mbps</option>
                            <option value="Gbps" @selected($package->speed_unit == 'Gbps')>Gbps</option>
                        </select>
                        @if ($errors->has('speed_unit'))
                            <span class="text-danger">{{ $errors->first('speed_unit') }}</span>
                        @endif
                    </div>
                </div>

                <div class="row align-items-center">
                    <div class="col-md-6 mb-3">
                        <label
                            for="recommended_device_min"><strong>{{ __('package.label.recommended_device_min') }}</strong></label>
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
                            for="recommended_device_max"><strong>{{ __('package.label.recommended_device_max') }}</strong></label>
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
                        <label for="quota"><strong>{{ __('package.label.quota') }}</strong></label>
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
                        <label for="include_modem"><strong>{{ __('package.label.incl_modem') }}</strong></label><br>
                        <div class="d-flex align-items-center">
                            <label class="switch me-2">
                                {!! Form::checkbox('include_modem', 1, $package->include_modem, [
                                    'class' => 'form-check-input toggle-switch',
                                    'id' => 'include_modem_toggle',
                                ]) !!}
                                <span class="slider round"></span>
                            </label>
                            <span id="include_modem_label" class="toggle-label">
                                {{ $package->include_modem ? 'Yes' : 'No' }}
                            </span>
                        </div>
                        @if ($errors->has('include_modem'))
                            <span class="text-danger">{{ $errors->first('include_modem') }}</span>
                        @endif
                    </div>
                    <div class="col-md-6 mb-3">
                        <label
                            for="is_installation_free"><strong>{{ __('package.label.installation_free') }}</strong></label><br>
                        <div class="d-flex align-items-center">
                            <label class="switch me-2">
                                {!! Form::checkbox('is_installation_free', 1, $package->is_installation_free, [
                                    'class' => 'form-check-input toggle-switch',
                                    'id' => 'is_installation_free_toggle',
                                ]) !!}
                                <span class="slider round"></span>
                            </label>
                            <span id="is_installation_free_label" class="toggle-label">
                                {{ $package->is_installation_free ? 'Yes' : 'No' }}
                            </span>
                        </div>
                        @if ($errors->has('is_installation_free'))
                            <span class="text-danger">{{ $errors->first('is_installation_free') }}</span>
                        @endif
                    </div>
                </div>

                <div class="row align-items-center">
                    <div class="col-md-12 mb-3">
                        <label
                            for="installation_fee"><strong>{{ __('package.label.installation_fee') }}</strong></label>
                        <div class="input-group">
                            <span class="input-group-text">Rp</span>
                            {!! Form::text(
                                'installation_fee_formatted',
                                $package->installation_fee ? number_format($package->installation_fee, 0, ',', '.') : '0',
                                [
                                    'placeholder' => 'Enter installation fee',
                                    'class' => 'form-control currency-input',
                                    'id' => 'installation_fee_formatted',
                                ],
                            ) !!}
                            {!! Form::hidden('installation_fee', $package->installation_fee ?? 0, ['id' => 'installation_fee']) !!}
                        </div>
                        @if ($errors->has('installation_fee'))
                            <span class="text-danger">{{ $errors->first('installation_fee') }}</span>
                        @endif
                    </div>
                </div>

                <div class="row align-items-center">
                    <div class="col-md-12 mb-3">
                        <label for="price"><strong>{{ __('package.label.price') }}</strong></label>
                        <div class="input-group">
                            <span class="input-group-text">Rp</span>
                            {!! Form::text('price_formatted', $package->price ? number_format($package->price, 0, ',', '.') : null, [
                                'placeholder' => __('package.placeholder.price'),
                                'class' => 'form-control currency-input',
                                'id' => 'price_formatted',
                            ]) !!}
                            {!! Form::hidden('price', $package->price, ['id' => 'price']) !!}
                        </div>
                        @if ($errors->has('price'))
                            <span class="text-danger">{{ $errors->first('price') }}</span>
                        @endif
                    </div>
                </div>

                <div class="row align-items-center">
                    <div class="col-md-12 mb-3">
                        <label
                            for="package_category_id"><strong>{{ __('package.label.package_category') }}</strong></label>
                        <select name="package_category_id" class="form-control">
                            @foreach ($categories as $cat)
                                <option value="{{ $cat->id }}" @selected($cat->id == $package->package_category_id)>{{ $cat->name }}
                                </option>
                            @endforeach
                        </select>
                        @if ($errors->has('package_category_id'))
                            <span class="text-danger">{{ $errors->first('package_category_id') }}</span>
                        @endif
                    </div>
                    {{-- <div class="col-md-6 mb-3">
                        <label for="duration_months"><strong>{{ __('package.label.billing_cycle') }}</strong></label>
                        <select name="duration_months" class="form-control">
                            @foreach (\App\Enums\SubscriptionDuration::cases() as $cycle)
                                <option value="{{ $cycle->months() }}" @selected($cycle->months() == $package->subscriptionDurations->duration_months)>
                                    {{ $cycle->labelEn() }}
                                </option>
                            @endforeach
                        </select>
                        @if ($errors->has('duration_months'))
                            <span class="text-danger">{{ $errors->first('duration_months') }}</span>
                        @endif
                    </div> --}}
                </div>

                <div class="row align-items-center">
                    <div class="col-md-12 mb-3">
                        <label for="description"><strong>{{ __('package.label.description') }}</strong></label>
                        {!! Form::text('description', null, [
                            'placeholder' => __('package.placeholder.description'),
                            'class' => 'form-control',
                        ]) !!}
                        @if ($errors->has('description'))
                            <span class="text-danger">{{ $errors->first('description') }}</span>
                        @endif
                    </div>
                </div>
            </div>
            <div class="card-footer">
                <button type="submit" class="btn btn-success">
                    <i class="fa fa-save"></i> &nbsp; {{ __('package.button.update') }}
                </button>

                <a class="btn btn-primary" href="{{ route('subscriptionDurations.manage', $package->slug) }}">
                    <i class="fa fa-clock"></i> &nbsp; {{ __('package.button.duration') }}
                </a>


                <a class="btn btn-secondary" href="{{ route('packages.index') }}">
                    <i class="fa fa-arrow-left"></i> &nbsp; {{ __('package.button.cancel') }}
                </a>
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
                // Function to update toggle label
                function updateToggleLabel(checkboxId, labelId) {
                    const checkbox = document.getElementById(checkboxId);
                    const label = document.getElementById(labelId);

                    checkbox.addEventListener('change', function() {
                        label.textContent = this.checked ? 'Yes' : 'No';
                    });
                }

                // Initialize both toggles
                updateToggleLabel('include_modem_toggle', 'include_modem_label');
                updateToggleLabel('is_installation_free_toggle', 'is_installation_free_label');
            });
        </script>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // Format currency inputs
                document.querySelectorAll('.currency-input').forEach(input => {
                    const hiddenInput = document.getElementById(input.id.replace('_formatted', ''));

                    // Handle input changes
                    input.addEventListener('input', function(e) {
                        // Remove non-digit characters
                        let value = this.value.replace(/[^\d]/g, '');

                        // Update hidden field with raw number
                        hiddenInput.value = value;

                        // Format display value
                        if (value.length > 0) {
                            this.value = formatCurrency(value);
                        } else {
                            this.value = '';
                        }
                    });

                    // Handle focus - select all text
                    input.addEventListener('focus', function() {
                        this.select();
                    });
                });

                function formatCurrency(number) {
                    // Format number with thousand separators
                    return number.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
                }
            });
        </script>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const formattedInput = document.getElementById('installation_fee_formatted');
                const hiddenInput = document.getElementById('installation_fee');

                // Format input saat halaman dimuat
                formatCurrencyInput(formattedInput, hiddenInput);

                // Format input saat ada perubahan
                formattedInput.addEventListener('input', function() {
                    formatCurrencyInput(formattedInput, hiddenInput);
                });

                // Format input saat keluar dari field
                formattedInput.addEventListener('blur', function() {
                    if (!this.value) {
                        this.value = '0';
                        hiddenInput.value = '0';
                    }
                    formatCurrencyInput(formattedInput, hiddenInput);
                });

                function formatCurrencyInput(formattedElement, hiddenElement) {
                    // Hapus semua karakter selain angka
                    let value = formattedElement.value.replace(/[^0-9]/g, '');

                    // Jika kosong, set ke 0
                    if (!value) {
                        value = '0';
                    }

                    // Simpan nilai numerik ke hidden input
                    hiddenElement.value = value;

                    // Format tampilan dengan separator ribuan
                    formattedElement.value = parseInt(value).toLocaleString('id-ID');
                }
            });
        </script>
    @endsection
</x-layouts.app-layout>
