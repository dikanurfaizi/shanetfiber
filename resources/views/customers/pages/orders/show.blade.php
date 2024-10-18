<x-layouts.customers.app-layout title="Order #{{ $order->number }}">

    <div class="card-header py-3">
        <h5 class="m-0 font-weight-bold text-primary">{{ __('Data Order #') }}{{ $order->number }}</h5>
    </div>

    <div class="table-responsive">
        <table class="table table-hover table-condensed">
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
                <td><b>@moneyIDR( $order->total_price)</b></td>
            </tr>
            <tr>
                <td>Status Pembayaran</td>
                <td><b>
                        @if ( $order->payment_status == '1')
                        {{ __('billing.status.waiting-payment') }}
                        @elseif ( $order->payment_status == 2)
                        {{ __('billing.status.paid-off') }}
                        @elseif ( $order->payment_status == 3)
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
    <div class="card-footer">
        @if ( $order->payment_status == 1)
        <button class="btn btn-primary" id="pay-button"><i class="far fa-money-bill-alt"></i> Bayar Sekarang</button>
        @elseif ( $order->payment_status == 2)
        @elseif ( $order->payment_status == 3)
        @endif
    </div>


    @section('custom_scripts')
    <script src="{{ $midtransJs }}" data-client-key="{{ $clientKey }}"></script>


    <script>
        const payButton = document.querySelector('#pay-button');
        payButton.addEventListener('click', function(e) {
            e.preventDefault();

            snap.pay('{{ $snapToken }}', {
                // Optional
                onSuccess: function(result) {
                    /* You may add your own js here, this is just example */
                    // document.getElementById('result-json').innerHTML += JSON.stringify(result, null, 2);
                    console.log(result);
                    //send_response_to_form(result);
                },
                // Optional
                onPending: function(result) {
                    /* You may add your own js here, this is just example */
                    // document.getElementById('result-json').innerHTML += JSON.stringify(result, null, 2);
                    console.log(result);
                    // send_response_to_form(result);
                },
                // Optional
                onError: function(result) {
                    /* You may add your own js here, this is just example */
                    // document.getElementById('result-json').innerHTML += JSON.stringify(result, null, 2);
                    console.log(result);
                    //send_response_to_form(result);
                },

                onClose: function(result) {
                    console.log('customer closed the popup without finishing the payment');
                    Swal.fire({
                        icon: 'error',
                        text: 'customer closed the popup without finishing the payment',
                    })
                }
            });
        });
    </script>

    @if ($message = Session::get('success'))
    <script>
        Swal.fire({
            icon: 'success',
            text: '{{ $message }}',
        })
    </script>
    @endif
    @endsection
</x-layouts.customers.app-layout>