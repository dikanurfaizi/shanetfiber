<x-layouts.cpanel.billings.app-layout title="Invoice Show">

    <div class="card-header py-3">
        <h5 class="m-0 font-weight-bold text-primary">{{ __('Invoice Detail') }}</h5>
    </div>

    <div class="card-body col-lg-8">
        <div class="col-xs-12 col-sm-12 col-md-12">
            <div class="form-group">
                <strong>Number:</strong>
                {{ $receipt->number ?? 'no data' }}
            </div>
        </div>
        <div class="col-xs-12 col-sm-12 col-md-12">
            <div class="form-group">
                <strong>Name:</strong>
                {{ $receipt->customer_name ?? 'no data' }}
            </div>
        </div>
        <div class="col-xs-12 col-sm-12 col-md-12">
            <div class="form-group">
                <strong>Address:</strong>
                {{ $receipt->customer->address ?? 'no data' }}
            </div>
        </div>
        <div class="col-xs-12 col-sm-12 col-md-12">
            <div class="form-group">
                <strong>Periode:</strong>
                {{ $receipt->periode ?? 'no periode' }}
            </div>
        </div>
        <div class="col-xs-12 col-sm-12 col-md-12">
            <div class="form-group">
                <strong>Package:</strong>
                {{ $receipt->package_name ?? 'no data' }}
            </div>
        </div>
        <div class="col-xs-12 col-sm-12 col-md-12">
            <div class="form-group">
                <strong>Package Price:</strong>
                @if ($receipt->package_price === 0)
                    {{ __('Free') }}
                @else
                    @moneyIDR($receipt->package_price)
                @endif
            </div>
        </div>
        <div class="col-xs-12 col-sm-12 col-md-12">
            <div class="form-group">
                <strong>Registered as:</strong>
                @php
                    $member = 'Non Member';
                    if (
                        $receipt->customer->billing_option === 1 ||
                        ($receipt->customer->billing_option === 2 &&
                            $receipt->customer->billing_period === $receipt->customer->billing->billing_period->name)
                    ) {
                        $member = $receipt->member_type;
                    }
                @endphp
                {{ $member }}
            </div>
        </div>
        <div class="col-xs-12 col-sm-12 col-md-12">
            <div class="form-group">
                <strong>Discount:</strong>
                @moneyIDR($receipt->discount == 0 || is_null($receipt->discount) ? 0.0 : $receipt->discount)
            </div>
        </div>
        <div class="col-xs-12 col-sm-12 col-md-12">
            <div class="form-group">
                <strong>Total Price:</strong>
                @moneyIDR($receipt->price_after_discount ?: $receipt->package_price)
            </div>
        </div>
        <div class="col-xs-12 col-sm-12 col-md-12">
            <div class="form-group">
                <strong>User Created:</strong>
                <span class="badge bg-danger-soft text-danger">
                    <i class="fas fa-id-card-alt"></i> {{ $receipt->created_name ?? 'no data' }}
                </span>
            </div>
        </div>
        <div class="col-xs-12 col-sm-12 col-md-12">
            <div class="form-group">

            </div>
        </div>

        <div class="col-xs-12 col-sm-12 col-md-12">
            <div class="form-group">
                <strong>Teller:</strong>
                <span class="badge bg-danger-soft text-danger">
                    <i class="fas fa-id-card-alt"></i> {{ $receipt->teller_name ?? 'No User' }}
                </span>
            </div>
        </div>

        <div class="col-xs-12 col-sm-12 col-md-12">
            <div class="form-group">
                <strong>Payment Time:</strong>
                <span class="badge bg-s-soft text-primary">
                    <i class="fas fa-id-card-alt"></i>
                    {{ Carbon\Carbon::parse($receipt->payment_time)->translatedFormat('l, d M Y - H:m:s') ?? 'No Payment Time' }}
                </span>
            </div>
        </div>

        <div class="col-xs-12 col-sm-12 col-md-12">
            <div class="form-group">
                <strong>Payment Methode:</strong>
                <span class="badge bg-s-soft text-primary">
                    <i class="fas fa-id-card-alt"></i> {{ $receipt->payment_methode ?? 'No Payment Methode' }}
                    @if ($receipt->payment_methode === 'transfer')
                        <i> - </i> {{ $receipt->account_name ?? 'No Name' }} ( {{ $receipt->bank_name ?? 'No Bank' }}
                        - {{ $receipt->account_number ?? 'No Number' }} )
                    @endif
                </span>
            </div>
        </div>
        @if ($receipt->payment_methode === 'transfer')
            <div class="col-xs-12 col-sm-12 col-md-12">
                <div class="form-group">
                    <strong>Reference Id:</strong>
                    <span class="badge bg-s-soft text-primary">
                        <i class="fas fa-id-card-alt"></i> {{ $receipt->reference_id ?? 'No Reference Id' }}
                    </span>
                </div>
            </div>
        @endif


    </div>
    <div class="card-footer">
        <a class="btn btn-sm btn-secondary" href="{{ URL::previous() }}"><i class="fa fa-arrow-left"></i> Back</a>
        <a class="btn btn-success btn-sm" href="{{ route('receipts.print', $receipt->slug) }}" target="_blank"><i
                class="fa fa-print"></i> PDF</a>
        <a class="btn btn-success btn-sm" href="{{ route('receipts.thermal', $receipt->slug) }}" target="_blank">
            <i class="fa fa-print"></i> Thermal
        </a>
    </div>

</x-layouts.cpanel.billings.app-layout>
