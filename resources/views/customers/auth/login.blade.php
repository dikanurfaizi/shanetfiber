@extends('layouts.cust-guest')

@section('content')
    <div class="min-vh-100 d-flex align-items-center justify-content-center position-relative py-4 py-sm-5"
        style="background: url('/images/slider1.jpeg') center/cover no-repeat fixed;">

        <!-- Background Overlay (Diperhalus untuk efek kaca) -->
        <div class="glass-bg-overlay"></div>

        <!-- Glassmorphism Card Container -->
        <div class="card glass-card border-0 p-4 p-sm-5 position-relative">

            <!-- Logo & Header -->
            <div class="text-center mb-4">
                <div class="glass-logo-wrapper mb-3 mx-auto d-flex align-items-center justify-content-center">
                    <img src="{{ asset('images/logo-secondary.png') }}" alt="Logo" class="img-fluid glass-logo">
                </div>
                <h5 class="mt-3 mb-1 fw-bold text-white">
                    {{ __('customer.login.title') ?? __('Cek Tagihan Anda') }}
                </h5>
                <small class="text-white-50">
                    {{ __('customer.login.subtitle') ?? __('Masuk untuk melihat detail tagihan') }}
                </small>
            </div>

            <form action="{{ route('customerLoginPost') }}" method="POST">
                @csrf

                <!-- Email / Phone Input -->
                <div class="mb-3">
                    <input type="text" name="email"
                        class="form-control form-control-glass @error('email') is-invalid @enderror"
                        placeholder="{{ __('customer.placeholder.email_or_phone') ?? __('Email atau Nomor HP') }}"
                        value="{{ old('email') }}" required autofocus>

                    @error('email')
                        <small class="text-danger mt-1 d-block fw-semibold ps-2">{{ $message }}</small>
                    @enderror
                </div>

                <!-- Password Input -->
                <div class="mb-3 position-relative">
                    <input type="password" name="password"
                        class="form-control form-control-glass @error('password') is-invalid @enderror" id="passwordInput"
                        placeholder="{{ __('customer.placeholder.password') ?? __('Password') }}" required>

                    <span onclick="togglePasswordVisibility()" class="password-toggle-btn">
                        <i id="passwordIcon" class="fas fa-eye text-white-50"></i>
                    </span>

                    @error('password')
                        <small class="text-danger mt-1 d-block fw-semibold ps-2">{{ $message }}</small>
                    @enderror
                </div>

                <!-- Submit Button -->
                <button type="submit" class="btn btn-glass-primary w-100 fw-bold shadow-sm mt-2 py-2">
                    {{ __('customer.button.login') ?? __('Login') }}
                </button>

                <!-- Forgot Password -->
                <div class="text-center mt-4">
                    <a href="#" class="small text-white-50 hover-white text-decoration-none" data-bs-toggle="modal"
                        data-bs-target="#forgotPasswordModal" data-bs-toggle="modal" data-bs-target="#forgotPasswordModal">
                        {{ __('customer.button.forgot_password') ?? __('Lupa Password?') }}
                    </a>
                </div>
            </form>
        </div>
    </div>

    @include('auth.partials.forgot-password')
@endsection

@section('custom_styles')
    <style>
        /* 1. Overlay Gelap Halus untuk Background Gambar */
        .glass-bg-overlay {
            position: absolute;
            inset: 0;
            background: linear-gradient(135deg, rgba(15, 23, 42, 0.6) 0%, rgba(30, 27, 75, 0.7) 100%);
            backdrop-filter: blur(6px);
            -webkit-backdrop-filter: blur(6px);
        }

        /* 2. Glassmorphism Card Utama (Dioptimalkan untuk Mobile agar Tidak Kekecilan) */
        .glass-card {
            z-index: 1;
            width: 100%;
            max-width: 440px;
            /* Ukuran proporsional desktop */
            border-radius: 24px !important;
            background: rgba(255, 255, 255, 0.12) !important;
            backdrop-filter: blur(16px) saturate(180%);
            -webkit-backdrop-filter: blur(16px) saturate(180%);
            border: 1px solid rgba(255, 255, 255, 0.22) !important;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.35) !important;
            box-sizing: border-box;
        }

        /* 3. Logo Container Efek Kaca Squircle */
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
            background: linear-gradient(135deg, rgba(13, 110, 253, 0.85), rgba(10, 88, 202, 0.95)) !important;
            border: 1px solid rgba(255, 255, 255, 0.3) !important;
            color: #ffffff !important;
            border-radius: 12px !important;
            min-height: 46px;
            transition: all 0.3s ease;
        }

        .btn-glass-primary:hover {
            background: linear-gradient(135deg, rgba(10, 88, 202, 0.95), rgba(8, 66, 152, 1)) !important;
            box-shadow: 0 8px 25px rgba(13, 110, 253, 0.4) !important;
            transform: translateY(-1px);
        }

        .hover-white:hover {
            color: #ffffff !important;
            text-decoration: underline !important;
        }

        /* ===== OPTIMASI MOBILE (MAX-WIDTH 575.98px) ===== */
        @media (max-width: 575.98px) {
            .glass-card {
                width: 92% !important;
                /* Memanfaatkan 92% lebar layar HP agar tidak kekecilan */
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

        // Forgot password logic
        function fpShowStep(step, channel = null) {
            document.getElementById('fp-step-channel').classList.add('d-none');
            document.getElementById('fp-step-confirm').classList.add('d-none');
            document.getElementById('fp-step-otp').classList.add('d-none');

            document.getElementById(step).classList.remove('d-none');

            if (channel) {
                document.getElementById('fpChannel').value = channel;

                document.getElementById('fpLabel').innerText =
                    channel === 'email' ?
                    "{{ __('customer.forgot.enter_email') ?? 'Masukkan Email Anda' }}" :
                    "{{ __('customer.forgot.enter_whatsapp') ?? 'Masukkan Nomor WhatsApp Anda' }}";
            }
        }

        document.getElementById('forgotPasswordModal')
            ?.addEventListener('hidden.bs.modal', function() {
                document.getElementById('fp-step-channel').classList.remove('d-none');
                document.getElementById('fp-step-confirm').classList.add('d-none');
                document.getElementById('fp-step-otp').classList.add('d-none');
            });
    </script>
@endsection
