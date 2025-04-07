<x-layouts.customers.app-layout title="Billing #{{ $billing->billing_number }}">

    <div class="card-header py-3">
        <h5 class="m-0 font-weight-bold text-primary">{{ __('Bill Number #') }}{{ $billing->billing_number }}</h5>
    </div>

    <x-billing-info :billing="$billing" :websystem="$websystem" />

    @if ($billing->status !== 'LS')

        @php
            $channels = collect($channels);
        @endphp

        {{-- Metode Pembayaran belum dipilih --}}
        @if (empty($order->payment_method))
            <form method="POST" action="{{ route('customer.tripays.order.create', $billing->id) }}">
                @csrf
                {{-- Tampilkan channel pembayaran --}}
                @if ($channels->isNotEmpty())
                    <div class="col-12 mb-3">
                        <div class="d-flex justify-content-between align-items-center alert alert-info mb-0">
                            <div>
                                Nikmati kemudahan pembayaran melalui beberapa channel pembayaran
                            </div>
                        </div>
                    </div>

                    <x-payment-channel-list :channels="$channels" />
                @endif

                <div class="card-footer">
                    @if ($billing->status == 'BL' && $websystem->payment_gateway == 'enable')
                        <form method="POST" action="{{ route('customer.tripays.order.create', $billing->id) }}">
                            @csrf
                            <!-- Hidden input untuk payment_channel -->
                            <input type="hidden" name="payment_channel" value="{{ $gateway->slug }}">
                            <button type="submit" class="order-button btn btn-success">
                                <i class="fas fa-shield-alt"></i> {{ __('billing.button.create-order') }}
                            </button>
                        </form>
                        <p></p>
                        <p><i>Untuk pembayaran online klik {{ __('billing.button.create-order') }}</i></p>
                    @endif
                </div>
            </form>

            {{-- Jika ada order dan payment_status belum lunas (tidak sama dengan 2) --}}
        @elseif (
            $order->payment_status != \App\Models\Order::STATUS_PAID &&
                $order->payment_status != \App\Models\Order::STATUS_EXPIRED)
            {{-- Tampilkan komponen aksi pembayaran --}}
            <x-payment-action :order="$order" :gateway="$gateway" :websystem="$websystem" />
        @elseif ($order->payment_status == \App\Models\Order::STATUS_EXPIRED)
            <form method="POST" action="{{ route('customer.tripays.order.create', $billing->id) }}">
                @csrf
                {{-- Tampilkan kembali channel pembayaran --}}
                @if ($channels->isNotEmpty())
                    <div class="col-12 mb-3">
                        <div class="d-flex justify-content-between align-items-center alert alert-info mb-0">
                            <div>
                                Metode pembayaran Anda sebelumnya menggunakan
                                <strong>{{ $order->payment_name }}</strong> telah
                                <strong>kadaluwarsa</strong>,
                                silakan memilih metode pembayaran yang baru.
                            </div>
                        </div>
                    </div>

                    <x-payment-channel-list :channels="$channels" />
                @endif

                <div class="card-footer">
                    @if ($billing->status == 'BL' && $websystem->payment_gateway == 'enable')
                        <!-- Hidden input untuk payment_channel -->
                        <input type="hidden" name="payment_channel" value="{{ $gateway->slug }}">
                        <button type="submit" class="order-button btn btn-success">
                            <i class="fas fa-shield-alt"></i> {{ __('billing.button.create-order') }}
                        </button>
                        <p></p>
                        <p><i>Untuk pembayaran online klik {{ __('billing.button.create-order') }}</i></p>
                    @endif
                </div>
            </form>
        @endif

    @endif

    <!-- Hidden Input untuk icon_url -->
    <input type="hidden" name="icon_url" id="icon_url">

    @section('custom_scripts')
        @if ($message = Session::get('success'))
            <script>
                Swal.fire({
                    icon: 'success',
                    text: '{{ $message }}',
                })
            </script>
        @endif

        {{-- <script>
            document.querySelector("form").addEventListener("submit", function(event) {
                let hiddenIconUrl = document.getElementById("icon_url").value;
                if (!hiddenIconUrl) {
                    event.preventDefault(); // Cegah submit jika kosong
                    alert("Silakan pilih metode pembayaran terlebih dahulu.");
                }
            });
        </script> --}}
    @endsection

    <style>
        .status-indicator {
            display: inline-block;
            padding: 5px 15px;
            border-radius: 12px;
            font-weight: bold;
            color: white;
            text-transform: uppercase;
            font-size: 9px;
        }

        .status-indicator.on {
            background-color: #31ce77;
            /* Hijau */
        }

        .status-indicator.off {
            background-color: #e74c3c;
            /* Merah */
        }

        .quote {
            font-size: 90%;
            padding: .2rem .5rem .2rem .5rem;
            border-left: 2px solid #c1c1c1;
            /* font-style: italic; */
            text-align: justify;
            font-size: 13px;
        }

        .mt-3,
        .my-3 {
            margin-top: 1.5rem !important;
        }

        .mb-0,
        .my-0 {
            margin-bottom: 0 !important;
        }

        .mb-4,
        .my-4 {
            margin-bottom: 0.25rem !important;
        }

        .shadow {
            border: 2px solid #ddd;
            border-radius: 4px;
            /* padding: 15px; */
            box-shadow: #ddd 0px 0px 5px 1px !important;
        }

        .card {
            position: relative !important;
            display: -webkit-box !important;
            display: -ms-flexbox !important;
            display: flex !important;
            -webkit-box-orient: vertical !important;
            -webkit-box-direction: normal !important;
            -ms-flex-direction: column !important;
            flex-direction: column !important;
            min-width: 0 !important;
            word-wrap: break-word !important;
            background-color: #fff !important;
            background-clip: border-box !important;
            border: 0 solid #f7f7f7 !important;
            border-radius: .25rem !important;
        }

        .align-items-stretch {
            -webkit-box-align: stretch !important;
            -ms-flex-align: stretch !important;
            align-items: stretch !important;
        }

        .payment-name {
            float: left;
            font-weight: bold;
            color: #000;
            font-size: 95%;
        }

        .order-button {
            display: block;
            width: 100%;
            text-align: center;
            background-color: #28a745;
            /* Warna hijau */
            color: white;
            /* Warna teks tetap putih */
            padding: 10px 15px;
            border-radius: 5px;
            text-decoration: none;
            font-size: 14px;
            font-weight: bold;
        }

        /* Efek saat dihover */
        .order-button:hover {
            font-weight: bolder;
            /* Buat lebih tebal */
            color: white;
            /* Mencegah perubahan warna */
        }

        /* Desktop: Tombol jadi kecil dan di kanan */
        @media (min-width: 768px) {
            .order-button {
                display: inline-block;
                width: auto;
                float: right;
            }
        }
    </style>
</x-layouts.customers.app-layout>
