<x-layouts.cpanel.billings.app-layout title="Pay Later">

    <div class="card-header py-3">
        <h5 class="m-0 fw-bold text-primary">{{ __('billing.header.billing-paylater') }}</h5>
    </div>
    {!! Form::model($billing, [
        'method' => 'PATCH',
        'route' => ['billings.postPaylater', $billing->slug],
        'style' => 'display:inline',
    ]) !!}
    <div class="card-body col-lg-8">
        <div class="col-xs-12 col-sm-12 col-md-12">
            <div class="mb-3">
                <strong>{{ __('billing.label.billing-number') }}:</strong>
                {{ $billing->billing_number ?? 'no data' }}
            </div>
        </div>
        <div class="col-xs-12 col-sm-12 col-md-12">
            <div class="mb-3">
                <strong>{{ __('billing.label.customer-name') }}:</strong>
                {{ $billing->customer->name ?? 'no data' }}
            </div>
        </div>
        <div class="col-xs-12 col-sm-12 col-md-12">
            <div class="mb-3">
                <strong>{{ __('billing.label.billing-period') }}:</strong>
                {{ $billing->billingSubscription->billingPeriode->name }}
            </div>
        </div>
        <div class="col-xs-12 col-sm-12 col-md-12">
            <div class="mb-3">
                <strong>{{ __('billing.label.package-name') }}:</strong>
                {{ $billing->billingSubscription->customerSubscription->package->name ?? 'no data' }}
            </div>
        </div>
        <div class="col-xs-12 col-sm-12 col-md-12">
            <div class="mb-3">
                <strong>{{ __('billing.label.package-price') }}:</strong>
                @moneyIDR($billing->base_price ?? '0')
            </div>
        </div>
        <div class="col-xs-12 col-sm-12 col-md-12">
            <div class="mb-3">
                <strong>{{ __('billing.label.user-created') }}:</strong>
                <span class="badge bg-danger-soft text-danger">
                    <i class="fas fa-id-card-alt"></i> {{ $billing->createdByUser->name ?? 'no data' }}
                </span>
            </div>
        </div>
        <div class="col-xs-12 col-sm-12 col-md-12">
            <div class="mb-3">
                <strong>{{ __('billing.label.state') }}:</strong>
                @if ($billing->status === 'LS')
                    <span class="badge bg-success text-white">
                        <i class="fas fa-check-circle"></i>
                        {{ __('billing.state.lunas') }}
                    </span>
                @elseif ($billing->status === 'PL')
                    <span class="badge bg-warning text-white">
                        <i class="fas fa-ban"></i>
                        {{ __('billing.state.paylater') }}
                    </span>
                @else
                    <span class="badge bg-danger text-white">
                        <i class="fas fa-ban"></i>
                        {{ __('billing.state.belum-lunas') }}
                    </span>
                @endif
            </div>
        </div>
        @if ($billing->status === 'LS')
            <div class="col-xs-12 col-sm-12 col-md-12">
                <div class="mb-3">
                    <strong>{{ __('billing.label.teller-name') }}:</strong>
                    <span class="badge bg-danger-soft text-danger">
                        <i class="fas fa-id-card-alt"></i> {{ $billing->teller_name ?? 'No User' }}
                    </span>
                </div>
            </div>
        @endif
        <div class="col-xs-6 col-sm-6 col-md-6">
            <div class="mb-3">
                <strong>{{ __('billing.pay-later') }}</strong>
                {{-- @php
                    dd([
                        'pay_later' => $billing->pay_later,
                        'value_attr' => $billing->pay_later
                            ? \Carbon\Carbon::parse($billing->pay_later)->format('Y-m-d\TH:i')
                            : now()->format('Y-m-d\TH:i'),
                    ]);
                @endphp --}}
                <span>
                    <input type="text" class="form-control flatpickr-date" name="pay_later" autocomplete="off"
                        value="{{ $billing->paylater_until ? \Carbon\Carbon::parse($billing->paylater_until)->format('d F Y') : now()->format('d F Y') }}">
                    <input type="hidden" name="pay_later_db"
                        value="{{ $billing->paylater_until ? \Carbon\Carbon::parse($billing->paylater_until)->format('Y-m-d H:i:s') : now()->format('Y-m-d H:i:s') }}">
                </span>
                @if ($errors->has('pay_later'))
                    <span class="text-danger">{{ $errors->first('pay_later') }}</span>
                @endif
            </div>
        </div>
    </div>

    <div class="card-footer">
        @if ($billing->status === 'BL' || $billing->status === 'PL')
            <a href="#" class="btn btn-success btn-sm"
                onclick="event.preventDefault(); this.closest('form').submit();">
                <i class="fas fa-money-bill-wave text-white"></i>
                {{ __('billing.button.pay-later') }}
            </a>
        @endif
        {!! Form::close() !!}
        <a class="btn btn-sm btn-primary" href="{{ route('billings.index') }}"><i class="fa fa-arrow-left"></i>
            {{ __('billing.button.back') }}</a>

    </div>

    @section('custom_styles')
        <link href="{{ asset('css/flatpickr.min.css') }}" rel="stylesheet">
    @endsection
    @section('custom_scripts')
        <script src="{{ asset('js/flatpickr') }}"></script>
        <script>
            flatpickr(".flatpickr-date", {
                dateFormat: "d F Y",
                minDate: "today",
            });
        </script>
    @endsection
</x-layouts.cpanel.billings.app-layout>
