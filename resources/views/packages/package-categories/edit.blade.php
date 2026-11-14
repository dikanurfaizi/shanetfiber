<x-layouts.app-layout title="Edit Package">
    <div class="row">
        <div class="col-lg-12">
            <form action="{{ route('categories.update', $package_category->slug) }}" method="POST">
                @csrf
                @method('PATCH')
            <div class="card-body col-lg-8">

                <div class="row align-items-center">
                    <div class="col-md-12 mb-3">
                        <label for="name"><strong>{{ __('package_categories.label.name') }}</strong></label>
                        <input type="text" name="name" class="form-control" placeholder="{{ __('package_categories.placeholder.name') }}" value="{{ old('name', $package_category->name) }}">
                        @if ($errors->has('name'))
                            <span class="text-danger">{{ $errors->first('name') }}</span>
                        @endif
                    </div>
                </div>

                <div class="row align-items-center">
                    <div class="col-md-12 mb-3">
                        <label for="description"><strong>{{ __('package_categories.label.description') }}</strong></label>
                        <input type="text" name="description" class="form-control" placeholder="{{ __('package_categories.placeholder.description') }}" value="{{ old('description', $package_category->description) }}">
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
            </form>
        </div>
    </div>
</x-layouts.app-layout>
