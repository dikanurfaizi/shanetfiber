<x-layouts.cpanel.customers.app-layout title="Edit Customer">


    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">
            {{ $customer->name . ' - ( ID: ' . $customer->customer_id . ' )' }}
        </h6>
    </div>

    <div class="row">
        <div class="col-lg-12">
            {!! Form::model($customer, ['method' => 'PATCH', 'route' => ['customers.update', $customer->slug]]) !!}
            <div class="card-body col-lg-8">
                <div class="row align-items-center">
                    <div class="col-md-12 mb-3">
                        <label for="name"><strong>{{ __('customer.label.name') }}</strong></label>
                        {!! Form::text('name', null, ['placeholder' => __('customer.placeholder.name'), 'class' => 'form-control']) !!}
                        @if ($errors->has('name'))
                            <span class="text-danger">{{ $errors->first('name') }}</span>
                        @endif
                    </div>
                </div>
                <div class="row align-items-center">
                    <div class="col-md-12 mb-3">
                        <label for="paket"><strong>{{ __('customer.label.paket') }}</strong>
                        </label>
                        @if ($serverDisconnect === 'false')
                            <select name="paket_id" class="form-control dynamic" data-dependent="details">
                                @foreach ($pakets as $paket)
                                    <option value="{{ $paket->id }}"
                                        {{ $paket->id == $customer->paket_id ? 'selected' : '' }}>{{ $paket->name }} -
                                        @moneyIDR($paket->price)</option>
                                @endforeach
                            </select>
                            @if ($errors->has('paket_id'))
                                <span class="text-danger">{{ $errors->first('paket_id') }}</span>
                            @endif
                        @else
                            <select readonly name="paket_id" class="form-control dynamic" data-dependent="details">
                                <option value="{{ $customer->paket_id }}" selected>{{ $customer->paket->name }} -
                                    @moneyIDR($customer->paket->price)</option>
                            </select>
                            <span class="text-danger">{{ __('Server ') }} {{ $customer->mikrotik->name }}
                                {{ __('Offline or Disable!') }}</span>
                        @endif

                    </div>
                </div>
                <div class="row align-items-center mb-3">
                    <div class="col-md-12">
                        <label for="discount"><strong>{{ __('customer.label.discount') }}</strong></label>
                    </div>
                    <div class="col-md-12 row">
                        <div class="col-md-2 mx-0">
                            {!! Form::text('discount', null, [
                                'placeholder' => __('customer.placeholder.discount'),
                                'class' => 'form-control',
                            ]) !!}
                        </div>
                        <div class=" py-2">
                            <strong>%</strong>
                        </div>
                    </div>
                    <div class="col-md-12">
                        @if ($errors->has('discount'))
                            <span class="text-danger">{{ $errors->first('discount') }}</span>
                        @endif
                    </div>

                </div>
                <div class="row align-items-center">
                    <div class="col-md-12 mb-3">
                        <label for="email"><strong>{{ __('customer.label.email') }}</strong></label>
                        {!! Form::text('email', null, ['placeholder' => __('customer.placeholder.email'), 'class' => 'form-control']) !!}
                        @if ($errors->has('email'))
                            <span class="text-danger">{{ $errors->first('email') }}</span>
                        @endif
                    </div>
                </div>

                <div class="row align-items-center">
                    <div class="col-md-12 mb-3">
                        <label for="password"><strong>{{ __('customer.label.password') }}</strong></label>
                        {!! Form::password('password', [
                            'placeholder' => __('customer.placeholder.password'),
                            'class' => 'form-control',
                        ]) !!}
                        @if ($errors->has('password'))
                            <span class="text-danger">{{ $errors->first('password') }}</span>
                        @endif
                    </div>
                </div>

                <div class="row align-items-center">
                    <div class="col-md-12 mb-3">
                        <label
                            for="confirm-password"><strong>{{ __('customer.label.confirm-password') }}</strong></label>
                        {!! Form::password('password_confirmation', [
                            'placeholder' => __('customer.placeholder.confirm-password'),
                            'class' => 'form-control',
                        ]) !!}
                        @if ($errors->has('password_confirmation'))
                            <span class="text-danger">{{ $errors->first('password_confirmation') }}</span>
                        @endif
                    </div>
                </div>

                <div class="row align-items-center">
                    <div class="col-md-12 mb-3">
                        <label for="address"><strong>{{ __('customer.label.address') }}</strong></label>
                        {!! Form::text('address', null, [
                            'placeholder' => __('customer.placeholder.address'),
                            'class' => 'form-control',
                        ]) !!}
                        @if ($errors->has('address'))
                            <span class="text-danger">{{ $errors->first('address') }}</span>
                        @endif
                    </div>
                </div>
                <div class="row align-items-center">
                    <div class="col-md-12 mb-3">
                        <label for="contact"><strong>{{ __('customer.label.contact') }}</strong></label>

                        <input class="form-control contact" id="contact" name="contact" type="text"
                            placeholder="{{ trans('customer.placeholder.contact') }}" value="{{ $customer_number }}">
                        @if ($errors->has('contact'))
                            <span class="text-danger">{{ $errors->first('contact') }}</span>
                        @endif
                    </div>
                </div>

                <div class="align-items-center">
                    <div class="col-md-12 mb-4 row">
                        <label
                            for="activation_date"><strong>{{ __('customer.label.activation-date') }}</strong></label>
                        @if ($serverDisconnect === 'false')
                            <div class="col-md-4">
                                <input type="datetime-local" class="form-control" name="activation_date"
                                    value="{{ Carbon\Carbon::parse($customer->activation_date)->format('d F Y') }}">
                            </div>
                            @if ($errors->has('activation_date'))
                                <span class="text-danger">{{ $errors->first('activation_date') }}</span>
                            @endif
                        @else
                            <input disabled type="text" value="{{ $customer->activation_date }}"
                                class="form-control">
                            <span class="text-danger">{{ __('Server ') }} {{ $customer->mikrotik->name }}
                                {{ __('Offline or Disable!') }}</span>
                        @endif
                    </div>
                </div>

                <div id="map"></div>

            </div>

            <div class="card-footer">
                <button type="submit" class="btn btn-sm btn-success"><i
                        class="fas fa-user-check"></i>&nbsp{{ __('customer.button.update') }}</button>
                <a class="btn btn-sm btn-primary" href="{{ route('customers.edit.router', $customer->slug) }}"><i
                        class="fas fa-user-edit"></i>&nbsp{{ __('customer.button.edit-router') }}</a>
                @if ($customer->internet_service == 'ppp')
                    <a class="btn btn-sm btn-primary" href="{{ route('customers.edit.ppp', $customer->slug) }}"><i
                            class="fas fa-user-edit"></i>&nbsp{{ __('customer.button.edit-ppp') }}</a>
                @elseif ($customer->internet_service == 'ip_static')
                    <a class="btn btn-sm btn-primary" href="{{ route('customers.edit.ppp', $customer->slug) }}"><i
                            class="fas fa-user-edit"></i>&nbsp{{ __('customer.button.edit-ip-static') }}</a>
                @endif
                <a class="btn btn-sm btn-secondary" href="{{ route('customers.index') }}"><i
                        class="fa fa-arrow-left"></i>&nbsp{{ __('customer.button.back') }}</a>
            </div>
            {!! Form::close() !!}
        </div>
    </div>
    @section('custom_styles')
        <link href="{{ asset('css/flatpickr.min.css') }}" rel="stylesheet">
        <style>
            #map {
                height: 100%;
            }
        </style>
    @endsection
    @section('custom_scripts')
        <script src="{{ asset('js/flatpickr') }}"></script>
        <script>
            config = {
                enableTime: false,
                dateFormat: 'd F Y',
                //format:'DD/MM/YYYY HH:mm:ss'
            }
            flatpickr("input[type=datetime-local]", config);
        </script>
    @endsection
</x-layouts.cpanel.customers.app-layout>