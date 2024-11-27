<x-layouts.cpanel.settings.app-layout title="{{ __('whatsapp.number.page-title-add') }}">
    <div class="card-header py-3">
        <h5 class="m-0 font-weight-bold text-primary"> {{ __('whatsapp.number.page-title-add') }}</h5>
    </div>

    <div class="row">
        <div class="col-lg-12">

            {!! Form::open(['route' => 'whatsappnumber.store', 'method' => 'POST']) !!}

            <div class="card-body col-lg-8">

                <div class="row align-items-center">
                    <div class="col-md-12 mb-3">
                        <label for="name"><strong>{{ __('whatsapp.number.label.name') }}</strong></label>
                        <div class="input-group">
                            {!! Form::text('name', null, ['placeholder' => __('whatsapp.number.ph.name'), 'class' => 'form-control']) !!}
                        </div>
                        @if ($errors->has('name'))
                            <span class="text-danger">{{ $errors->first('name') }}</span>
                        @endif
                    </div>
                </div>

                <div class="row align-items-center">
                    <div class="col-md-12 mb-3">
                        <label for="number"><strong>{{ __('whatsapp.number.label.number') }}</strong></label>

                        {!! Form::text('number', null, ['placeholder' => __('whatsapp.number.ph.number'), 'class' => 'form-control']) !!}

                        @if ($errors->has('number'))
                            <span class="text-danger">{{ $errors->first('number') }}</span>
                        @endif
                    </div>
                </div>

                

                <div class="row align-items-center">
                    <div class="col-md-12 mb-3">
                        <label for="description"><strong>{{ __('whatsapp.number.label.description') }}</strong></label>
                        <div class="input-group">
                            {!! Form::text('description', null, [
                                'placeholder' => __('whatsapp.number.ph.description'),
                                'class' => 'form-control',
                            ]) !!}
                        </div>
                        @if ($errors->has('description'))
                            <span class="text-danger">{{ $errors->first('description') }}</span>
                        @endif
                    </div>
                </div>
            </div>
            <div class="card-footer">
                <button type="submit" class="btn btn-sm btn-success"><i class="fa fa-save"></i> &nbsp
                    {{ __('whatsapp.number.button.add-new-number') }}</button>
            </div>
            {!! Form::close() !!}
        </div>
    </div>

    @section('custom_scripts')
    @endsection
</x-layouts.cpanel.settings.app-layout>
