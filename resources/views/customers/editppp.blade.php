<x-layouts.cpanel.customers.app-layout title="Edit PPP Secrets">

    <div class="card-header py-3">
        <h5 class="m-0 fw-bold text-primary"> {{ __('customer.header.edit') }} - {{ $customer->name }}</h5>
    </div>

    <div class="row">
        <div class="col-lg-12">

            <form action="{{ route('customers.update.ppp', $customer->slug) }}" method="POST">
                @csrf
                @method('PATCH')
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
                            <select name="ppp_type_id" class="form-control" id="server-dropdown">
                                <option value="">Select Service</option>
                                @foreach ($services as $id => $name)
                                    <option value="{{ $id }}" {{ old('ppp_type_id', $customer->ppp_type_id) == $id ? 'selected' : '' }}>{{ $name }}</option>
                                @endforeach
                            </select>
                            @if ($errors->has('ppp_type_id'))
                                <span class="text-danger">{{ $errors->first('ppp_type_id') }}</span>
                            @endif
                        </div>
                    </div>


                    <div class="row align-items-center">
                        <div class="col-md-12 mb-3">
                            <label for="address"><strong>{{ __('customer.label.username') }}</strong></label>

                            <input type="text" name="username" class="form-control" placeholder="{{ __('customer.placeholder.username') }}" value="{{ old('username', $customer->username) }}">

                            @if ($errors->has('username'))
                                <span class="text-danger">{{ $errors->first('username') }}</span>
                            @endif
                        </div>
                    </div>
                    <div class="row align-items-center">
                        <div class="col-md-12 mb-3">
                            <label for="password"><strong>{{ __('customer.label.password-ppp') }}</strong></label>

                            <input type="text" name="password_ppp" class="form-control" placeholder="{{ __('customer.placeholder.username') }}" value="{{ old('password_ppp', $customer->password_ppp) }}">

                            @if ($errors->has('password_ppp'))
                                <span class="text-danger">{{ $errors->first('password_ppp') }}</span>
                            @endif
                        </div>
                    </div>
                    <div class="row align-items-center">
                        <div class="col-md-12 mb-3">
                            <label for="address"><strong>{{ __('customer.label.caller-id') }}</strong></label>
                            <input type="text" name="caller_id" class="form-control" placeholder="{{ __('customer.placeholder.caller-id') }}" value="{{ old('caller_id', $customer->caller_id) }}">
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
                            <input type="text" name="ip_static" class="form-control" placeholder="{{ __('customer.placeholder.ip_static') }}" value="{{ old('ip_static', $customer->ip_static) }}">
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
                            <input type="text" name="mac_address" class="form-control" placeholder="{{ __('customer.placeholder.mac_address') }}" value="{{ old('mac_address', $customer->mac_address) }}">
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
                        <select name="interface" class="form-control" id="interface">
                            <option value="">Select Interface</option>
                            @foreach ($interfaces as $id => $name)
                                <option value="{{ $id }}" {{ old('interface', $customer->interface) == $id ? 'selected' : '' }}>{{ $name }}</option>
                            @endforeach
                        </select>
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
                                href="{{ route('customers.show', $customer->slug) }}"><i
                                    class="fa fa-arrow-left"></i>&nbsp{{ __('customer.button.cancel') }}</a>
                        </div>
                    </div>
                </div>
            </div>


            </form>
        </div>
    </div>
</x-layouts.cpanel.customers.app-layout>
