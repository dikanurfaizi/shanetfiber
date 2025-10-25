<table class="table table-hover table-bordered">
    <tr>
        <td>ID</td>
        <td><b>#{{ $order->number }}</b></td>
    </tr>
    <tr>
        <td>Name</td>
        <td><b>{{ $billing->customer_name }}</b></td>
    </tr>
    <tr>
        <td>Period</td>
        <td><b>{{ $billing->billingSubscription->billingPeriode->name }}</b></td>
    </tr>
    <tr>
        <td>Package</td>
        <td><b>{{ $billing->billingSubscription->customerSubscription->package->name }}</b></td>
    </tr>
    <tr>
        <td>Price</td>
        <td><b>@moneyIDR($order->total_price)</b></td>
    </tr>
    <tr>
        <td>Bill Status</td>
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
        <td>Date</td>
        <td><b>{{ $order->created_at->format('d M Y H:i') }}</b></td>
    </tr>
</table>
