<x-layouts.cpanel.customers.app-layout title="Edit PPP Secrets">

    <div class="card-header py-3">
        <h5 class="m-0 font-weight-bold text-primary"> {{ __('customer.header.edit') }} - {{ $customer->name }}</h5>
    </div>

    <div class="row">
        <div class="col-lg-12">

            {!! Form::model($customer, ['method' => 'PATCH', 'route' => ['customers.update.ppp', $customer->slug]]) !!}
            <div class="card-body col-lg-8">

                <div class="row align-items-center">
                    <div class="col-md-12 mb-3">
                        <label for="package"><strong>{{ __('customer.label.server') }}</strong>
                            <span class="badge bg-danger-soft text-danger">
                                <i class="fas fa-server"></i> {{ $customer->mikrotik->name }}
                            </span>
                        </label>
                    </div>
                </div>
                @if ($customer->internet_service == 'ppp')
                    <div class="row align-items-center">
                        <div class="col-md-12 mb-3">
                            <label for="package"><strong>{{ __('pppoe.secret.service') }}</strong>
                            </label>
                            {!! Form::select('ppp_type_id', $services, null, [
                                'placeholder' => 'Select Service',
                                'class' => 'form-control',
                                'id' => 'server-dropdown',
                            ]) !!}
                            @if ($errors->has('ppp_type_id'))
                                <span class="text-danger">{{ $errors->first('ppp_type_id') }}</span>
                            @endif
                        </div>
                    </div>


                    <div class="row align-items-center">
                        <div class="col-md-12 mb-3">
                            <label for="address"><strong>{{ __('customer.label.username') }}</strong></label>

                            {!! Form::text('username', null, [
                                'placeholder' => __('customer.placeholder.username'),
                                'class' => 'form-control',
                            ]) !!}

                            @if ($errors->has('username'))
                                <span class="text-danger">{{ $errors->first('username') }}</span>
                            @endif
                        </div>
                    </div>
                    <div class="row align-items-center">
                        <div class="col-md-12 mb-3">
                            <label for="password"><strong>{{ __('customer.label.password-ppp') }}</strong></label>

                            {!! Form::text('password_ppp', null, [
                                'placeholder' => __('customer.placeholder.username'),
                                'class' => 'form-control',
                            ]) !!}

                            @if ($errors->has('password_ppp'))
                                <span class="text-danger">{{ $errors->first('password_ppp') }}</span>
                            @endif
                        </div>
                    </div>
                    <div class="row align-items-center">
                        <div class="col-md-12 mb-3">
                            <label for="address"><strong>{{ __('customer.label.caller-id') }}</strong></label>
                            {!! Form::text('caller_id', null, [
                                'placeholder' => __('customer.placeholder.caller-id'),
                                'class' => 'form-control',
                            ]) !!}
                            @if ($customer->caller_id != $callerID)
                                <div class="form-switch">
                                    <input type="hidden" name="save_with_last_caller_id" value="false">
                                    <input class="form-check-input" value="true" type="checkbox"
                                        name="save_with_last_caller_id" id="saveLastCallerId">
                                    <label class="form-check-label" for="saveLastCallerId">
                                        {{ __('customer.label.update-with-last-caller-id') }}
                                    </label>
                                </div>
                            @endif
                            @if ($errors->has('caller_id'))
                                <span class="text-danger">{{ $errors->first('caller_id') }}</span>
                            @endif
                        </div>
                    </div>
                    @if ($customer->caller_id != $callerID)
                        <div class="row align-items-center">
                            <div class="col-md-12 mb-3">
                                <label
                                    for="caller_id"><strong>{{ __('customer.label.last-caller-id') }}</strong></label>
                                <input readonly name="last_caller_id" type="text" value="{{ $callerID }}"
                                    class="form-control">
                            </div>
                        </div>
                    @endif
                    <div class="row align-items-center">
                        <div class="col-md-12 mb-3">
                            <div class="form-switch">
                                <input type="hidden" name="dont_disconnect_client_button" value="false">
                                <input class="form-check-input" value="true" type="checkbox"
                                    name="dont_disconnect_client_button" id="disconnectClientButton">
                                <label class="form-check-label" for="disconnectClientButton">
                                    {{ __('customer.label.disconnect-client-button') }}
                                </label>
                            </div>
                        </div>
                    </div>
                @elseif ($customer->internet_service == 'ip_static')
                    <div class="col-md-12 mb-3">
                        <label for="caller_id"><strong>{{ __('customer.label.ip_address') }}</strong></label>
                        <div class="input-group">
                            {!! Form::text('ip_static', null, [
                                'placeholder' => __('customer.placeholder.ip_static'),
                                'class' => 'form-control',
                            ]) !!}
                            <div class="input-group-append">
                                <div class="input-group-text">
                                    <span class="far fa-keyboard"></span>
                                </div>
                            </div>
                        </div>
                        @if ($errors->has('ip_static'))
                            <span class="text-danger">{{ $errors->first('ip_static') }}</span>
                        @endif
                    </div>

                    <div class="col-md-12 mb-3">
                        <label for="caller_id"><strong>{{ __('customer.label.mac_address') }}</strong></label>
                        <div class="input-group">
                            {!! Form::text('mac_address', null, [
                                'placeholder' => __('customer.placeholder.mac_address'),
                                'class' => 'form-control',
                            ]) !!}
                            <div class="input-group-append">
                                <div class="input-group-text">
                                    <span class="far fa-keyboard"></span>
                                </div>
                            </div>
                        </div>
                        @if ($errors->has('mac_address'))
                            <span class="text-danger">{{ $errors->first('mac_address') }}</span>
                        @endif
                    </div>

                    <div class="col-md-12 mb-3">
                        <label for="interface"><strong>{{ __('customer.label.interface') }}</strong></label>
                        {!! Form::select('interface', $interfaces, null, [
                            'placeholder' => 'Select Interface',
                            'class' => 'form-control',
                            'id' => 'interface',
                        ]) !!}
                        @if ($errors->has('interface'))
                            <span class="text-danger">{{ $errors->first('interface') }}</span>
                        @endif
                    </div>

                @endif
                <div class="row align-items-center">
                    <div class="col-md-12 mb-3">
                        <div class="card-footer">
                            <button type="submit" class="btn btn-sm btn-success"><i
                                    class="fas fa-user-check"></i>&nbsp{{ __('customer.button.update-ppp') }}</button>
                            <a class="btn btn-sm btn-secondary"
                                href="{{ route('customers.edit', $customer->slug) }}"><i
                                    class="fa fa-arrow-left"></i>&nbsp{{ __('customer.button.cancel') }}</a>
                        </div>
                    </div>
                </div>
            </div>


            {!! Form::close() !!}
        </div>
    </div>
</x-layouts.cpanel.customers.app-layout>
