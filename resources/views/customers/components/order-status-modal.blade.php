<table class="table table-hover table-bordered">
    <tr>
        <td>ID</td>
        <td><b>#{{ $order->number }}</b></td>
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
    <tr>
        <td>Harga</td>
        <td><b>@moneyIDR($order->total_price)</b></td>
    </tr>
    <tr>
        <td>Status Pembayaran</td>
        <td><b>
                @switch($order->payment_status)
                    @case(1)
                        {{ __('billing.status.waiting-payment') }}
                    @break

                    @case(2)
                        {{ __('billing.status.paid-off') }}
                    @break

                    @case(3)
                        {{ __('billing.status.expired') }}
                    @break

                    @default
                        {{ __('billing.status.cancelled') }}
                @endswitch
            </b></td>
    </tr>
    <tr>
        <td>Tanggal</td>
        <td><b>{{ $order->created_at->format('d M Y H:i') }}</b></td>
    </tr>
</table>
