@php
    $channels = collect($channels);
@endphp

<form method="POST" action="{{ route('customer.tripays.order.create', $billing->id) }}">
    @csrf
    @if ($channels->isNotEmpty())
        <div class="col-12 mb-3">
            <div class="d-flex justify-content-between align-items-center alert alert-info mb-0">
                <div>
                    Metode pembayaran Anda sebelumnya menggunakan
                    <strong>{{ $order->payment_name }}</strong> telah
                    <strong>kadaluwarsa</strong>,
                    silakan memilih metode pembayaran yang baru.
                </div>
            </div>
        </div>

        <x-payment-channel-list :channels="$channels" />
    @endif

    <input type="hidden" name="icon_url" id="icon_url">

    <div class="card-footer">
        @if ($billing->status == 'BL' && $websystem->payment_gateway == 'enable')
            <input type="hidden" name="payment_channel" value="{{ $gateway->slug }}">
            <button type="submit" class="order-button btn btn-success">
                <i class="fas fa-shield-alt"></i> {{ __('billing.button.create-order') }}
            </button>
            <p></p>
            <p><i>Untuk pembayaran online klik {{ __('billing.button.create-order') }}</i></p>
        @endif
    </div>
</form>
