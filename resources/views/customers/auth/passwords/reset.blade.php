<x-layouts.customers.app-layout title="Verifikasi OTP">

    <div class="card-header py-3 bg-gradient-primary">
        <h5 class="m-0 fw-bold text-white d-flex align-items-center">
            <i class="fas fa-shield-alt me-3"></i>
            <span>Verifikasi OTP</span>
        </h5>
    </div>

    <div class="card-body">
        <div class="row justify-content-center">
            <div class="col-lg-6">
                <div class="card shadow mb-4">
                    <div class="card-body p-5">
                        <form method="POST" action="{{ route('customer.password.update') }}">
                            @csrf
                            <input type="hidden" name="token" value="{{ $token }}">

                            <div class="mb-3 mb-4">
                                <label for="otp" class="form-label">
                                    <strong>Kode OTP (6 digit)</strong>
                                </label>
                                <div class="input-group">
                                    <input type="text" id="otp" name="otp"
                                        class="form-control @error('otp') is-invalid @enderror" required autofocus>
                                    <div class="input-group-append">
                                        <span class="input-group-text">
                                            <i class="fas fa-lock"></i>
                                        </span>
                                    </div>
                                </div>
                                <small class="text-muted">Cek email/WhatsApp Anda</small>
                                @error('otp')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3 mb-4">
                                <label for="password" class="form-label">
                                    <strong>Password Baru</strong>
                                </label>
                                <input type="password" id="password" name="password"
                                    class="form-control @error('password') is-invalid @enderror" required>
                                @error('password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3 mb-4">
                                <label for="password_confirmation" class="form-label">
                                    <strong>Konfirmasi Password</strong>
                                </label>
                                <input type="password" id="password_confirmation" name="password_confirmation"
                                    class="form-control" required>
                            </div>

                            <div class="mb-3 row mb-0">
                                <div class="col-md-8 offset-md-4">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-sync-alt me-1"></i> Reset Password
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

</x-layouts.customers.app-layout>
