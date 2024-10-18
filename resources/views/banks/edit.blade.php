<x-layouts.cpanel.settings.app-layout title="Edit Acount Bank">
    <div class="card-header py-3">
        <h5 class="m-0 font-weight-bold text-primary"> {{ __('bank.header.edit') }}</h5>
    </div>

    <div class="row">
        <div class="col-lg-12">
            {!! Form::model($bank, ['method' => 'PATCH','route' => ['banks.update', $bank->slug]]) !!}
            <div class="card-body col-lg-8">

                <div class="row align-items-center">
                    <div class="col-md-12 mb-3">
                        <label for="name"><strong>{{ __('bank.label.name') }}</strong></label>
                        <div class="input-group">
                            {!! Form::text('name', null, array('placeholder' => __('bank.placeholder.name'),'class' => 'form-control')) !!}
                        </div>
                        @if ($errors->has('name'))
                        <span class="text-danger">{{ $errors->first('name') }}</span>
                        @endif
                    </div>
                </div>


                <div class="row align-items-center">
                    <div class="col-md-12 mb-3">
                        <label for="bank"><strong>{{ __('bank.label.bank-name') }}</strong></label>
                        {!! Form::text('bank', null, array('placeholder' => __('bank.placeholder.bank'),'class' => 'form-control')) !!}

                        @if ($errors->has('bank'))
                        <span class="text-danger">{{ $errors->first('bank') }}</span>
                        @endif
                    </div>
                </div>

                <div class="row align-items-center">
                    <div class="col-md-12 mb-3">
                        <label for="account_number"><strong>{{ __('bank.label.account-number') }}</strong></label>
                        <div class="input-group">
                            {!! Form::text('account_number', null, array('placeholder' => __('bank.placeholder.account_number'),'class' => 'form-control')) !!}
                        </div>
                        @if ($errors->has('account_number'))
                        <span class="text-danger">{{ $errors->first('account_number') }}</span>
                        @endif
                    </div>
                </div>
            </div>
            <div class="card-footer">
                <button type="submit" class="btn btn-sm btn-success"><i class="fa fa-save"></i> &nbsp {{ __('bank.button.update') }}</button>
            </div>
            {!! Form::close() !!}
        </div>
    </div>

</x-layouts.cpanel.settings.app-layout>