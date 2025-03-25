<x-layouts.customers.app-layout title="Order #{{ $order->number }}">

    @php
        $expiredAt = $order->expired_time * 1000; // Konversi ke milidetik
    @endphp

    <div class="card-header py-3">
        <h5 class="m-0 font-weight-bold text-primary">{{ __('Data Order #') }}{{ $order->number }}</h5>
    </div>
    <div class="card-body p-1 shadow">
        {{-- <div class="row"> --}}
        <!-- Bagian Bayar Sebelum -->
        {{-- <div class="mb-3 mb-md-0">
            <div class="p-3 border rounded"> --}}
        <div class="d-flex justify-content-center align-items-center">
            <div class="py-3 px-3 border rounded w-100" style="max-width: 500px;">
                <div class="text-center d-flex flex-column align-items-center gap-0">
                    <p class="head-section">Selesaikan pembayaran dalam</b>
                    </p>
                    <p class="timer text-danger font-weight-bold" id="countdown"></p>
                    <p class="end-payment">Batas Akhir Pembayaran</p>
                    <p class="end-date">
                        {{ \Carbon\Carbon::createFromTimestamp($order->expired_time)->translatedFormat('d F Y, H:i') }}
                        WIB
                    </p>
                </div>

                <div class="info mt-2">
                    <div class="virt-number">
                        <p class="label">{{ $order->payment_name }}</p>
                        <img class="image-logo" src="{{ $order->icon_url }}" alt="{{ $order->payment_name }}">
                    </div>
                    <div class="number-section">
                        <p class="label">Nama Pelanggan</p>
                        <div class="detail-virt-number">
                            <p class="value">{{ $order->customer->name }}</p>
                        </div>
                        <p class="label">No. HP</p>
                        <div class="detail-virt-number">
                            <p class="value">{{ preg_replace('/^628/', '08', $order->customer->contact) }}</p>
                        </div>
                        <p class="label">Email</p>
                        <div class="detail-virt-number">
                            <p class="value">{{ $order->customer->email }}</p>
                        </div>
                        <p class="label">Rincian Transaksi</p>
                        {{-- @foreach ($reqTransaction['order_items'] as $item) --}}
                        <div class="row">
                            <div class="col-8">
                                + <a href="{{ $order->package ?? '#!' }}" target="_blank">{{ $order->package }}</a>
                                <br>
                                <small class="ml-3 font-weight-bold" style="color: #17a2b8;">
                                    Rp {{ number_format($order->total_price, 0, ',', '.') }} x {{ $order->quantity }}
                                </small>
                            </div>
                            <div class="col-4 text-right font-weight-bold" style="color: #17a2b8;">
                                Rp {{ number_format($order->total_price, 0, ',', '.') }}
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-8">
                                + Biaya Administrasi
                            </div>
                            <div class="col-4 text-right font-weight-bold" style="color: #17a2b8;">
                                Rp {{ number_format($order->total_fee, 0, ',', '.') }}
                            </div>
                        </div>
                        {{-- @endforeach --}}
                    </div>
                    <div class="number-section">
                        <div class="tot-tagihan">
                            <p class="label">Nomor Referensi</p>
                            <div class="detail-virt-number">
                                <p class="value">{{ $order->number }}</p>
                            </div>

                        </div>
                        <p class="label">Kode Bayar/Nomor VA</p>
                        <div class="detail-virt-number">
                            <p class="value" id="va-number">{{ $order->va_number }}</p>
                            <div class="css-ee7rfg" data-testid="copy-content" onclick="copyToClipboard()">
                                Salin <img src="/images/salin.svg" alt="Copy Icon" width="18" height="18">
                            </div>
                        </div>
                        <div class="tot-tagihan">
                            <p class="label">Jumlah Pembayaran</p>
                            <div class="detail-virt-number">
                                <p class="value">Rp {{ number_format($order->total_amount, 0, ',', '.') }}</p>
                            </div>

                        </div>
                        <div class="note text-muted">
                            <p><b>Penting:</b></p>
                            <ol>
                                <li>Transfer Virtual Account hanya bisa dilakukan dari <b>bank yang kamu pilih</b></li>
                                <li>Pastikan Anda melakukan pembayaran sebelum melewati batas pembayaran dan dengan
                                    nominal yang tepat</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        {{-- </div> --}}
    </div>

    {{-- <div class="card-footer">
        @if ($order->payment_status == 1)
            <button class="btn" id="pay-button"><i class="far fa-money-bill-alt"></i> Cek Status
                Pembayaran</button>
        @elseif ($order->payment_status == 2)

        @elseif ($order->payment_status == 3)
        @endif
    </div> --}}

    <!-- Tombol untuk membuka modal -->
    <div class="card-footer-custom">
        @if ($order->payment_status == 1)
            <button class="btn btn-outline-success m-2 btn-how-payment" data-toggle="modal"
                data-target="#statusModal">Lihat Detail
            </button>
            <button class="btn btn-success m-2 btn-custom" id="paymentInstructionBtn" data-toggle="modal"
                data-target="#howToPaymentModal">
                Cara Pembayaran
            </button>
        @endif
    </div>

    <!-- Modal Bootstrap -->
    <div class="modal fade" id="statusModal" tabindex="-1" aria-labelledby="statusModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="statusModalLabel">Detail Pembayaran</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <table class="table table-hover table-bordered">
                        <tr>
                            <td>ID</td>
                            <td><b>#{{ $order->number }}</b></td>
                        </tr>
                        <tr>
                            <td>Nama</td>
                            <td><b>{{ $order->billing->customer_name }}</b></td>
                        </tr>
                        <tr>
                            <td>Periode</td>
                            <td><b>{{ $order->billing->billing_periode->name }}</b></td>
                        </tr>
                        <tr>
                            <td>Paket</td>
                            <td><b>{{ $order->billing->paket_name }}</b></td>
                        </tr>
                        <tr>
                            <td>Harga</td>
                            <td><b>@moneyIDR($order->total_price)</b></td>
                        </tr>
                        <tr>
                            <td>Status Pembayaran</td>
                            <td><b>
                                    @if ($order->payment_status == 1)
                                        {{ __('billing.status.waiting-payment') }}
                                    @elseif ($order->payment_status == 2)
                                        {{ __('billing.status.paid-off') }}
                                    @elseif ($order->payment_status == 3)
                                        {{ __('billing.status.expired') }}
                                    @else
                                        {{ __('billing.status.cancelled') }}
                                    @endif
                                </b></td>
                        </tr>
                        <tr>
                            <td>Tanggal</td>
                            <td><b>{{ $order->created_at->format('d M Y H:i') }}</b></td>
                        </tr>
                    </table>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-success" data-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="howToPaymentModal" tabindex="-1" aria-labelledby="modalTitle" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Instruksi Pembayaran</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body" id="paymentInstructions">
                    <p>Memuat instruksi pembayaran...</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-success" data-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        var expiredAt = {{ $expiredAt }}; // Waktu order kedaluwarsa dalam milidetik

        function updateCountdown() {
            var now = Date.now(); // Waktu sekarang dalam milidetik
            var remainingTime = Math.max(0, Math.floor((expiredAt - now) / 1000)); // Sisa waktu dalam detik

            if (remainingTime <= 0) {
                document.getElementById("countdown").innerText = "Waktu habis!";
                clearInterval(countdownInterval); // Hentikan interval saat waktu habis
                return;
            }

            var days = Math.floor(remainingTime / (24 * 3600));
            var hours = Math.floor((remainingTime % (24 * 3600)) / 3600);
            var minutes = Math.floor((remainingTime % 3600) / 60);
            var seconds = remainingTime % 60;

            document.getElementById("countdown").innerText =
                days + " hari, " +
                ('0' + hours).slice(-2) + " jam, " +
                ('0' + minutes).slice(-2) + " menit, " +
                ('0' + seconds).slice(-2) + " detik";
        }

        var countdownInterval = setInterval(updateCountdown, 1000);
        updateCountdown(); // Jalankan langsung saat halaman dimuat

        // copy to clipboard
        function copyToClipboard() {
            var copyText = document.getElementById("va-number").innerText;

            // Buat elemen input sementara
            var tempInput = document.createElement("input");
            tempInput.value = copyText;
            document.body.appendChild(tempInput);

            // Pilih dan salin teks
            tempInput.select();
            tempInput.setSelectionRange(0, 99999); // Untuk perangkat mobile
            document.execCommand("copy");

            // Hapus elemen input sementara
            document.body.removeChild(tempInput);

            // Tampilkan notifikasi dengan SweetAlert
            Swal.fire({
                icon: "success",
                title: "Berhasil!",
                text: "Nomor Virtual Account berhasil disalin!",
                confirmButtonColor: "#3085d6",
                confirmButtonText: "OK"
            });
        }

        document.getElementById("paymentInstructionBtn").addEventListener("click", function() {
            var orderId = "{{ $order->id }}"; // Pastikan orderId sudah tersedia di Blade template
            var url = "{{ url('customer/billings/orders') }}/" + orderId + "/instruction";

            fetch(url)
                .then(response => response.json()) // Konversi ke JSON
                .then(data => {
                    if (!data || !data.data || data.data.length === 0) {
                        document.getElementById("paymentInstructions").innerHTML =
                            `<p class="text-danger">Tidak ada instruksi pembayaran.</p>`;
                        return;
                    }

                    let instructions = data.data; // Ambil array dari `data.data`
                    let content = "";

                    instructions.forEach((method, index) => {
                        let isActive = index === 0 ? "show" : ""; // Hanya item pertama yang terbuka

                        content += `
                    <div class="card">
                        <div class="card-header">
                            <button class="btn btn-link" data-toggle="collapse" data-target="#collapse${index}">
                                ${method.title}
                            </button>
                        </div>
                        <div id="collapse${index}" class="collapse ${isActive}">
                            <div class="card-body">
                                <ol>
                                    ${method.steps.map(step => `<li>${step}</li>`).join("")}
                                </ol>
                            </div>
                        </div>
                    </div>
                `;
                    });

                    document.getElementById("paymentInstructions").innerHTML = content;
                })
                .catch(error => {
                    console.error("Fetch error:", error);
                    document.getElementById("paymentInstructions").innerHTML =
                        `<p class="text-danger">Gagal memuat instruksi pembayaran.</p>`;
                });
        });
    </script>

    <style>
        .table-bordered {
            border: none !important;
            /* Hilangkan border di tabel */
        }

        .table-bordered td,
        .table-bordered th {
            border: none !important;
            /* Hilangkan garis antar sel */
        }

        .border {
            border: none !important;
            /* Hilangkan border di div tertentu */
        }

        .p-3.border.rounded {
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            /* Bagi ruang antara elemen */
            height: 100%;
            /* Pastikan div memenuhi tinggi parent */
        }

        .info {
            border: 1px solid var(--NN50, #F0F3F7);
            border-radius: 8px;
            margin-top: 40px;
        }

        .info p {
            margin: 5px 0;
        }

        .info .label {
            color: var(--NN600, #6D7588);
        }

        .info .value {
            font-weight: bold;
            font-size: 1rem;
            /* Default ukuran */
            line-height: 20px;
            color: var(--NN950, #212121);
        }

        /* Saat layar lebih kecil dari 768px (tablet & mobile) */
        @media (max-width: 768px) {
            .info .value {
                font-size: 0.875rem;
                /* Kecilkan ukuran */
                line-height: 18px;
            }
        }

        /* Saat layar lebih kecil dari 480px (mobile kecil) */
        @media (max-width: 480px) {
            .info .value {
                font-size: 0.75rem;
                /* Ukuran lebih kecil untuk layar kecil */
                line-height: 16px;
            }
        }

        .note {
            font-size: 12px;
            color: #555;
            text-align: left;
        }

        .wrapper {
            max-width: 90vw;
            /* Maksimum 90% dari lebar layar */
            margin: auto;
            /* Agar selalu di tengah */
            padding: 1rem;
            /* Biar tidak terlalu mepet */
        }

        .head-section {
            font-size: 1.42857rem;
            font-weight: 800;
            color: var(--NN950, #212121);
        }

        .timer {
            display: block;
            position: relative;
            font-family: "Open Sauce One", "Nunito Sans", -apple-system, sans-serif;
            line-height: 20px;
            letter-spacing: 0px;
            text-decoration: initial;
            color: var(--YN500, #FF7F17);
            font-size: 1.42857rem;
            font-weight: 800;
            margin: 8px 0px 16px;
        }

        .end-payment {
            display: block;
            position: relative;
            font-weight: 400;
            font-family: "Open Sauce One", "Nunito Sans", -apple-system, sans-serif;
            line-height: 20px;
            letter-spacing: 0px;
            color: var(--NN600, #6D7588);
            text-decoration: initial;
            margin: 0px 0px 8px;
            font-size: 1.14286rem;
        }

        .end-date {
            display: block;
            position: relative;
            font-family: "Open Sauce One", "Nunito Sans", -apple-system, sans-serif;
            line-height: 20px;
            letter-spacing: 0px;
            color: var(--YN400, #FFA617);
            text-decoration: initial;
            margin: 0px;
            font-size: 1.28571rem;
            font-weight: 800;
        }

        .virt-number {
            display: flex;
            -webkit-box-pack: justify;
            justify-content: space-between;
            -webkit-box-align: center;
            align-items: center;
            border-bottom: 0.5px solid var(--NN50, #F0F3F7);
            padding: 16px;
        }

        .number-section {
            padding: 16px;
        }

        .label {
            color: var(--NN950, #212121);
            font-size: 14px;
            font-weight: unset;
            line-height: 20px;
            margin: 0.25rem 0px;
            text-align: unset;
        }

        .image-logo {
            max-width: 64px;
            max-height: 40px;
            vertical-align: middle;
        }

        .detail-virt-number {
            display: flex;
            align-items: center;
            justify-content: space-between;
            /* Memisahkan nomor dan tombol salin */
            flex-wrap: wrap;
            /* Agar elemen turun ke bawah jika tidak cukup */
            gap: 8px;
            /* Jarak antar elemen */
            max-width: 100%;
            /* Pastikan tidak keluar dari parent */
        }

        .detail-virt-number .value {
            font-size: 1.2rem;
            word-break: break-word;
            /* Mencegah overflow */
            overflow-wrap: break-word;
        }

        .css-ee7rfg {
            display: flex;
            align-items: center;
            gap: 4px;
            color: var(--G500, #03AC0E);
            font-size: 0.857143rem;
            text-decoration: none;
            cursor: pointer;
            /* Jarak antara teks "Salin" dan ikon */
        }

        @media (max-width: 480px) {
            .detail-virt-number {
                flex-direction: row;
                /* Tetap sejajar */
                flex-wrap: nowrap;
                /* Jangan biarkan turun ke bawah */
                align-items: center;
                /* Pastikan vertikal tetap sejajar */
                justify-content: space-between;
                gap: 4px;
                /* Jarak lebih kecil di mobile */
            }

            .detail-virt-number .value {
                font-size: 0.875rem;
                /* Sesuaikan ukuran */
                overflow: hidden;
                text-overflow: ellipsis;
                /* Jika kepanjangan, jadi "..." */
                white-space: nowrap;
                /* Jangan turun ke bawah */
                max-width: 70%;
                /* Batasi panjang agar "Salin" tetap di samping */
            }

            .css-ee7rfg {
                flex-shrink: 0;
                /* Jangan mengecil saat layar kecil */
            }
        }

        .tot-tagihan {
            padding: 16px 0px;
        }

        .view-detail {
            line-height: 20px;
            margin: 0.25rem 0px;
            text-align: unset;
            color: var(--GN500, #00AA5B);
            cursor: pointer;
            font-size: 1.14286rem;
            font-weight: 800;
        }

        .container-payment {
            width: 50% !important;
            padding-right: 0.75rem !important;
            padding-left: 0.75rem !important;
            margin-right: auto !important;
            margin-left: auto !important;
        }

        /* Responsif untuk tablet */
        @media (max-width: 768px) {
            .container-payment {
                width: 75% !important;
                /* Lebih lebar pada tablet */
            }
        }

        /* Responsif untuk smartphone */
        @media (max-width: 480px) {
            .container-payment {
                width: 90% !important;
                /* Hampir full-width pada mobile */
            }
        }

        .card-footer-custom {
            display: flex;
            justify-content: center;
            gap: 8px;
            background-color: #f8f9fc;
            border-top: 1px solid #e3e6f0;
            padding: 10px;
        }

        .btn-custom {
            margin: 0;
            background-color: var(--GN500, #00AA5B);
            border: none;
            border-radius: 8px;
            color: rgb(255, 255, 255);
            cursor: pointer;
            display: inline-block;
            font-family: inherit;
            font-weight: 800;
            font-size: 0.857143rem;
            height: 40px;
            line-height: 24px;
            outline: none;
            position: relative;
            padding: 0px 16px;
            text-indent: initial;
            transition: background-color 300ms;
            width: auto;
            margin-left: 12px;
            min-width: 146px;
        }

        .btn-how-payment {
            background-color: var(--NN0, #FFFFFF);
            border: 1px solid var(--GN500, #00AA5B);
            border-radius: 8px;
            color: var(--GN500, #00AA5B);
            cursor: pointer;
            display: inline-block;
            font-family: inherit;
            font-weight: 800;
            font-size: 0.857143rem;
            height: 40px;
            line-height: 24px;
            outline: none;
            position: relative;
            padding: 0px 16px;
            text-indent: initial;
            transition: background-color 300ms;
            width: auto;
            min-width: 146px;
            margin: 0;
        }
    </style>

</x-layouts.customers.app-layout>
