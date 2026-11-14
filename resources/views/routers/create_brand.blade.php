<x-layouts.cpanel.settings.app-layout title="Create Merk Ont">
    <div class="card-header py-3">
        <h5 class="m-0 fw-bold text-primary">{{ __('router.header.create-brand') }}</h5>
    </div>

    <div class="row">
        <div class="col-lg-12">

            <form action="{{ route('brand.store') }}" method="POST">
                @csrf

                <div class="card-body col-lg-8">

                    <div class="row align-items-center">
                        <div class="col-md-12 mb-3">
                            <label for="name"><strong>{{ __('router.label.brand') }}</strong></label>
                            <div class="input-group">
                                <input type="text" name="name" id="name" class="form-control"
                                    placeholder="{{ __('router.placeholder.name-brand') }}" value="{{ old('name') }}">
                            </div>
                            @error('name')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="card-footer">
                        <button type="submit" class="btn btn-sm btn-success">
                            <i class="fa fa-save"></i> &nbsp; {{ __('router.button.save') }}
                        </button>
                        <a class="btn btn-sm btn-secondary" href="{{ url()->previous() }}">
                            <i class="fa fa-arrow-left"></i> &nbsp; {{ __('router.button.cancel') }}
                        </a>
                    </div>

                </div>
            </form>

        </div>
    </div>

</x-layouts.cpanel.settings.app-layout>
