<x-layouts.customers.app-layout title="Order #{{ $order->number }}">

    <div class="card-header py-3">
        <h5 class="m-0 fw-bold text-primary">{{ __('Data Order #') }}{{ $order->number }}</h5>
    </div>
    <div class="card-body p-1 shadow">
        {{-- <div class="d-flex justify-content-center align-items-start"> --}}
        <div class="d-flex flex-column align-items-center w-100">
            <div class="d-flex flex-column border rounded p-3" id="payment-status-wrapper">
                @include('customers.components.payment-status', ['order' => $order])
            </div>

            <div class="info mt-2 {{ $order->status != 1 ? 'is-paid' : '' }}">
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
                    <div class="row">
                        <div class="col-8">
                            + <a href="{{ $order->package ?? '#!' }}" target="_blank">{{ $order->package }}</a>
                            <br>
                            <small class="ms-3 fw-bold" style="color: #17a2b8;">
                                Rp {{ number_format($order->total_price, 0, ',', '.') }} x {{ $order->quantity }}
                            </small>
                        </div>
                        <div class="col-4 text-end fw-bold" style="color: #17a2b8;">
                            <span style="float: left;">Rp</span>
                            <span style="float: right;">{{ number_format($order->total_price, 0, ',', '.') }}</span>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-8">
                            + Biaya Administrasi
                        </div>
                        <div class="col-4 text-end fw-bold" style="color: #17a2b8;">
                            <span style="float: left;">Rp</span>
                            <span style="float: right;">{{ number_format($order->total_fee, 0, ',', '.') }}</span>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-8">
                            + Jumlah Pembayaran
                        </div>
                        <div class="col-4 text-end fw-bold" style="color: #17a2b8;">
                            <span style="float: left;">Rp</span>
                            <span style="float: right;">{{ number_format($order->total_amount, 0, ',', '.') }}</span>
                        </div>
                    </div>
                </div>
                <div class="number-section" id="payment-section">
                    @include('customers.components.payment-section', [
                        'order' => $order,
                        'shouldShow' => $shouldShow,
                        'vaNumber' => $vaNumber,
                    ])
                </div>
            </div>
        </div>
    </div>

    <!-- Tombol untuk membuka modal -->
    <div id="payment-footer">
        @include('customers.components.payment-footer', ['order' => $order])
    </div>

    <!-- Modal QR -->
    <div id="qrModal" class="modal fade" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="statusModalLabel">Detail Pembayaran</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body d-flex justify-content-center">
                    <img src="{{ $order->qr_url }}" alt="QR Besar"
                        style="width: 200px; height: 200px; object-fit: contain;">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-success" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Bootstrap -->
    <div class="modal fade" id="statusModal" tabindex="-1" aria-labelledby="statusModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="statusModalLabel">Detail Pembayaran</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="statusModalBody">
                    <!-- AJAX akan isi bagian ini -->
                    <div class="text-center">
                        <div class="spinner-border text-success" role="status">
                            <span class="visually-hidden">Memuat...</span>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-success" data-bs-dismiss="modal">Tutup</button>
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
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="paymentInstructions">
                    <p>Memuat instruksi pembayaran...</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-success" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    <style>
        .card-body.p-1 {
            padding: 1.25rem !important;
        }

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

        /* Untuk layout full-width saat desktop dan status PAID */
        @media (min-width: 768px) {
            .full-paid-wrapper {
                width: 100vw;
                max-width: 100vw;
                margin-left: -1.5rem;
                /* kompensasi padding container-fluid */
                margin-right: -1.5rem;
                padding-left: 1.5rem;
                padding-right: 1.5rem;
                border-radius: 0 !important;
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
            padding: 7px;
        }

        @media (min-width: 768px) {
            .is-paid {
                width: 100%;
                max-width: 1024px;
            }

            .is-paid .virt-number,
            .is-paid .number-section {
                padding-left: 32px;
                padding-right: 32px;
            }

            .is-paid .row {
                padding-left: 32px;
                padding-right: 32px;
            }
        }

        @media (max-width: 576px) {
            /* .number-section .row>div {
                font-size: 13px;
            }

            .number-section .fw-bold {
                font-size: 14px;
            }

            .number-section .label {
                font-size: 14px;
            }

            .number-section .value {
                font-size: 15px;
                word-break: break-word;
            }

            .number-section {
                padding: 12px;
            }

            .number-section .row {
                margin-bottom: 6px;
            }

            .number-section .text-end {
                text-align: right !important;
            } */

            .number-section {
                padding: 8px 12px;
                /* Lebih rapat kiri-kanan */
            }

            .number-section .row {
                margin-left: 0;
                margin-right: 0;
            }

            .number-section .col-8,
            .number-section .col-4 {
                padding-left: 4px;
                padding-right: 4px;
            }

            /* .number-section .col-4.text-end span {
                font-size: 13px;
            } */
            .number-section .fw-bold span {
                font-size: 13px !important;
            }

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
            flex-wrap: wrap;
            gap: 8px;
            max-width: 100%;
        }

        .detail-virt-number .value {
            font-size: 1.2rem;
            word-break: break-word;
            overflow-wrap: break-word;
            white-space: normal;
            /* Pastikan teks bisa turun baris */
            flex: 1 1 100%;
            /* Supaya ambil 100% lebar saat dibutuhkan */
        }

        @media (max-width: 576px) {
            .detail-virt-number .value {
                font-size: 0.9rem;
                /* Lebih kecil di layar kecil */
            }
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

    <script>
        (function() {
            // Global countdown interval reference
            let countdownInterval = null;

            // Initialize all components
            function initComponents() {
                initCountdown();
                initCopyToClipboard();
                initPaymentInstructions();
                initQrModal();
                initPaymentStatusModal();
            }

            // Countdown function
            function initCountdown() {
                const countdownElement = document.getElementById("countdown");
                const expiredAt = {{ $expiredAt ? $expiredAt : 'null' }}; // Convert to milliseconds

                // Clear any existing interval
                if (countdownInterval) {
                    clearInterval(countdownInterval);
                }

                if (countdownElement && expiredAt) {
                    function updateCountdown() {
                        const now = Date.now();
                        const remaining = Math.max(0, Math.floor((expiredAt - now) / 1000));

                        if (remaining <= 0) {
                            countdownElement.innerText = "Waktu habis!";
                            clearInterval(countdownInterval);
                            return;
                        }

                        const d = Math.floor(remaining / 86400);
                        const h = Math.floor((remaining % 86400) / 3600);
                        const m = Math.floor((remaining % 3600) / 60);
                        const s = remaining % 60;

                        countdownElement.innerText = `${d} hari, ${pad(h)} jam, ${pad(m)} menit, ${pad(s)} detik`;
                    }

                    const pad = num => ('0' + num).slice(-2);
                    countdownInterval = setInterval(updateCountdown, 1000);
                    updateCountdown();
                }
            }

            // Copy to clipboard
            function initCopyToClipboard() {
                window.copyToClipboard = function() {
                    const va = document.getElementById("va-number");
                    if (!va) return;

                    navigator.clipboard.writeText(va.innerText)
                        .then(() => {
                            Swal.fire({
                                icon: "success",
                                title: "Berhasil!",
                                text: "Nomor Virtual Account berhasil disalin!",
                                confirmButtonColor: "#3085d6"
                            });
                        })
                        .catch(err => {
                            console.error("Copy failed:", err);
                            // Fallback to old method if clipboard API fails
                            const temp = document.createElement("input");
                            temp.value = va.innerText;
                            document.body.appendChild(temp);
                            temp.select();
                            document.execCommand("copy");
                            document.body.removeChild(temp);

                            Swal.fire({
                                icon: "success",
                                title: "Berhasil!",
                                text: "Nomor Virtual Account berhasil disalin!",
                                confirmButtonColor: "#3085d6"
                            });
                        });
                };
            }

            // Load instructions
            function initPaymentInstructions() {
                const btnInstruction = document.getElementById("paymentInstructionBtn");
                if (btnInstruction) {
                    btnInstruction.addEventListener("click", function() {
                        const orderId = "{{ $order->id }}";
                        const url = `{{ url('customer/billings/orders') }}/${orderId}/instruction`;

                        fetch(url)
                            .then(r => r.json())
                            .then(res => {
                                const wrapper = document.getElementById("paymentInstructions");
                                if (!wrapper) return;

                                if (!res?.data?.length) {
                                    wrapper.innerHTML =
                                        `<p class="text-danger">Tidak ada instruksi pembayaran.</p>`;
                                    return;
                                }

                                const html = res.data.map((method, i) => `
                                <div class="card">
                                    <div class="card-header">
                                        <button class="btn btn-link" data-bs-toggle="collapse" data-bs-target="#collapse${i}">
                                            ${method.title}
                                        </button>
                                    </div>
                                    <div id="collapse${i}" class="collapse ${i === 0 ? 'show' : ''}">
                                        <div class="card-body">
                                            <ol>${method.steps.map(s => `<li>${s}</li>`).join("")}</ol>
                                        </div>
                                    </div>
                                </div>
                            `).join("");

                                wrapper.innerHTML = html;
                            })
                            .catch(err => {
                                console.error("Fetch error:", err);
                                const wrapper = document.getElementById("paymentInstructions");
                                if (wrapper) {
                                    wrapper.innerHTML =
                                        `<p class="text-danger">Gagal memuat instruksi pembayaran.</p>`;
                                }
                            });
                    });
                }
            }

            // QR Modal functions
            function initQrModal() {
                window.openQrModal = () => document.getElementById('qrModal')?.classList.remove('hidden');
                window.closeQrModal = () => document.getElementById('qrModal')?.classList.add('hidden');

                window.downloadQr = () => {
                    const imageUrl = '{{ $order->qr_url ?? '' }}';
                    if (!imageUrl) return;

                    fetch(imageUrl)
                        .then(res => res.blob())
                        .then(blob => {
                            const url = URL.createObjectURL(blob);
                            const a = document.createElement('a');
                            a.href = url;
                            a.download = 'qr-code-pembayaran.png';
                            document.body.appendChild(a);
                            a.click();
                            document.body.removeChild(a);
                            URL.revokeObjectURL(url);
                        })
                        .catch(() => Swal.fire({
                            icon: 'error',
                            title: 'Gagal',
                            text: 'Gagal mendownload QR code'
                        }));
                };
            }

            // Payment status modal
            function initPaymentStatusModal() {
                $(document).on('click', '.btn-how-payment', function() {
                    const orderId = $(this).data('order-id');
                    const url = `/customer/billings/orders/${orderId}/status`;

                    $('#statusModalBody').html(`
            <div class="text-center">
                <div class="spinner-border text-success" role="status">
                    <span class="visually-hidden">Memuat...</span>
                </div>
            </div>
        `);
                    $('#statusModal').modal('show');

                    $.get(url)
                        .done(function(data) {
                            $('#payment-status-wrapper').html(data.html);
                            $('#statusModalBody').html(data.modal);
                            $('#payment-footer').html(data.footer);

                            // Hide payment section if status != 1
                            if (data.payment_status != 1) {
                                $('#payment-section').remove();
                            }

                            // Reinitialize components after AJAX update
                            initCountdown();
                        })
                        .fail(function() {
                            $('#statusModalBody').html(
                                '<div class="alert alert-danger">Gagal memuat detail status pembayaran.</div>'
                            );
                        });
                });
            }

            // Initialize all components when page loads
            initComponents();
        })();
    </script>

</x-layouts.customers.app-layout>
