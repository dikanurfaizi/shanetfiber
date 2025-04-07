<div class="card-footer">
    @if ($websystem->payment_gateway == 'enable')
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
                <i>Anda telah memilih metode pembayaran <strong>{{ $order->payment_name }}</strong>.</i>
            </div>
            <div class="d-flex">
                <form method="GET" action="{{ route('customer.pages.order.tripays.show', $order->id) }}">
                    @csrf
                    <input type="hidden" name="payment_channel" value="{{ $gateway->slug }}">
                    <button type="submit" class="btn btn-primary mr-2">
                        {{ __('billing.button.continue-payment') }}
                    </button>
                </form>

                <form method="POST" action="{{ route('customer.order.tripays.cancel', $order->id) }}">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="payment_channel" value="{{ $gateway->slug }}">
                    <button type="submit" class="btn btn-danger">Batalkan</button>
                </form>
            </div>
        </div>
    @endif
</div>
