@extends('layouts.adm-guest')

@section('content')
    <div class="min-vh-100 d-flex align-items-center justify-content-center position-relative py-4 py-sm-5"
        style="background: url('/images/slider1.jpeg') center/cover no-repeat fixed;">

        <!-- Background Overlay (Sama dengan Customer) -->
        <div class="glass-bg-overlay"></div>

        <!-- Glassmorphism Card Container -->
        <div class="card glass-card border-0 p-4 p-sm-5 position-relative">

            <!-- Logo & Header Admin -->
            <div class="text-center mb-4">
                <div class="glass-logo-wrapper mb-3 mx-auto d-flex align-items-center justify-content-center">
                    <img src="{{ asset('images/logo-secondary.png') }}" alt="Logo" class="img-fluid glass-logo">
                </div>
                <h5 class="mt-3 mb-1 fw-bold text-white">{{ __('customer.title.cust_mgmt') }}</h5>
                <small class="text-white-50">{{ __('customer.info.admin_panel') }}</small>
            </div>

            <form action="{{ route('login') }}" method="post" class="user">
                @csrf

                <!-- Email Input -->
                <div class="mb-3">
                    <input type="email" name="email" value="{{ old('email') }}"
                        class="form-control form-control-glass @error('email') is-invalid @enderror" id="exampleInputEmail"
                        aria-describedby="emailHelp" placeholder="{{ __('customer.label.email') }}" required autofocus>

                    @error('email')
                        <small class="text-danger mt-1 d-block fw-semibold pl-2">{{ $message }}</small>
                    @enderror
                </div>

                <!-- Password Input -->
                <div class="mb-3 position-relative">
                    <input type="password" name="password"
                        class="form-control form-control-glass @error('password') is-invalid @enderror" id="passwordInput"
                        placeholder="{{ __('customer.table.password') ?? 'Password' }}" required>

                    <span onclick="togglePasswordVisibility()" class="password-toggle-btn">
                        <i id="passwordIcon" class="fas fa-eye text-white-50"></i>
                    </span>

                    @error('password')
                        <small class="text-danger mt-1 d-block fw-semibold pl-2">{{ $message }}</small>
                    @enderror
                </div>

                <!-- Remember Me Checkbox -->
                <div class="mb-4">
                    <div class="custom-control custom-checkbox small">
                        <input type="checkbox" name="remember" class="custom-control-input" id="customCheck">
                        <label class="custom-control-label text-white-50 cursor-pointer" for="customCheck">
                            {{ __('customer.table.remember_me') ?? 'Ingat Saya' }}
                        </label>
                    </div>
                </div>

                <!-- Submit Button -->
                <button type="submit" class="btn btn-glass-primary w-100 fw-bold shadow-sm py-2">
                    {{ __('customer.button.login') ?? 'Login' }}
                </button>
            </form>

            @if (Route::has('register'))
                <hr class="border-glass my-4">
                <div class="text-center">
                    <a class="small text-white-50 hover-white text-decoration-none" href="{{ route('register') }}">
                        {{ __('customer.button.create_new_account') ?? 'Buat Akun Baru!' }}
                    </a>
                </div>
            @endif

        </div>
    </div>
@endsection

@section('custom_styles')
    <style>
        /* 1. Overlay Gelap Halus untuk Background Gambar */
        .glass-bg-overlay {
            position: absolute;
            inset: 0;
            background: linear-gradient(135deg, rgba(15, 23, 42, 0.65) 0%, rgba(30, 27, 75, 0.75) 100%);
            backdrop-filter: blur(6px);
            -webkit-backdrop-filter: blur(6px);
        }

        /* 2. Glassmorphism Card Utama */
        .glass-card {
            z-index: 1;
            width: 100%;
            max-width: 440px;
            border-radius: 24px !important;
            background: rgba(255, 255, 255, 0.12) !important;
            backdrop-filter: blur(16px) saturate(180%);
            -webkit-backdrop-filter: blur(16px) saturate(180%);
            border: 1px solid rgba(255, 255, 255, 0.22) !important;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.35) !important;
            box-sizing: border-box;
        }

        /* 3. Logo Container Squircle Efek Kaca */
        .glass-logo-wrapper {
            width: 90px;
            height: 90px;
            background: rgba(255, 255, 255, 0.15);
            border: 1px solid rgba(255, 255, 255, 0.3);
            border-radius: 22px;
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            padding: 12px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
        }

        .glass-logo {
            max-height: 100%;
            object-fit: contain;
            filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.2));
        }

        /* 4. Form Input Transparan Glass */
        .form-control-glass {
            background: rgba(255, 255, 255, 0.1) !important;
            border: 1px solid rgba(255, 255, 255, 0.2) !important;
            color: #ffffff !important;
            border-radius: 12px !important;
            padding: 0.75rem 1rem !important;
            transition: all 0.3s ease;
            height: 48px;
        }

        .form-control-glass::placeholder {
            color: rgba(255, 255, 255, 0.55) !important;
        }

        .form-control-glass:focus {
            background: rgba(255, 255, 255, 0.2) !important;
            border-color: rgba(255, 255, 255, 0.5) !important;
            box-shadow: 0 0 15px rgba(255, 255, 255, 0.2) !important;
            color: #ffffff !important;
        }

        /* 5. Tombol Password Eye Toggle */
        .password-toggle-btn {
            position: absolute;
            right: 14px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            z-index: 10;
            padding: 4px;
        }

        .password-toggle-btn:hover i {
            color: #ffffff !important;
        }

        /* 6. Tombol Primary Glassmorphic Glowing */
        .btn-glass-primary {
            background: linear-gradient(135deg, rgba(40, 167, 69, 0.85), rgba(33, 136, 56, 0.95)) !important;
            border: 1px solid rgba(255, 255, 255, 0.3) !important;
            color: #ffffff !important;
            border-radius: 12px !important;
            min-height: 46px;
            transition: all 0.3s ease;
        }

        .btn-glass-primary:hover {
            background: linear-gradient(135deg, rgba(33, 136, 56, 0.95), rgba(24, 100, 41, 1)) !important;
            box-shadow: 0 8px 25px rgba(40, 167, 69, 0.4) !important;
            transform: translateY(-1px);
        }

        /* 7. Divider Line & Utility */
        .border-glass {
            border-top: 1px solid rgba(255, 255, 255, 0.15) !important;
        }

        .hover-white:hover {
            color: #ffffff !important;
            text-decoration: underline !important;
        }

        .cursor-pointer {
            cursor: pointer;
        }

        /* ===== OPTIMASI RESPONSIVE MOBILE (MAX-WIDTH 575.98px) ===== */
        @media (max-width: 575.98px) {
            .glass-card {
                width: 92% !important;
                /* Kartu memanfaatkan 92% lebar layar HP */
                max-width: 100% !important;
                padding: 1.75rem 1.25rem !important;
                border-radius: 20px !important;
            }

            .glass-logo-wrapper {
                width: 78px;
                height: 78px;
                border-radius: 18px;
                padding: 10px;
            }

            .form-control-glass {
                height: 46px;
                font-size: 15px;
            }
        }
    </style>
@endsection

@section('custom_scripts')
    <script>
        function togglePasswordVisibility() {
            let input = document.getElementById("passwordInput");
            let icon = document.getElementById("passwordIcon");

            if (input.type === "password") {
                input.type = "text";
                icon.classList.replace("fa-eye", "fa-eye-slash");
            } else {
                input.type = "password";
                icon.classList.replace("fa-eye-slash", "fa-eye");
            }
        }
    </script>
@endsection
