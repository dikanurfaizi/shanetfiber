<x-layouts.cpanel.settings.app-layout title="Web System">
    <div class="card-header py-3">
        <h5 class="m-0 fw-bold text-primary">{{ __('websystem.header.edit') }}</h5>
    </div>

    <div class="row">
        <div class="col-lg-12">
            <form action="{{ route('websystem.update', $websystem->slug) }}" method="POST">
                @csrf
                @method('PATCH')

                <div class="card-body col-lg-8">
                    {{-- Web Title --}}
                    <div class="row align-items-center">
                        <div class="col-md-12 mb-3">
                            <label for="title" class="form-label">
                                <strong>{{ __('websystem.label.web-title') }}</strong>
                            </label>
                            <input type="text" name="title" id="title"
                                value="{{ old('title', $websystem->title) }}"
                                placeholder="{{ __('websystem.placeholder.web-title') }}"
                                class="form-control @error('title') is-invalid @enderror">
                            @error('title')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    {{-- Email --}}
                    <div class="row align-items-center">
                        <div class="col-md-12 mb-3">
                            <label for="email" class="form-label">
                                <strong>{{ __('websystem.label.email') }}</strong>
                            </label>
                            <input type="email" name="email" id="email"
                                value="{{ old('email', $websystem->email) }}"
                                placeholder="{{ __('websystem.placeholder.email') }}"
                                class="form-control @error('email') is-invalid @enderror">
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    {{-- Address --}}
                    <div class="row align-items-center">
                        <div class="col-md-12 mb-3">
                            <label for="address" class="form-label">
                                <strong>{{ __('websystem.label.address') }}</strong>
                            </label>
                            <input type="text" name="address" id="address"
                                value="{{ old('address', $websystem->address) }}"
                                placeholder="{{ __('websystem.placeholder.address') }}"
                                class="form-control @error('address') is-invalid @enderror">
                            @error('address')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    {{-- City --}}
                    <div class="row align-items-center">
                        <div class="col-md-12 mb-3">
                            <label for="city" class="form-label">
                                <strong>{{ __('websystem.label.city') }}</strong>
                            </label>
                            <input type="text" name="city" id="city"
                                value="{{ old('city', $websystem->city) }}"
                                placeholder="{{ __('websystem.placeholder.city') }}"
                                class="form-control @error('city') is-invalid @enderror">
                            @error('city')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    {{-- Postal Code --}}
                    <div class="row align-items-center">
                        <div class="col-md-12 mb-3">
                            <label for="postal_code" class="form-label">
                                <strong>{{ __('websystem.label.postal_code') }}</strong>
                            </label>
                            <input type="text" name="postal_code" id="postal_code"
                                value="{{ old('postal_code', $websystem->postal_code) }}"
                                placeholder="{{ __('websystem.placeholder.postal_code') }}"
                                class="form-control @error('postal_code') is-invalid @enderror">
                            @error('postal_code')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    {{-- Phone --}}
                    <div class="row align-items-center">
                        <div class="col-md-12 mb-3">
                            <label for="phone" class="form-label">
                                <strong>{{ __('websystem.label.phone') }}</strong>
                            </label>
                            <input type="text" name="phone" id="phone"
                                value="{{ old('phone', $websystem->phone) }}"
                                placeholder="{{ __('websystem.placeholder.phone') }}"
                                class="form-control @error('phone') is-invalid @enderror">
                            @error('phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    {{-- Tax Rate --}}
                    @php
                        $isTaxEnabled = (bool) old('tax_button', $websystem->enable_tax);
                    @endphp
                    <div class="row align-items-center">
                        <div class="col-md-6 mb-3">
                            <label for="tax_rate" class="form-label">
                                <strong>{{ __('websystem.label.tax-rate') }} (%)</strong>
                            </label>
                            <div class="form-check form-switch mb-2">
                                <input class="form-check-input" value="true" type="checkbox" id="enable_tax_button"
                                    name="tax_button" @checked($isTaxEnabled)>
                                <label class="form-check-label" for="enable_tax_button">
                                    {{ __('websystem.label.enable-tax-button') }}
                                </label>
                            </div>
                            <div id="tax" class="{{ $isTaxEnabled ? '' : 'd-none' }}">
                                <input type="text" name="tax_rate" id="tax_rate"
                                    value="{{ old('tax_rate', $websystem->tax_rate) }}"
                                    placeholder="{{ __('websystem.placeholder.tax-rate') }}"
                                    class="form-control @error('tax_rate') is-invalid @enderror">
                                <div class="form-text text-info">{{ __('websystem.info.tax-rate') }}</div>
                                @error('tax_rate')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-footer">
                    <button type="submit" class="btn btn-sm btn-success">
                        <i class="fa fa-save"></i>&nbsp;{{ __('websystem.button.update') }}
                    </button>
                </div>
            </form>
        </div>
    </div>

    @section('custom_scripts')
        <script>
            $('#enable_tax_button').on('click', function(e) {
                if (e.currentTarget.checked) {
                    $('#tax').removeClass('d-none');
                } else {
                    $('#tax').addClass('d-none');
                }
            });
        </script>
    @endsection
</x-layouts.cpanel.settings.app-layout>
