<x-layouts.customers.app-layout title="Billing #{{ $billing->billing_number }}">

    <form method="POST"
        action="{{ route($websystem->payment_channel === 'tripays' ? 'customer.tripays.order.create' : 'customer.order.create', $billing->id) }}">
        @csrf
        <div class="card-header py-3">
            <h5 class="m-0 font-weight-bold text-primary">{{ __('Bill Number #') }}{{ $billing->billing_number }}</h5>
        </div>

        <!-- Hidden input untuk payment_channel -->
        <input type="hidden" name="payment_channel" value="{{ $websystem->payment_channel }}">

        <div class="table-responsive">
            <table class="table table-hover table-condensed">
                <tr>
                    <td>ID</td>
                    <td><b>#{{ $billing->billing_number }}</b></td>
                </tr>
                <tr>
                    <td>Nama</td>
                    <td><b>{{ $billing->customer_name }}</b></td>
                </tr>
                <tr>
                    <td>Periode</td>
                    <td><b>{{ $billing->billing_periode->name }}</b></td>
                </tr>
                <tr>
                    <td>Paket</td>
                    <td><b>{{ $billing->paket_name }}</b></td>
                </tr>
                @if ($websystem->enable_tax === 'true')
                    <tr>
                        <td>Harga</td>
                        <td><b>@moneyIDR($billing->paket_price)</b></td>
                    </tr>
                    <tr>
                        <td>PPN </td>
                        <td><b>@moneyIDR($billing->tax)</b></td>
                    </tr>
                @endif
                <tr>
                    <td>Total Price </td>
                    <td><b>@moneyIDR($billing->paket_price + $billing->tax)</b></td>
                </tr>

                <tr>
                    <td>Status Pembayaran</td>
                    <td><b>
                            @if ($billing->status == 'BL')
                                {{ __('billing.state.belum-lunas') }}
                            @else
                                {{ __('billing.state.lunas') }}
                            @endif
                        </b></td>
                </tr>
                <tr>
                    <td>Tanggal</td>
                    <td><b>{{ $billing->created_at->format('d M Y H:i') }}</b></td>
                </tr>
            </table>
        </div>
        @php
            $channels = collect($channels);
        @endphp

        @if ($channels->isNotEmpty())
            <div class="col-12 mb-3">
                <div class="d-flex justify-content-between align-items-center alert alert-info mb-0">
                    <div>
                        Nikmati kemudahan pembayaran melalui beberapa channel pembayaran
                    </div>
                </div>
            </div>
            @php
                $vaChannels = $channels->where('group', 'Virtual Account');
                $csChannels = $channels->where('group', 'Convenience Store');
                $ewalletChannels = $channels->where('group', 'E-Wallet');
            @endphp

            @if ($vaChannels->isNotEmpty())
                <x-payment-channel title="Virtual Account" group="Virtual Account" :channels="$vaChannels" />
            @endif

            @if ($csChannels->isNotEmpty())
                <x-payment-channel title="Gerai Offline" group="Convenience Store" :channels="$csChannels" />
            @endif

            @if ($ewalletChannels->isNotEmpty())
                <x-payment-channel title="Dompet Digital" group="E-Wallet" :channels="$ewalletChannels" />
            @endif
        @endif

        <div class="card-footer">
            @if ($billing->status == 'BL' && $websystem->payment_gateway == 'enable')
                <button type="submit" class="order-button btn btn-success">
                    <i class="fas fa-shield-alt"></i> {{ __('billing.button.create-order') }}
                </button>
                <p></p>
                <p><i>Untuk pembayaran online klik {{ __('billing.button.create-order') }}</i></p>
            @endif
        </div>
    </form>

    @section('custom_scripts')
        @if ($message = Session::get('success'))
            <script>
                Swal.fire({
                    icon: 'success',
                    text: '{{ $message }}',
                })
            </script>
        @endif
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
