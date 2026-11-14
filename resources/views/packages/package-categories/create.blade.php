<x-layouts.app-layout title="Create Package Category">
    <div class="card-header py-3">
        <h5 class="m-0 fw-bold text-primary"> {{ __('package_categories.header.create') }}</h5>
    </div>

    <div class="row">
        <div class="col-lg-12">

            <form action="{{ route('categories.store') }}" method="POST">
                @csrf

            <div class="card-body col-lg-8">

                <div class="row align-items-center">
                    <div class="col-md-12 mb-3">
                        <label for="name"><strong>{{ __('package_categories.label.name') }}</strong></label>
                        <div class="input-group">
                            <input type="text" name="name" class="form-control" placeholder="{{ __('package_categories.placeholder.name') }}" value="{{ old('name') }}">
                        </div>
                        @if ($errors->has('name'))
                            <span class="text-danger">{{ $errors->first('name') }}</span>
                        @endif
                    </div>
                </div>

                <div class="row align-items-center">
                    <div class="col-md-12 mb-3">
                        <label
                            for="description"><strong>{{ __('package_categories.label.description') }}</strong></label>
                        <div class="input-group">
                            <input type="text" name="description" class="form-control" placeholder="{{ __('package_categories.placeholder.description') }}" value="{{ old('description') }}">
                        </div>
                        @if ($errors->has('description'))
                            <span class="text-danger">{{ $errors->first('description') }}</span>
                        @endif
                    </div>
                </div>
            </div>
            <div class="card-footer">
                <button type="submit" class="btn btn-sm btn-success"><i class="fa fa-save"></i> &nbsp
                    {{ __('package_categories.button.save') }}</button>
            </div>
            </form>
        </div>
    </div>
</x-layouts.app-layout>
