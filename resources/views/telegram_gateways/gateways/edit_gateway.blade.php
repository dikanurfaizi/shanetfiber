<x-layouts.cpanel.settings.app-layout title="{{ __('telegram.gateway.header-edit') }}">
    <div class="card-header py-3">
        <h5 class="m-0 fw-bold text-primary">
            {{ __('telegram.gateway.header-edit') }}
        </h5>
    </div>

    <div class="row">
        <div class="col-lg-12">
            <form action="{{ route('telegramgateway.update_gateway', $data->slug) }}" method="POST">
                @csrf
                @method('PATCH')

                <div class="card-body col-lg-8">
                    {{-- Token --}}
                    <div class="row mb-3">
                        <label for="token" class="col-md-4 col-form-label">
                            {!! trans('telegram.gateway.label.token') !!}
                        </label>
                        <div class="col-md-8">
                            <input type="text" name="token" id="token" value="{{ old('token', $data->token) }}"
                                placeholder="{{ __('telegram.gateway.ph.token') }}"
                                class="form-control @error('token') is-invalid @enderror">
                            @error('token')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    {{-- URL Callback --}}
                    <div class="row mb-3">
                        <label for="url_callback" class="col-md-4 col-form-label">
                            {!! trans('telegram.gateway.label.url-callback') !!}
                        </label>
                        <div class="col-md-8">
                            <input type="text" name="url_callback" id="url_callback"
                                value="{{ old('url_callback', $data->url_callback) }}"
                                placeholder="{{ __('telegram.gateway.ph.url_callback') }}"
                                class="form-control @error('url_callback') is-invalid @enderror">
                            @error('url_callback')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    {{-- Cert File --}}
                    <div class="row mb-3">
                        <label for="cert_file" class="col-md-4 col-form-label">
                            {!! trans('telegram.gateway.label.cert-file') !!}
                        </label>
                        <div class="col-md-8">
                            <input type="text" name="cert_file" id="cert_file"
                                value="{{ old('cert_file', $data->cert_file) }}"
                                placeholder="{{ __('telegram.gateway.ph.cert_file') }}"
                                class="form-control @error('cert_file') is-invalid @enderror">
                            @error('cert_file')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    {{-- Basic Auth Username --}}
                    <div class="row mb-3">
                        <label for="basic_auth_username" class="col-md-4 col-form-label">
                            {!! trans('telegram.gateway.label.auth-username') !!}
                        </label>
                        <div class="col-md-8">
                            <input type="text" name="basic_auth_username" id="basic_auth_username"
                                value="{{ old('basic_auth_username', $data->basic_auth_username) }}"
                                placeholder="{{ __('telegram.gateway.ph.basic_auth_username') }}"
                                class="form-control @error('basic_auth_username') is-invalid @enderror">
                            @error('basic_auth_username')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    {{-- Basic Auth Password --}}
                    <div class="row mb-3">
                        <label for="basic_auth_password" class="col-md-4 col-form-label">
                            {!! trans('telegram.gateway.label.auth-password') !!}
                        </label>
                        <div class="col-md-8">
                            <div class="input-group">
                                <input type="text" name="basic_auth_password" id="basic_auth_password"
                                    value="{{ old('basic_auth_password', $data->basic_auth_password) }}"
                                    placeholder="{{ __('telegram.gateway.ph.basic_auth_password') }}"
                                    pattern="^[a-zA-Z0-9!@#$%^&*()-_+=<>?]*$"
                                    class="form-control @error('basic_auth_password')
is-invalid
@enderror">
                                <button type="button" class="btn btn-outline-secondary"
                                    onclick="togglePasswordVisibility()">
                                    <i id="togglePasswordIcon" class="fas fa-eye"></i>
                                </button>
                                @error('basic_auth_password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    {{-- Auth Disabled Toggle --}}
                    @php
                        $isAuthDisabled = old('auth_disabled', $data->auth_disabled) === 'true';
                    @endphp
                    <div class="row mb-3 align-items-center">
                        <label for="auth_disabled_toggle" class="col-md-4 col-form-label">
                            {!! trans('telegram.gateway.label.auth-disabled') !!}
                        </label>
                        <div class="col-md-8 d-flex align-items-center">
                            <label class="switch mb-0">
                                <input type="hidden" name="auth_disabled" value="false">
                                <input type="checkbox" name="auth_disabled" id="auth_disabled_toggle" value="true"
                                    class="toggle-input" @checked($isAuthDisabled)>
                                <span class="slider round"></span>
                            </label>
                            <span id="auth_disabled_label" class="ms-2">
                                {{ $isAuthDisabled ? 'Enable' : 'Disable' }}
                            </span>
                        </div>
                    </div>
                </div>

                <div class="card-footer d-flex justify-content-between">
                    <a class="btn btn-sm btn-outline-danger" href="{{ route('telegramgateway.index') }}">
                        <i class="fa fa-arrow-left"></i>&nbsp;{{ __('server.button.cancel') }}
                    </a>
                    <button type="submit" class="btn btn-sm btn-outline-success btn-save" id="confirm">
                        <span class="button-text">
                            <i class="fa fa-save"></i>&nbsp;{{ __('telegram.gateway.button.update') }}
                        </span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    @section('custom_scripts')
    @endsection
</x-layouts.cpanel.settings.app-layout>

<style>
    .switch {
        position: relative;
        display: inline-block;
        width: 34px;
        height: 20px;
    }

    .switch .toggle-input {
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
        border-radius: 20px;
    }

    .slider:before {
        position: absolute;
        content: "";
        height: 14px;
        width: 14px;
        left: 3px;
        bottom: 3px;
        background-color: white;
        transition: .4s;
        border-radius: 50%;
    }

    .toggle-input:checked+.slider {
        background-color: #4CAF50;
    }

    .toggle-input:checked+.slider:before {
        transform: translateX(14px);
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const passwordField = document.getElementById('basic_auth_password');
        const toggleIcon = document.getElementById('togglePasswordIcon');

        passwordField.type = 'password';
        toggleIcon.classList.remove('fa-eye-slash');
        toggleIcon.classList.add('fa-eye');
    });

    function togglePasswordVisibility() {
        const passwordField = document.getElementById('basic_auth_password');
        const toggleIcon = document.getElementById('togglePasswordIcon');

        if (passwordField.type === 'password') {
            passwordField.type = 'text';
            toggleIcon.classList.remove('fa-eye');
            toggleIcon.classList.add('fa-eye-slash');
        } else {
            passwordField.type = 'password';
            toggleIcon.classList.remove('fa-eye-slash');
            toggleIcon.classList.add('fa-eye');
        }
    }

    document.getElementById('auth_disabled_toggle').addEventListener('change', function() {
        const label = document.getElementById('auth_disabled_label');
        label.textContent = this.checked ? 'Enable' : 'Disable';
    });
</script>
