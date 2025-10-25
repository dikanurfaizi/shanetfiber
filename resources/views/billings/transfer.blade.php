<x-layouts.cpanel.billings.app-layout title="Transfer">

    <div class="card-header py-3">
        <h5 class="m-0 font-weight-bold text-primary">{{ __('Billing Transfer') }}</h5>
    </div>
    {!! Form::model($billing, [
        'method' => 'PATCH',
        'route' => ['billings.payment', $billing->slug],
        'style' => 'display:inline',
    ]) !!}
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
                {{ $billing->billingSubscription->billingPeriode->month ?? 'no data' }} -
                {{ $billing->billingSubscription->billingPeriode->year ?? 'no data' }}
            </div>
        </div>
        <div class="col-xs-12 col-sm-12 col-md-12">
            <div class="form-group">
                <strong>Package:</strong>
                {{ $billing->customer->package->name ?? 'no data' }}
            </div>
        </div>
        <div class="col-xs-12 col-sm-12 col-md-12">
            <div class="form-group">
                <strong>Price:</strong>
                @moneyIDR($billing->base_price ?? '0')
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
        @if ($billing->status != 'LS')
            <div class="col-xs-6 col-sm-6 col-md-6">
                <div class="form-group">
                    <strong>{{ __('billing.bank-transfer') }}</strong>
                    <select name="bank_transfer" id="bank-dropdown" class="form-control">
                        <option value="">Select Bank</option>
                        @foreach ($banks as $bank)
                            <option value="{{ $bank->id }}"
                                {{ old('bank_transfer') == $bank->id ? 'selected' : '' }}>{{ $bank->name }}
                                ({{ $bank->bank }} - {{ $bank->account_number }})
                            </option>
                        @endforeach
                    </select>
                    @if ($errors->has('bank_transfer'))
                        <span class="text-danger">{{ $errors->first('bank_transfer') }}</span>
                    @endif
                </div>
            </div>
            <div class="col-xs-6 col-sm-6 col-md-6">
                <div class="form-group">
                    <strong>Deposit Bank:</strong>
                    {!! Form::text('deposit_bank_name', null, [
                        'placeholder' => __('bank.placeholder.deposit_bank_name'),
                        'class' => 'form-control',
                    ]) !!}
                    @if ($errors->has('deposit_bank_name'))
                        <span class="text-danger">{{ $errors->first('deposit_bank_name') }}</span>
                    @endif
                </div>
            </div>

            <div class="col-xs-6 col-sm-6 col-md-6">
                <div class="form-group">
                    <strong>Deposit Account Name:</strong>
                    {!! Form::text('deposit_account_name', null, [
                        'placeholder' => __('bank.placeholder.deposit_account_name'),
                        'class' => 'form-control',
                    ]) !!}
                    @if ($errors->has('deposit_account_name'))
                        <span class="text-danger">{{ $errors->first('deposit_account_name') }}</span>
                    @endif
                </div>
            </div>

            <div class="col-xs-6 col-sm-6 col-md-6">
                <div class="form-group">
                    <strong>Deposit Account Number:</strong>
                    {!! Form::text('deposit_account_number', null, [
                        'placeholder' => __('bank.placeholder.deposit_account_number'),
                        'class' => 'form-control',
                    ]) !!}
                    @if ($errors->has('deposit_account_number'))
                        <span class="text-danger">{{ $errors->first('deposit_account_number') }}</span>
                    @endif
                </div>
            </div>

            <div class="col-xs-6 col-sm-6 col-md-6">
                <div class="form-group">
                    <strong>Reference Id:</strong>
                    {!! Form::text('reference_id', null, [
                        'placeholder' => __('bank.placeholder.reference-id'),
                        'class' => 'form-control',
                    ]) !!}
                    @if ($errors->has('reference_id'))
                        <span class="text-danger">{{ $errors->first('reference_id') }}</span>
                    @endif
                </div>
            </div>
        @endif
        <input type="hidden" value="transfer_payment" name="transfer_payment">

    </div>
    <div class="card-footer">
        @if ($billing->status === 'BL' || $billing->status === 'PL')
            <a href="#" class="btn btn-success btn-sm"
                onclick="event.preventDefault(); this.closest('form').submit();">
                <i class="fas fa-money-bill-wave text-white"></i>
                {{ __('billing.button.pay') }}
            </a>
        @endif
        {!! Form::close() !!}
        <a class="btn btn-sm btn-primary" href="{{ route('billings.index') }}"><i class="fa fa-arrow-left"></i>
            Back</a>

    </div>
</x-layouts.cpanel.billings.app-layout>
