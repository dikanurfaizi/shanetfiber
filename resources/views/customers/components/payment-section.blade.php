@if ($order->payment_status == 1)
    <div class="tot-tagihan">
        <p class="label">Nomor Referensi</p>
        <div class="detail-virt-number">
            <p class="value">{{ $order->number }}</p>
        </div>
    </div>
    @if ($shouldShow)
        <p class="label">
            {{ Str::startsWith($vaNumber, '08') ? 'Masukkan nomor ' . $order->payment_name : 'Kode Bayar/Nomor VA' }}
        </p>
        <div class="detail-virt-number">
            <p class="value" id="va-number">{{ $vaNumber }}</p>
            <div class="css-ee7rfg" data-testid="copy-content" onclick="copyToClipboard()">
                Salin <img src="/images/salin.svg" alt="Copy Icon" width="18" height="18">
            </div>
        </div>
    @endif

    @if (in_array($order->payment_method, ['QRIS', 'QRISC', 'QRIS2']))
        <div class="tot-tagihan text-center">
            <p class="label">* Klik untuk memperbesar atau download kode QR</p>

            <div class="detail-virt-number d-flex justify-content-center align-items-center">
                <img src="{{ $order->qr_url }}" alt="QR Code Pembayaran"
                    style="width: 160px; height: 160px; object-fit: contain; cursor: pointer; transition: transform 0.2s;"
                    data-bs-toggle="modal" data-bs-target="#qrModal" onmouseover="this.style.transform='scale(1.05)'"
                    onmouseout="this.style.transform='scale(1)'">
            </div>

            <a href="{{ $order->qr_url }}" download="qr-code.png" class="btn btn-primary mt-3">
                Download QR Code
            </a>
        </div>
    @endif

    <div class="note text-muted mt-4">
        <p><b>Penting:</b></p>
        <ol>
            <li>Transfer Virtual Account hanya bisa dilakukan dari <b>bank yang kamu pilih</b></li>
            <li>Pastikan Anda melakukan pembayaran sebelum melewati batas pembayaran dan dengan nominal yang tepat
            </li>
        </ol>
    </div>
@endif
