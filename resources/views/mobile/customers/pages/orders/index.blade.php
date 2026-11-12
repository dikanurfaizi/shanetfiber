<x-layouts.customers.mobile.app-layout title="Orders {{ auth()->guard('customer')->user()->name }}">

    <div class="card-header py-3">
        <h5 class="m-0 fw-bold text-primary">{{ __('Orders') }}</h5>
        <span><a class="btn btn-sm @if (request()->routeIs('customer.order.index')) btn-success @else btn-secondary @endif  mt-2"
                href="{{ route('customer.order.index') }}"><i class="fas fa-star"></i>
                {{ __('billing.button.all-payment') }}</a></span>
        <span><a class="btn btn-sm @if (Request::is('customer/billings/orders/1/show')) btn-success @else btn-secondary @endif mt-2"
                href="{{ route('customer.order.state', '1') }}"><i class="fas fa-search"></i>
                {{ __('billing.button.waiting-payment') }}</a></span>
        <span><a class="btn btn-sm @if (Request::is('customer/billings/orders/3/show')) btn-success @else btn-secondary @endif mt-2"
                href="{{ route('customer.order.state', '3') }}"><i class="fas fa-search"></i>
                {{ __('billing.button.expired') }}</a></span>
        <span><a class="btn btn-sm @if (Request::is('customer/billings/orders/4/show')) btn-success @else btn-secondary @endif mt-2"
                href="{{ route('customer.order.state', '4') }}"><i class="fas fa-search"></i>
                {{ __('billing.button.cancelled') }}</a></span>
        <span><a class="btn btn-sm @if (Request::is('customer/billings/orders/2/show')) btn-success @else btn-secondary @endif mt-2"
                href="{{ route('customer.order.state', '2') }}"><i class="fas fa-clipboard-check"></i>
                {{ __('billing.button.paid-off') }}</a></span>
    </div>

    <div class="card-body">
        <div style="overflow-x: auto;">
            <table id="packagesTable" cellspacing="0" width="100%"
                class="table datatable-loading no-footer sortable searchable">
                <thead class="thead-light">
                    <th scope="col">#</th>
                    <th scope="col">ID</th>
                    <th scope="col">Total Harga</th>
                    <th scope="col">Status Pembayaran</th>
                    <th scope="col">Action</th>
                </thead>
                <tbody>
                    @foreach ($orders as $order)
                        <tr>
                            <td>{{ ++$i }}</td>
                            <td>#{{ $order->number }}</td>
                            <td>@moneyIDR($order->billing->base_price)</td>
                            <td>
                                @if ($order->payment_status == 1)
                                    {{ __('billing.status.waiting-payment') }}
                                @elseif ($order->payment_status == 2)
                                    {{ __('billing.status.paid-off') }}
                                @elseif ($order->payment_status == 3)
                                    {{ __('billing.status.expired') }}
                                @else
                                    {{ __('billing.status.cancelled') }}
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('customer.order.show', $order->id) }}"
                                    class="btn btn-success btn-sm">
                                    <i class="far fa-eye me-2 text-white"></i>
                                    {{ __('billing.button.view') }}
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        {{ $orders->links() }}
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
