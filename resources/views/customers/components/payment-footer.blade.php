<div class="card-footer-custom">
    @if ($order->payment_status == 1)
        <button class="btn btn-outline-success m-1 btn-how-payment" data-toggle="modal" data-order-id="{{ $order->id }}"
            id="btnCheckStatus">
            Lihat Detail & Cek Status Pembayaran
        </button>
        <button class="btn btn-success m-1 btn-custom" id="paymentInstructionBtn" data-toggle="modal"
            data-target="#howToPaymentModal">
            Cara Pembayaran
        </button>
    @endif
    <a href="{{ route('customer.order.index') }}"
        class="btn btn-secondary m-1 d-flex justify-content-center align-items-center">
        <i class="fa fa-arrow-left mr-2"></i> Kembali
    </a>
</div>
