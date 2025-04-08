<x-layouts.customers.app-layout title="Billing #{{ $billing->billing_number }}">

    <div class="card-header py-3">
        <h5 class="m-0 font-weight-bold text-primary">{{ __('Bill Number #') }}{{ $billing->billing_number }}</h5>
    </div>

    <x-billing-info :billing="$billing" :websystem="$websystem" />

    @if ($billing->status !== 'LS')
        @php
            Log::info("Billing Status: {$billing->status} not LS");
        @endphp
        @if (!$order)
            {{-- Case: Order belum pernah dibuat --}}
            @include('partials.create-order-form', [
                'channels' => $channels,
                'billing' => $billing,
                'websystem' => $websystem,
                'gateway' => $gateway,
            ])
        @elseif (in_array($order->payment_status, [\App\Models\Order::STATUS_CANCELED]))
            @php
                Log::info('Customer membuka halaman ulang karena order sebelumnya dibatalkan.');
            @endphp
            @include('partials.create-order-form', [
                'channels' => $channels,
                'billing' => $billing,
                'websystem' => $websystem,
                'gateway' => $gateway,
            ])
        @elseif (in_array($order->payment_status, [\App\Models\Order::STATUS_UNPAID]))
            @php
                Log::info('Order masih UNPAID. Menampilkan komponen aksi pembayaran.');
            @endphp
            <x-payment-action :order="$order" :gateway="$gateway" :websystem="$websystem" />
        @elseif (in_array($order->payment_status, [\App\Models\Order::STATUS_EXPIRED]))
            @php
                Log::info('Order kadaluwarsa. Menampilkan ulang channel pembayaran.');
            @endphp
            @include('partials.expired-order-form', [
                'channels' => $channels,
                'billing' => $billing,
                'websystem' => $websystem,
                'gateway' => $gateway,
                'order' => $order,
            ])
        @endif
    @endif

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
