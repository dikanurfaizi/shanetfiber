<x-layouts.cpanel.settings.app-layout title="Web System">
    <div class="card-header py-3">
        <h5 class="m-0 font-weight-bold text-primary"> {{ __('websystem.header.edit') }}</h5>
    </div>

    <div class="row">
        <div class="col-lg-12">

            {!! Form::model($websystem, ['method' => 'PATCH', 'route' => ['websystem.update', $websystem->slug]]) !!}
            <div class="card-body col-lg-8">

                <div class="row align-items-center">
                    <div class="col-md-12 mb-3">
                        <label for="title"><strong>{{ __('websystem.label.web-title') }}</strong></label>
                        {!! Form::text('title', null, [
                            'placeholder' => __('websystem.placeholder.web-title'),
                            'class' => 'form-control',
                        ]) !!}
                        @if ($errors->has('title'))
                            <span class="text-danger">{{ $errors->first('title') }}</span>
                        @endif
                    </div>
                </div>
                <div class="row align-items-center">
                    <div class="col-md-12 mb-3">
                        <label for="email"><strong>{{ __('websystem.label.email') }}</strong></label>
                        {!! Form::text('email', null, ['placeholder' => __('websystem.placeholder.email'), 'class' => 'form-control']) !!}
                        @if ($errors->has('email'))
                            <span class="text-danger">{{ $errors->first('email') }}</span>
                        @endif
                    </div>
                </div>
                <div class="row align-items-center">
                    <div class="col-md-12 mb-3">
                        <label for="address"><strong>{{ __('websystem.label.address') }}</strong></label>
                        {!! Form::text('address', null, [
                            'placeholder' => __('websystem.placeholder.address'),
                            'class' => 'form-control',
                        ]) !!}
                        @if ($errors->has('address'))
                            <span class="text-danger">{{ $errors->first('address') }}</span>
                        @endif
                    </div>
                </div>
                <div class="row align-items-center">
                    <div class="col-md-12 mb-3">
                        <label for="city"><strong>{{ __('websystem.label.city') }}</strong></label>
                        {!! Form::text('city', null, ['placeholder' => __('websystem.placeholder.city'), 'class' => 'form-control']) !!}
                        @if ($errors->has('city'))
                            <span class="text-danger">{{ $errors->first('city') }}</span>
                        @endif
                    </div>
                </div>
                <div class="row align-items-center">
                    <div class="col-md-12 mb-3">
                        <label for="postal_code"><strong>{{ __('websystem.label.postal_code') }}</strong></label>
                        {!! Form::text('postal_code', null, [
                            'placeholder' => __('websystem.placeholder.postal_code'),
                            'class' => 'form-control',
                        ]) !!}
                        @if ($errors->has('postal_code'))
                            <span class="text-danger">{{ $errors->first('postal_code') }}</span>
                        @endif
                    </div>
                </div>
                <div class="row align-items-center">
                    <div class="col-md-12 mb-3">
                        <label for="phone"><strong>{{ __('websystem.label.phone') }}</strong></label>
                        {!! Form::text('phone', null, ['placeholder' => __('websystem.placeholder.phone'), 'class' => 'form-control']) !!}
                        @if ($errors->has('phone'))
                            <span class="text-danger">{{ $errors->first('phone') }}</span>
                        @endif
                    </div>
                </div>

                <div class="row align-items-center">
                    <div class="col-md-6 mb-3">
                        <label for="title"><strong>{{ __('websystem.label.tax-rate') }} (%)</strong></label>
                        <div class="form-switch mb-2">
                            <input class="form-check-input" value="true" type="checkbox" id="enable_tax_button"
                                name="tax_button" @if ($websystem->enable_tax == true) checked @endif>
                            <label class="form-check-label" for="enable_tax_button">
                                {{ __('websystem.label.enable-tax-button') }}
                            </label>
                        </div>
                        <div id="tax" class="@if ($websystem->enable_tax == false) d-none @endif">
                            {!! Form::text('tax_rate', null, [
                                'placeholder' => __('websystem.placeholder.tax-rate'),
                                'class' => 'form-control',
                            ]) !!}
                            <span class="text-info">{{ __('websystem.info.tax-rate') }}</span>
                        </div>
                        @if ($errors->has('tax_rate'))
                            <span class="text-danger">{{ $errors->first('tax_rate') }}</span>
                        @endif
                    </div>
                </div>

                <div class="card-footer">
                    <button type="submit" class="btn btn-success"><i class="fa fa-save"></i> &nbsp
                        {{ __('websystem.button.update') }}</button>
                </div>
                {!! Form::close() !!}
            </div>
        </div>
    </div>
    @section('custom_scripts')
        <script>
            $('#enable_tax_button').on('click', function(e) {
                if (e.currentTarget.checked) {
                    $('#tax').removeClass('d-none');
                } else {
                    $('#tax').addClass('d-none');
                }
            });
        </script>
    @endsection
</x-layouts.cpanel.settings.app-layout>
