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
                <td>PPN</td>
                <td><b>@moneyIDR($billing->tax)</b></td>
            </tr>
        @endif

        <tr>
            <td>Total Price</td>
            <td><b>@moneyIDR($billing->paket_price + $billing->tax)</b></td>
        </tr>
        <tr>
            <td>Status Pembayaran</td>
            <td>
                <b>
                    {{ $billing->status == 'BL' ? __('billing.state.belum-lunas') : __('billing.state.lunas') }}
                </b>
            </td>
        </tr>
        <tr>
            <td>Tanggal</td>
            <td><b>{{ $billing->created_at->format('d M Y H:i') }}</b></td>
        </tr>
    </table>
</div>
