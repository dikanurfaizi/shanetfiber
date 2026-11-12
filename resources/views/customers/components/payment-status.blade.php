<div class="text-center d-flex flex-column align-items-center gap-0 w-100">
    @switch($order->payment_status)
        @case(1)
            {{-- UNPAID --}}
            <div class="w-100 mb-3">
                <p class="head-section">Selesaikan pembayaran dalam</p>
                <p class="timer text-danger fw-bold" id="countdown"></p>
                <p class="end-payment">Batas Akhir Pembayaran</p>
                <p class="end-date">
                    {{ \Carbon\Carbon::createFromTimestamp($order->expired_time)->translatedFormat('d F Y, H:i') }} WIB
                </p>
            </div>
        @break

        @case(2)
            {{-- PAID --}}
            <div class="mb-3 text-center">
                <div class="rounded-circle bg-success d-inline-flex align-items-center justify-content-center"
                    style="width: 64px; height: 64px;">
                    <i class="fas fa-check-circle fa-2x text-white"></i>
                </div>
                <p class="h5 text-success fw-bold mt-2">Pembayaran Berhasil</p>
            </div>
        @break

        @case(3)
            {{-- EXPIRED --}}
            <div class="mb-3 text-center">
                <div class="rounded-circle bg-secondary d-inline-flex align-items-center justify-content-center"
                    style="width: 64px; height: 64px;">
                    <i class="fas fa-clock fa-2x text-dark"></i>
                </div>
                <p class="h5 text-secondary fw-bold mt-2">Pembayaran Kadaluarsa</p>
            </div>
        @break

        @case(4)
            {{-- CANCEL --}}
            <div class="mb-3 text-center">
                <div class="rounded-circle bg-danger d-inline-flex align-items-center justify-content-center"
                    style="width: 64px; height: 64px;">
                    <i class="fas fa-ban fa-2x text-white"></i>
                </div>
                <p class="h5 text-danger fw-bold mt-2">Pembayaran Dibatalkan</p>
            </div>
        @break

        @default
            <p class="text-muted">Status pembayaran tidak diketahui.</p>
    @endswitch
</div>
