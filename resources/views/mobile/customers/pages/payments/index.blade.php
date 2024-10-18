<x-layouts.customers.mobile.app-layout title="Billings {{ auth()->guard('customer')->user()->name }}">

    <div class="card-header py-3">
        <h5 class="m-0 font-weight-bold text-primary">{{ __('Payments') }}</h5>
        <span><a class="btn btn-sm @if(request()->routeIs('customer.payment.index')) btn-success @else btn-secondary @endif  mt-2" href="{{ route('customer.payment.index') }}"><i class="fas fa-star"></i> {{ __('billing.button.all-payment') }}</a></span>
        <span><a class="btn btn-sm @if(Request::is('customer/billings/payments/BL/show')) btn-danger @else btn-secondary @endif mt-2" href="{{ route('customer.payment.state', 'BL') }}"><i class="fas fa-ban"></i> {{ __('billing.button.unpaid') }}</a></span>
        <span><a class="btn btn-sm @if(Request::is('customer/billings/payments/LS/show')) btn-success @else btn-secondary @endif mt-2" href="{{ route('customer.payment.state', 'LS') }}"><i class="fas fa-heart"></i> {{ __('billing.button.paid-off') }}</a></span>

    </div>

    <div class="card-body">
        <table id="paketsTable" cellspacing="0" width="100%" class="table datatable-loading no-footer sortable searchable">
            <thead class="thead-light">
                <th scope="col">#</th>
                <th scope="col">ID</th>
                <th scope="col">Total Harga</th>
                <th scope="col">Status Pembayaran</th>
                <th scope="col">Action</th>
            </thead>
            <tbody>
                @foreach ($billings as $billing)
                <tr>
                    <td>{{ ++$i }}</td>
                    <td>#{{ $billing->billing_number }}</td>
                    <td>@moneyIDR($billing->paket_price)</td>
                    <td>
                        @if ($billing->status == 'LS')
                        {{ __('billing.state.lunas') }}

                        @else
                        {{ __('billing.state.belum-lunas') }}
                        @endif
                    </td>
                    <td>

                        <a href="{{ route('customer.payment.show', $billing->id) }}" class="btn btn-success btn-sm">
                            <i class="far fa-eye me-2 text-white"></i> {{ __('billing.button.view') }}
                        </a>

                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        {{ $billings->links() }}
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