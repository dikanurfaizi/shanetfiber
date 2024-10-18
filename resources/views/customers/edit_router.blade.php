<x-layouts.cpanel.customers.app-layout title="Edit Router">

    <div class="card-header py-3">
        <h5 class="m-0 font-weight-bold text-primary"> {{ __('customer.header.edit-router') }} - {{ $customer->name}}</h5>
    </div>

    <div class="row">
        <div class="col-lg-12">
            {!! Form::model($customerRouter, ['method' => 'PATCH','route' => ['customers.update.router', $customer->slug]]) !!}
            <div class="card-body col-lg-8">

                <div class="row align-items-center">
                    <div class="col-md-12 mb-3">
                        <label for="merk_ont"><strong>{{ __('customer.label.merk-router') }}</strong></label>
                        <select id="merk-ont-dropdown" name="merk_ont" class="form-control">
                            <option value="">{{ __('customer.placeholder.select-merk-router') }}</option>
                            @foreach ($merkRouters as $merkRouter)
                            <option value="{{ $merkRouter->name ?? 'no data' }}" @selected($customerRouter->merk_ont==$merkRouter->name) }}">
                                {{ $merkRouter->name ?? 'no data' }}
                            </option>
                            @endforeach
                        </select>
                        @if ($errors->has('merk_ont'))
                        <span class="text-danger">{{ $errors->first('merk_ont') }}</span>
                        @endif
                    </div>
                </div>
                <div class="row align-items-center">
                    <div class="col-md-12 mb-3">
                        <label for="type_ont"><strong>{{ __('customer.label.type-router') }}</strong>
                        </label>
                        <select id="type-ont-dropdown" name="type_ont" class="form-control dynamic" data-dependent="details">
                            <option value="">{{ __('customer.placeholder.select-type-router') }}</option>
                            @foreach ($typeRouters as $typeRouter)
                            <option value="{{ $typeRouter->name ?? 'no data' }}" @selected($customerRouter->type_ont==$typeRouter->name) }}">
                                {{ $typeRouter->name ?? 'no data' }}
                            </option>
                            @endforeach
                        </select>
                        @if ($errors->has('type_ont'))
                        <span class="text-danger">{{ $errors->first('type_ont') }}</span>
                        @endif
                    </div>
                </div>


                <div class="row align-items-center">
                    <div class="col-md-12 mb-3">
                        <label for="address"><strong>{{ __('customer.label.username-router') }}</strong></label>

                        {!! Form::text('username', null, array('placeholder' => __('customer.placeholder.username-router'),'class' => 'form-control')) !!}

                        @if ($errors->has('username'))
                        <span class="text-danger">{{ $errors->first('username') }}</span>
                        @endif
                    </div>
                </div>
                <div class="row align-items-center">
                    <div class="col-md-12 mb-3">
                        <label for="password"><strong>{{ __('customer.label.password-router') }}</strong></label>

                        {!! Form::text('password', null, array('placeholder' => __('customer.placeholder.password-router'),'class' => 'form-control')) !!}

                        @if ($errors->has('password'))
                        <span class="text-danger">{{ $errors->first('password') }}</span>
                        @endif
                    </div>
                </div>

                <div class="row align-items-center">
                    <div class="col-md-12 mb-3">
                        <label for="port"><strong>{{ __('customer.label.port-router') }}</strong></label>

                        {!! Form::text('port', null, array('placeholder' => __('customer.placeholder.port-router'),'class' => 'form-control')) !!}

                        @if ($errors->has('port'))
                        <span class="text-danger">{{ $errors->first('port') }}</span>
                        @endif
                    </div>
                </div>

                <div class="row align-items-center">
                    <div class="col-md-12 mb-3">
                        <label for="mac_address"><strong>{{ __('customer.label.mac-address') }}</strong></label>

                        {!! Form::text('mac_address', null, array('placeholder' => __('customer.placeholder.mac-address'),'class' => 'form-control')) !!}

                        @if ($errors->has('mac_address'))
                        <span class="text-danger">{{ $errors->first('mac_address') }}</span>
                        @endif
                    </div>
                </div>
                <div class="row align-items-center">
                    <div class="col-md-12 mb-3">
                        <label for="description"><strong>{{ __('customer.label.description') }}</strong></label>

                        {!! Form::text('description', null, array('placeholder' => __('customer.placeholder.description'),'class' => 'form-control')) !!}

                        @if ($errors->has('description'))
                        <span class="text-danger">{{ $errors->first('description') }}</span>
                        @endif
                    </div>
                </div>
            </div>

            <div class="card-footer">
                <button type="submit" class="btn btn-sm btn-success"><i class="fas fa-user-check"></i>&nbsp{{ __('customer.button.update-router') }}</button>
                <a class="btn btn-sm btn-secondary" href="{{ route('customers.edit', $customer->slug) }}"><i class="fa fa-arrow-left"></i>&nbsp{{ __('customer.button.back') }}</a>
                @can ('customer-edit')
                <a class="btn btn-sm btn-primary float-right" href="{{ route('routers.create') }}"><i class="fa fa-plus"></i>&nbsp{{ __('customer.button.create-router') }}</a>
                @endcan
            </div>

            {!! Form::close() !!}
        </div>
    </div>


    @section('custom_scripts')
    <script type="text/javascript">
        $(document).ready(function() {

            /*==================================================
                Type Router Dropdown Change Event
            ====================================================*/
            $('#merk-ont-dropdown').on('change', function() {
                var merkOnt = this.value;
                $("#type-ont-dropdown").html('');
                $.ajax({

                    url: "{{url('api/fetch-type-routers')}}",
                    type: "POST",
                    data: {
                        merk_ont: merkOnt,
                        _token: '{{csrf_token()}}'
                    },
                    dataType: 'json',
                    success: function(result_type_ont) {
                        $('#type-ont-dropdown').html('<option value="">Select Profile</option>');
                        $.each(result_type_ont, function(key, value) {
                            $("#type-ont-dropdown").append('<option value="' + value.name + '">' + value.name + '</option>');
                        });
                    }

                });
            });
        });
    </script>
    @endsection

</x-layouts.cpanel.customers.app-layout>