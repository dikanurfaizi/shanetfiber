<x-layouts.cpanel.customers.app-layout title="Create Customer">

    <div class="card-header py-3">
        <h5 class="m-0 font-weight-bold text-primary">{{ __('customer.header.create') }}</h5>
    </div>

    <div class="row">
        <div class="col-lg-12">
            {!! Form::open(['route' => 'customers.store', 'method' => 'POST']) !!}
            <div class="card-body col-lg-8">
                <div class="row align-items-center">
                    <div class="col-md-12 mb-3">
                        <label for="user_id"><strong>{{ __('customer.label.responsibility') }}</strong></label>
                        <div class="input-group">
                            {!! Form::select('user_id', $users->pluck('name', 'id'), null, [
                                'placeholder' => __('customer.placeholder.responsibility'),
                                'class' => 'form-control',
                            ]) !!}
                            <div class="input-group-append">
                                <div class="input-group-text">
                                    <span class="fas fa-user"></span>
                                </div>
                            </div>
                        </div>
                        @if ($errors->has('user_id'))
                            <span class="text-danger">{{ $errors->first('user_id') }}</span>
                        @endif
                    </div>
                </div>

                <div class="row align-items-center">
                    <div class="col-md-12 mb-3">
                        <label for="server"><strong>{{ __('customer.label.server') }}</strong></label>
                        {!! Form::select('mikrotik_id', $mikrotiks, null, [
                            'placeholder' => 'Select Server',
                            'class' => 'form-control',
                            'id' => 'server-dropdown',
                        ]) !!}
                        @if ($errors->has('mikrotik_id'))
                            <span class="text-danger">{{ $errors->first('mikrotik_id') }}</span>
                        @endif
                    </div>
                </div>
                <div class="row align-items-center">
                    <div class="col-md-12 mb-3">

                        <select name="paket_id" id="paket-dropdown" class="form-control d-none">
                            <option value="">Select Paket</option>
                        </select>

                        @if ($errors->has('paket_id'))
                            <span class="text-danger">{{ $errors->first('paket_id') }}</span>
                        @endif
                    </div>
                </div>

                <div class="row align-items-center">
                    <div class="col-md-12 mb-3">
                        <label
                            for="internet_service"><strong>{{ __('customer.label.internet_service') }}</strong></label>
                        <select name="internet_service" id="service-dropdown" class="form-control">
                            <option value="">Select Service</option>
                            <option value="ppp">PPP</option>
                            <option value="ip_static">IP Static</option>
                        </select>
                        @if ($errors->has('internet_service'))
                            <span class="text-danger">{{ $errors->first('internet_service') }}</span>
                        @endif
                    </div>
                </div>

                <div id="input-ppp" class="row align-items-center d-none">
                    <div class="col-md-12 mb-3">

                        {!! Form::select('ppp_type_id', $pppservices, null, [
                            'placeholder' => 'Select PPP Type',
                            'class' => 'form-control',
                            'id' => 'ppp_types_id',
                        ]) !!}
                        @if ($errors->has('ppp_type_id'))
                            <span class="text-danger">{{ $errors->first('ppp_type_id') }}</span>
                        @endif
                    </div>
                </div>


                <div id="input-ip-static" class="row align-items-center d-none">
                    <div class="col-md-12 mb-3">
                        <label for="interface"><strong>{{ __('customer.label.interface') }}</strong></label>
                        <select name="interface" id="interface-dropdown" class="form-control">
                            <option value="">Select Interface</option>
                        </select>
                        @if ($errors->has('interface'))
                            <span class="text-danger">{{ $errors->first('interface') }}</span>
                        @endif
                    </div>
                    <div class="col-md-12 mb-3">
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
                </div>


                <div class="row align-items-center">
                    <div class="col-md-12 mb-3">
                        <label for="name"><strong>{{ __('customer.label.name') }}</strong></label>
                        <div class="input-group ">
                            {!! Form::text('name', null, ['placeholder' => __('customer.placeholder.name'), 'class' => 'form-control']) !!}
                            <div class="input-group-append">
                                <div class="input-group-text">
                                    <span class="fas fa-user"></span>
                                </div>
                            </div>
                        </div>
                        @if ($errors->has('name'))
                            <span class="text-danger">{{ $errors->first('name') }}</span>
                        @endif
                    </div>
                </div>

                <div class="row align-items-center">
                    <div class="col-md-12 mb-3">
                        <label for="email"><strong>{{ __('customer.label.email') }}</strong></label>
                        <div class="input-group">
                            {!! Form::text('email', null, ['placeholder' => __('customer.placeholder.email'), 'class' => 'form-control']) !!}
                            <div class="input-group-append">
                                <div class="input-group-text">
                                    <span class="fas fa-envelope"></span>
                                </div>
                            </div>
                        </div>
                        @if ($errors->has('email'))
                            <span class="text-danger">{{ $errors->first('email') }}</span>
                        @endif
                    </div>
                </div>

                <div class="row align-items-center">
                    <div class="col-md-12 mb-3">
                        <label for="password"><strong>{{ __('customer.label.password') }}</strong></label>
                        <div class="input-group">
                            {!! Form::password('password', [
                                'placeholder' => __('customer.placeholder.password'),
                                'class' => 'form-control',
                            ]) !!}
                            <div class="input-group-append">
                                <div class="input-group-text">
                                    <span class="fas fa-lock"></span>
                                </div>
                            </div>
                        </div>
                        @if ($errors->has('password'))
                            <span class="text-danger">{{ $errors->first('password') }}</span>
                        @endif
                    </div>
                </div>

                <div class="row align-items-center">
                    <div class="col-md-12 mb-3">
                        <label
                            for="password_confirmation"><strong>{{ __('customer.label.confirm-password') }}</strong></label>
                        <div class="input-group">
                            {!! Form::password('password_confirmation', [
                                'placeholder' => __('customer.placeholder.confirm-password'),
                                'class' => 'form-control',
                            ]) !!}
                            <div class="input-group-append">
                                <div class="input-group-text">
                                    <span class="fas fa-lock"></span>
                                </div>
                            </div>
                        </div>
                        @if ($errors->has('password_confirmation'))
                            <span class="text-danger">{{ $errors->first('password_confirmation') }}</span>
                        @endif
                    </div>
                </div>
                <div class="row align-items-center">
                    <div class="col-md-12 mb-3">
                        <label for="address"><strong>{{ __('customer.label.address') }}</strong></label>
                        <div class="input-group">
                            {!! Form::text('address', null, [
                                'placeholder' => __('customer.placeholder.address'),
                                'class' => 'form-control',
                            ]) !!}
                            <div class="input-group-append">
                                <div class="input-group-text">
                                    <span class="fas fa-home"></span>
                                </div>
                            </div>
                        </div>
                        @if ($errors->has('address'))
                            <span class="text-danger">{{ $errors->first('address') }}</span>
                        @endif
                    </div>
                </div>
                <div class="row align-items-center">
                    <div class="col-md-12 mb-3">
                        <label for="contact"><strong>{{ __('customer.label.contact') }}</strong></label>
                        <div class="input-group">
                            {!! Form::text('contact', null, [
                                'placeholder' => __('customer.placeholder.contact'),
                                'class' => 'form-control',
                            ]) !!}
                            <div class="input-group-append">
                                <div class="input-group-text">
                                    <span class="far fa-address-book"></span>
                                </div>
                            </div>
                        </div>
                        @if ($errors->has('contact'))
                            <span class="text-danger">{{ $errors->first('contact') }}</span>
                        @endif
                    </div>
                </div>


                <div class="row align-items-center">
                    <div class="col-md-12">
                        <label for="discount"><strong>{{ __('customer.label.discount') }}</strong></label>
                    </div>
                    <div class="col-md-12 mb-3 row">
                        <div class="col-md-2 mx-0">
                            {!! Form::text('discount', null, [
                                'placeholder' => __('customer.placeholder.discount'),
                                'class' => 'form-control',
                            ]) !!}
                        </div>
                        <div class=" py-2">
                            <strong>%</strong>
                        </div>
                        <div class="col-md-12">
                            @if ($errors->has('discount'))
                                <span class="text-danger">{{ $errors->first('discount') }}</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>


            <div class="card-footer">
                <button type="submit" class="btn btn-sm btn-success"><i class="fas fa-user-plus"></i> &nbsp
                    {{ __('customer.button.save') }}</button>
                <a class="btn btn-sm btn-secondary" href="{{ route('customers.index') }}"><i
                        class="fa fa-arrow-left"></i> &nbsp {{ __('customer.button.back') }}</a>
            </div>
            {!! Form::close() !!}
        </div>
    </div>



</x-layouts.cpanel.customers.app-layout>
