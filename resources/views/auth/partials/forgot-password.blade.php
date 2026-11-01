<div class="modal fade" id="forgotPasswordModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content p-3" style="border-radius: 12px;">

            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title font-weight-bold">Reset Password</h5>
                <button type="button" class="close btn-close" data-dismiss="modal" data-bs-dismiss="modal"
                    aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <div class="modal-body">

                <!-- STEP 1: PILIH CHANNEL -->
                <div id="fp-step-channel">
                    <p class="small text-muted mb-3">
                        Pilih metode untuk menerima kode verifikasi
                    </p>

                    <button class="btn btn-outline-primary w-100 mb-2" onclick="fpShowStep('fp-step-confirm', 'email')">
                        Via Email
                    </button>

                    <button class="btn btn-outline-success w-100" onclick="fpShowStep('fp-step-confirm', 'whatsapp')">
                        Via WhatsApp
                    </button>
                </div>

                <!-- STEP 2: INPUT -->
                <div id="fp-step-confirm" class="d-none">
                    <form method="POST" action="{{ route('password.channel.verify') }}">
                        @csrf

                        <input type="hidden" name="channel" id="fpChannel">

                        <div class="mb-3">
                            <label id="fpLabel" class="form-label small"></label>
                            <input type="text" name="login" class="form-control" placeholder="Masukkan data Anda"
                                required>
                        </div>

                        <button type="submit" class="btn btn-primary w-100">
                            Kirim OTP
                        </button>
                    </form>

                    <button class="btn btn-link mt-2 small" onclick="fpShowStep('fp-step-channel')">
                        ← Ganti metode
                    </button>
                </div>

                <!-- STEP 3: OTP -->
                <div id="fp-step-otp" class="d-none">
                    <form method="POST" action="{{ route('password.otp.verify') }}">
                        @csrf

                        <div class="mb-3">
                            <input type="text" name="otp" class="form-control text-center"
                                placeholder="Masukkan kode OTP" required>
                        </div>

                        <button type="submit" class="btn btn-primary w-100">
                            Verifikasi
                        </button>
                    </form>

                    <form method="POST" action="{{ route('password.otp.resend') }}" class="mt-2">
                        @csrf
                        <button type="submit" class="btn btn-link small">
                            Kirim ulang kode
                        </button>
                    </form>

                    <button class="btn btn-link mt-2 small" onclick="fpShowStep('fp-step-channel')">
                        ← Ulang dari awal
                    </button>
                </div>

            </div>
        </div>
    </div>
</div>
