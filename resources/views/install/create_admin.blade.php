@extends('layouts.guest')

@section('content')
    <div class="row justify-content-center align-items-center min-vh-100">
        <div class="col-xl-6 col-lg-8 col-md-9">
            <div class="card border-0 shadow-lg my-5"
                style="background: rgba(15, 23, 42, 0.75); backdrop-filter: blur(16px); -webkit-backdrop-filter: blur(16px); border-radius: 1rem; border: 1px solid rgba(255, 255, 255, 0.1);">
                <div class="card-body p-5 text-white">
                    <div class="text-center mb-4">
                        <h1 class="h4 font-weight-bold mb-3" style="color: #f8fafc;">{{ __('install.title.admin') }}</h1>
                    </div>

                    <form class="user" action="{{ route('installStoreAdmin') }}" method="post">
                        @csrf

                        <!-- Nama Lengkap -->
                        <div class="mb-3">
                            <input type="text" name="name" value="{{ old('name') }}"
                                class="form-control form-control-user @error('name') is-invalid @enderror"
                                id="exampleInputName" placeholder="{{ __('install.form.name') }}"
                                style="background: rgba(255, 255, 255, 0.05); border: 1px solid rgba(255, 255, 255, 0.1); color: #fff;">
                            @error('name')
                                <div class="text-danger small mt-1">
                                    <label>{{ $message }}</label>
                                </div>
                            @enderror
                        </div>

                        <!-- Email -->
                        <div class="mb-3">
                            <input type="email" name="email" value="{{ old('email') }}"
                                class="form-control form-control-user @error('email') is-invalid @enderror"
                                id="exampleInputEmail" placeholder="{{ __('install.form.email') }}"
                                style="background: rgba(255, 255, 255, 0.05); border: 1px solid rgba(255, 255, 255, 0.1); color: #fff;">
                            @error('email')
                                <div class="text-danger small mt-1">
                                    <label>{{ $message }}</label>
                                </div>
                            @enderror
                        </div>

                        <!-- Password & Confirm Password Row -->
                        <div class="mb-3 row">
                            <!-- Password -->
                            <div class="col-sm-6 mb-3 mb-sm-0">
                                <div class="input-group">
                                    <input type="password" name="password"
                                        class="form-control form-control-user @error('password') is-invalid @enderror"
                                        id="exampleInputPassword" placeholder="{{ __('install.form.password') }}"
                                        style="background: rgba(255, 255, 255, 0.05); border: 1px solid rgba(255, 255, 255, 0.1); color: #fff; border-top-right-radius: 0; border-bottom-right-radius: 0;">
                                    <button class="btn btn-outline-secondary text-white" type="button" id="togglePassword"
                                        style="border: 1px solid rgba(255, 255, 255, 0.1); background: rgba(255, 255, 255, 0.05);">
                                        <i class="fa fa-eye" id="passwordIcon"></i>
                                    </button>
                                </div>
                                <!-- Indikator Kekuatan Password -->
                                <div class="mt-2">
                                    <div class="progress" style="height: 6px; background: rgba(255, 255, 255, 0.1);">
                                        <div id="passwordStrengthBar" class="progress-bar" role="progressbar"
                                            style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">
                                        </div>
                                    </div>
                                    <small id="passwordStrengthText" class="form-text mt-1 text-muted"></small>
                                </div>
                                @error('password')
                                    <div class="text-danger small mt-1">
                                        <label>{{ $message }}</label>
                                    </div>
                                @enderror
                            </div>

                            <!-- Confirm Password -->
                            <div class="col-sm-6">
                                <div class="input-group">
                                    <input type="password" name="password_confirmation"
                                        class="form-control form-control-user @error('password_confirmation') is-invalid @enderror"
                                        id="exampleRepeatPassword" placeholder="{{ __('install.form.repeat_password') }}"
                                        style="background: rgba(255, 255, 255, 0.05); border: 1px solid rgba(255, 255, 255, 0.1); color: #fff; border-top-right-radius: 0; border-bottom-right-radius: 0;">
                                    <button class="btn btn-outline-secondary text-white" type="button"
                                        id="toggleConfirmPassword"
                                        style="border: 1px solid rgba(255, 255, 255, 0.1); background: rgba(255, 255, 255, 0.05);">
                                        <i class="fa fa-eye" id="confirmPasswordIcon"></i>
                                    </button>
                                </div>
                                <div id="passwordMatchText" class="form-text mt-1 small"></div>
                                @error('password_confirmation')
                                    <div class="text-danger small mt-1">
                                        <label>{{ $message }}</label>
                                    </div>
                                @enderror
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary btn-user w-100 py-2 font-weight-bold shadow-sm mt-3"
                            style="background-color: #2563eb; border: none; border-radius: 0.5rem;">
                            {{ __('install.button.create_admin') }}
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Script JavaScript untuk Validasi Live & Show/Hide -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Toggle Show/Hide Password Utama
            const togglePassword = document.querySelector('#togglePassword');
            const password = document.querySelector('#exampleInputPassword');
            const passwordIcon = document.querySelector('#passwordIcon');

            togglePassword.addEventListener('click', function() {
                const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
                password.setAttribute('type', type);
                passwordIcon.classList.toggle('fa-eye');
                passwordIcon.classList.toggle('fa-eye-slash');
            });

            // Toggle Show/Hide Confirm Password
            const toggleConfirmPassword = document.querySelector('#toggleConfirmPassword');
            const confirmPassword = document.querySelector('#exampleRepeatPassword');
            const confirmPasswordIcon = document.querySelector('#confirmPasswordIcon');

            toggleConfirmPassword.addEventListener('click', function() {
                const type = confirmPassword.getAttribute('type') === 'password' ? 'text' : 'password';
                confirmPassword.setAttribute('type', type);
                confirmPasswordIcon.classList.toggle('fa-eye');
                confirmPasswordIcon.classList.toggle('fa-eye-slash');
            });

            // Password Strength Indicator & Rule Checker
            const strengthBar = document.querySelector('#passwordStrengthBar');
            const strengthText = document.querySelector('#passwordStrengthText');
            const matchText = document.querySelector('#passwordMatchText');

            const textLow = "{{ __('install.form.strength_weak') }}";
            const textMedium = "{{ __('install.form.strength_medium') }}";
            const textStrong = "{{ __('install.form.strength_strong') }}";
            const textMatchOk = "{{ __('install.form.match_success') }}";
            const textMatchErr = "{{ __('install.form.match_error') }}";
            const textSeqWarn = "{{ __('install.form.sequential_warning') }}";

            password.addEventListener('input', function() {
                const val = password.value;

                const hasSequential = /(012|123|234|345|456|567|678|789|987|876|765|654|543|432|321|210)/
                    .test(val);
                const hasDateFormat = /\d{8}/.test(val);

                let score = 0;
                if (val.length >= 8) score++;
                if (/[A-Z]/.test(val) && /[a-z]/.test(val)) score++;
                if (/[0-9]/.test(val) && /[^A-Za-z0-9]/.test(val)) score++;

                if (val.length === 0) {
                    strengthBar.style.width = '0%';
                    strengthBar.style.backgroundColor = 'transparent';
                    strengthText.textContent = '';
                } else if (hasSequential || hasDateFormat || val.length < 8 || score <= 1) {
                    strengthBar.style.width = '33%';
                    strengthBar.style.backgroundColor = '#dc3545'; // Merah (Low)
                    strengthText.style.color = '#dc3545';
                    strengthText.textContent = textLow + (hasSequential || hasDateFormat ? textSeqWarn :
                    '');
                } else if (score === 2) {
                    strengthBar.style.width = '66%';
                    strengthBar.style.backgroundColor = '#ffc107'; // Kuning (Medium)
                    strengthText.style.color = '#ffc107';
                    strengthText.textContent = textMedium;
                } else {
                    strengthBar.style.width = '100%';
                    strengthBar.style.backgroundColor = '#198754'; // Hijau (Strong)
                    strengthText.style.color = '#198754';
                    strengthText.textContent = textStrong;
                }

                checkPasswordMatch();
            });

            confirmPassword.addEventListener('input', function() {
                checkPasswordMatch();
            });

            function checkPasswordMatch() {
                if (confirmPassword.value.length === 0) {
                    matchText.textContent = '';
                    return;
                }

                if (password.value === confirmPassword.value) {
                    matchText.style.color = '#198754';
                    matchText.textContent = textMatchOk;
                } else {
                    matchText.style.color = '#dc3545';
                    matchText.textContent = textMatchErr;
                }
            }
        });
    </script>
@endsection
