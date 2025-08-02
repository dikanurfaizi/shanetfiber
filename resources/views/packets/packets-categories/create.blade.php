<x-layouts.app-layout title="Create Package Category">
    <div class="card-header py-3">
        <h5 class="m-0 font-weight-bold text-primary"> {{ __('paket_categories.header.create') }}</h5>
    </div>

    <div class="row">
        <div class="col-lg-12">

            {!! Form::open(['route' => 'categories.store', 'method' => 'POST']) !!}

            <div class="card-body col-lg-8">

                <div class="row align-items-center">
                    <div class="col-md-12 mb-3">
                        <label for="name"><strong>{{ __('paket_categories.label.name') }}</strong></label>
                        <div class="input-group">
                            {!! Form::text('name', null, [
                                'placeholder' => __('paket_categories.placeholder.name'),
                                'class' => 'form-control',
                            ]) !!}
                        </div>
                        @if ($errors->has('name'))
                            <span class="text-danger">{{ $errors->first('name') }}</span>
                        @endif
                    </div>
                </div>

                <div class="row align-items-center">
                    <div class="col-md-12 mb-3">
                        <label for="description"><strong>{{ __('paket_categories.label.description') }}</strong></label>
                        <div class="input-group">
                            {!! Form::text('description', null, [
                                'placeholder' => __('paket_categories.placeholder.description'),
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
                    {{ __('paket_categories.button.save') }}</button>
            </div>
            {!! Form::close() !!}
        </div>
    </div>
</x-layouts.app-layout>
