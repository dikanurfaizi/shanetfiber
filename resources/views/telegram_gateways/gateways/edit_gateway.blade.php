<x-layouts.cpanel.settings.app-layout title="{{ __('telegram.gateway.header-edit') }}">
    <div class="card-header py-3">
        <h5 class="m-0 font-weight-bold text-primary">
            {{ __('telegram.gateway.header-edit') }}</h5>
    </div>

    <div class="row">
        <div class="col-lg-12">
            {!! Form::model($data, ['method' => 'PATCH', 'route' => ['telegramgateway.update_gateway', $data->slug]]) !!}
            <div class="card-body col-lg-8">

                <div class="form-group has-feedback row mb-2">
                    <div class="col-md-4">
                        <label for="token">{!! trans('telegram.gateway.label.token') !!}</label>
                    </div>
                    <div class="col-md-8">

                        {!! Form::text('token', null, [
                            'placeholder' => __('telegram.gateway.ph.token'),
                            'class' => 'form-control',
                        ]) !!}
                        @if ($errors->has('token'))
                            <span class="text-danger">{{ $errors->first('token') }}</span>
                        @endif

                    </div>
                </div>
                <div class="form-group has-feedback row mb-2">
                    <div class="col-md-4">
                        <label for="url_callback">{!! trans('telegram.gateway.label.url-callback') !!}</label>
                    </div>
                    <div class="col-md-8">

                        {!! Form::text('url_callback', null, [
                            'placeholder' => __('telegram.gateway.ph.url_callback'),
                            'class' => 'form-control',
                        ]) !!}
                        @if ($errors->has('url_callback'))
                            <span class="text-danger">{{ $errors->first('url_callback') }}</span>
                        @endif

                    </div>
                </div>
                <div class="form-group has-feedback row mb-2">
                    <div class="col-md-4">
                        <label for="cert_file">{!! trans('telegram.gateway.label.cert-file') !!}</label>
                    </div>
                    <div class="col-md-8">
                        {!! Form::text('cert_file', null, [
                            'placeholder' => __('telegram.gateway.ph.cert_file'),
                            'class' => 'form-control',
                        ]) !!}
                        @if ($errors->has('cert_file'))
                            <span class="text-danger">{{ $errors->first('cert_file') }}</span>
                        @endif

                    </div>
                </div>
                <div class="form-group has-feedback row mb-2">
                    <div class="col-md-4">
                        <label for="auth_username">{!! trans('telegram.gateway.label.auth-username') !!}</label>
                    </div>
                    <div class="col-md-8">
                        {!! Form::text('basic_auth_username', null, [
                            'placeholder' => __('telegram.gateway.ph.basic_auth_username'),
                            'class' => 'form-control',
                        ]) !!}
                        @if ($errors->has('basic_auth_username'))
                            <span class="text-danger">{{ $errors->first('basic_auth_username') }}</span>
                        @endif

                    </div>
                </div>
                <div class="form-group has-feedback row mb-2">
                    <div class="col-md-4">
                        <label for="auth_password">{!! trans('telegram.gateway.label.auth-password') !!}</label>
                    </div>
                    <div class="col-md-8 d-flex align-items-center">
                        {!! Form::text('basic_auth_password', old('basic_auth_password', Form::getModel()?->basic_auth_password), [
                            'placeholder' => __('telegram.gateway.ph.basic_auth_password'),
                            'class' => 'form-control',
                            'id' => 'basic_auth_password',
                            'pattern' => "^[a-zA-Z0-9!@#$%^&*()-_+=<>?]*$",
                        ]) !!}
                        <button type="button" class="btn btn-outline-secondary ms-2"
                            onclick="togglePasswordVisibility()">
                            <i id="togglePasswordIcon" class="fas fa-eye"></i>
                        </button>
                        @if ($errors->has('basic_auth_password'))
                            <span class="text-danger">{{ $errors->first('basic_auth_password') }}</span>
                        @endif
                    </div>
                </div>
                <div class="form-group row mb-2">
                    <div class="col-md-4">
                        <label for="auth_disabled">{!! trans('telegram.gateway.label.auth-disabled') !!}</label>
                    </div>
                    <div class="col-md-8 d-flex align-items-center">
                        <label class="switch">
                            {!! Form::hidden('auth_disabled', 'false') !!}
                            {!! Form::checkbox('auth_disabled', 'true', old('auth_disabled', Form::getModel()?->auth_disabled) === 'true', [
                                'class' => 'toggle-input',
                                'id' => 'auth_disabled_toggle',
                            ]) !!}
                            <span class="slider round"></span>
                        </label>
                        <span id="auth_disabled_label"
                            class="ml-2 text-center">{{ old('auth_disabled', Form::getModel()?->auth_disabled) === 'true' ? 'Enable' : 'Disable' }}</span>
                    </div>
                </div>

            </div>
            <div class="card-footer">

                <a class="btn btn-sm btn-outline-danger" href="{{ route('telegramgateway.index') }}"><i
                        class="fa fa-arrow-left"></i>&nbsp{{ __('server.button.cancel') }}</a>
                <button type="submit" class="btn btn-sm btn-outline-success pull-right btn-save" id="confirm">
                    <span class="button-text"><i class="fa fa-save"></i>
                        &nbsp{{ __('telegram.gateway.button.update') }}</span>
                </button>
            </div>
            {!! Form::close() !!}
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
