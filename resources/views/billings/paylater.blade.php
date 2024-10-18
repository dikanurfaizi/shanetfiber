<x-layouts.cpanel.billings.app-layout title="Pay Later">

    <div class="card-header py-3">
        <h5 class="m-0 font-weight-bold text-primary">{{ __('Billing Pay Later') }}</h5>
    </div>
    {!! Form::model($billing, ['method' => 'PATCH','route' => ['billings.postPaylater', $billing->slug],'style'=>'display:inline']) !!}
    <div class="card-body col-lg-8">
        <div class="col-xs-12 col-sm-12 col-md-12">
            <div class="form-group">
                <strong>Number:</strong>
                {{ $billing->billing_number ?? 'no data' }}
            </div>
        </div>
        <div class="col-xs-12 col-sm-12 col-md-12">
            <div class="form-group">
                <strong>Name:</strong>
                {{ $billing->customer_name ?? 'no data' }}
            </div>
        </div>
        <div class="col-xs-12 col-sm-12 col-md-12">
            <div class="form-group">
                <strong>Periode:</strong>
                {{ $billing->month ?? 'no data' }} - {{ $billing->year ?? 'no data' }}
            </div>
        </div>
        <div class="col-xs-12 col-sm-12 col-md-12">
            <div class="form-group">
                <strong>Paket:</strong>
                {{ $billing->paket_name ?? 'no data' }}
            </div>
        </div>
        <div class="col-xs-12 col-sm-12 col-md-12">
            <div class="form-group">
                <strong>Price:</strong>
                @moneyIDR($billing->paket_price ?? '0')
            </div>
        </div>
        <div class="col-xs-12 col-sm-12 col-md-12">
            <div class="form-group">
                <strong>User Created:</strong>
                <span class="badge bg-danger-soft text-danger">
                    <i class="fas fa-id-card-alt"></i> {{ $billing->created_name ?? 'no data' }}
                </span>
            </div>
        </div>
        <div class="col-xs-12 col-sm-12 col-md-12">
            <div class="form-group">
                <strong>State:</strong>
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
            <div class="form-group">
                <strong>Teller:</strong>
                <span class="badge bg-danger-soft text-danger">
                    <i class="fas fa-id-card-alt"></i> {{ $billing->teller_name ?? 'No User' }}
                </span>
            </div>
        </div>
        @endif
        <div class="col-xs-6 col-sm-6 col-md-6">
            <div class="form-group">
                <strong>{{ __('billing.pay-later') }}</strong>
                <span>
                    <input type="datetime-local" class="form-control" name="pay_later" value="{{ Carbon\Carbon::parse($billing->pay_later)->format('d F Y') }}">
                </span>
                @if ($errors->has('pay_later'))
                <span class="text-danger">{{ $errors->first('pay_later') }}</span>
                @endif
            </div>
        </div>
    </div>

    <div class="card-footer">
        @if ($billing->status === 'BL' || $billing->status === 'PL')
        <a href="#" class="btn btn-success btn-sm" onclick="event.preventDefault(); this.closest('form').submit();">
            <i class="fas fa-money-bill-wave text-white"></i>
            {{ __('billing.button.pay-later') }}
        </a>
        @endif
        {!! Form::close() !!}
        <a class="btn btn-sm btn-primary" href="{{ route('billings.index') }}"><i class="fa fa-arrow-left"></i> Back</a>

    </div>

    @section('custom_styles')
    <link href="{{ asset('css/flatpickr.min.css') }}" rel="stylesheet">
    @endsection
    @section('custom_scripts')
    <script src="{{ asset('js/flatpickr') }}"></script>
    <script>
        config = {
            enableTime: false,
            dateFormat: 'd F Y',
        }
        flatpickr("input[type=datetime-local]", config);
    </script>
    @endsection
</x-layouts.cpanel.billings.app-layout>