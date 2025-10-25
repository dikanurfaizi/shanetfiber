<x-layouts.app-layout title="Edit Package">
    <div class="row">
        <div class="col-lg-12">
            {!! Form::model($package_category, [
                'method' => 'PATCH',
                'route' => ['categories.update', $package_category->slug],
            ]) !!}
            <div class="card-body col-lg-8">

                <div class="row align-items-center">
                    <div class="col-md-12 mb-3">
                        <label for="name"><strong>{{ __('package_categories.label.name') }}</strong></label>
                        {!! Form::text('name', null, [
                            'placeholder' => __('package_categories.placeholder.name'),
                            'class' => 'form-control',
                        ]) !!}
                        @if ($errors->has('name'))
                            <span class="text-danger">{{ $errors->first('name') }}</span>
                        @endif
                    </div>
                </div>

                <div class="row align-items-center">
                    <div class="col-md-12 mb-3">
                        <label for="description"><strong>{{ __('package_categories.label.description') }}</strong></label>
                        {!! Form::text('description', null, [
                            'placeholder' => __('package_categories.placeholder.description'),
                            'class' => 'form-control',
                        ]) !!}
                        @if ($errors->has('description'))
                            <span class="text-danger">{{ $errors->first('description') }}</span>
                        @endif
                    </div>
                </div>
            </div>
            <div class="card-footer">
                <button type="submit" class="btn btn-success"><i class="fa fa-save"></i> &nbsp
                    {{ __('package_categories.button.update') }}</button>
                <a class="btn btn-secondary" href="{{ route('categories.index') }}"><i class="fa fa-arrow-left"></i>
                    &nbsp
                    {{ __('package_categories.button.cancel') }}</a>
            </div>
            {!! Form::close() !!}
        </div>
    </div>
</x-layouts.app-layout>
