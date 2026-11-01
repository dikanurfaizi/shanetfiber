@extends('layouts.cust-guest')

@section('content')
    <div class="min-vh-100 d-flex align-items-center justify-content-center position-relative"
        style="background: url('/images/slider1.jpeg') center/cover no-repeat;">

        <!-- Overlay -->
        <div
            style="
        position:absolute;
        inset:0;
        background: rgba(0,0,0,0.4);
        backdrop-filter: blur(4px);
    ">
        </div>

        <!-- Card Container -->
        <div class="card border-0 p-4 position-relative"
            style="
            z-index: 1;
            width: 100%;
            max-width: 380px;
            border-radius: 16px;
            background: rgba(255,255,255,0.95);
            box-shadow: 0 8px 24px rgba(0,0,0,0.2);
         ">

            <!-- Logo -->
            <div class="text-center mb-4">
                <img src="/images/logo_top_customer.png" alt="Logo" height="55">
                <h5 class="mt-3 mb-1 fw-semibold">Cek Tagihan Anda</h5>
                <small class="text-muted">Masuk untuk melihat detail tagihan</small>
            </div>

            <form action="{{ route('customerLoginPost') }}" method="POST">
                @csrf

                <!-- Email / Phone -->
                <div class="mb-3">
                    <input type="text" name="email" class="form-control @error('email') is-invalid @enderror"
                        placeholder="Email atau Nomor HP" value="{{ old('email') }}" required autofocus>

                    @error('email')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>

                <!-- Password -->
                <div class="mb-3 position-relative">
                    <input type="password" name="password" class="form-control @error('password') is-invalid @enderror"
                        id="passwordInput" placeholder="Password" required>

                    <span onclick="togglePasswordVisibility()"
                        style="position:absolute; right:12px; top:50%; transform:translateY(-50%); cursor:pointer;">
                        <i id="passwordIcon" class="fas fa-eye"></i>
                    </span>

                    @error('password')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>

                <!-- Button -->
                <button type="submit" class="btn btn-primary w-100">
                    Login
                </button>

                <!-- Forgot -->
                <div class="text-center mt-3">
                    <a href="#" data-toggle="modal" data-target="#forgotPasswordModal" data-bs-toggle="modal" data-bs-target="#forgotPasswordModal">
                        Lupa Password?
                    </a>
                </div>
            </form>
        </div>
    </div>

    @include('auth.partials.forgot-password')
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
                    'Masukkan Email Anda' :
                    'Masukkan Nomor WhatsApp Anda';
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
