<x-layouts.customers.mobile.app-layout title="Billing #{{ $billing->billing_number }}">

    <div class="card-header py-3">
        <h5 class="m-0 font-weight-bold text-primary">{{ __('Bill Number #') }}{{ $billing->billing_number }}</h5>
    </div>

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
            <tr>
                <td>Total Harga</td>
                <td><b>@moneyIDR($billing->paket_price)</b></td>
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
    <div class="card-footer">
        @if ($billing->status == 'BL' && $paymentGateway == 'enable')
        <a class="btn btn-sm btn-success mt-2" href="{{ route('customer.order.create', $billing->id) }}">
            <i class="fas fa-cart-plus"></i> {{ __('billing.button.create-order') }}
        </a>
        <p></p>
        <p><i>Untuk pembayaran online klik order</i></p>
        @endif
    </div>


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
</x-layouts.customers.mobile.app-layout>