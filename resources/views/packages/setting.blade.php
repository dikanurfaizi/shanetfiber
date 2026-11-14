<x-layouts.cpanel.settings.app-layout title="Setting Package">

    <div class="card-header py-3">
        <h5 class="m-0 fw-bold text-primary">{{ __('package.header.export') }}</h5>
    </div>

    <div class="row">
        <div class="col-lg-12">
            <form action="{{ route('packages.export') }}" method="POST">
                @csrf

                <div class="card-body col-lg-8">
                    <div class="row align-items-center">
                        <div class="col-md-12 mb-3">
                            <label for="server-dropdown"><strong>{{ __('customer.label.server') }}</strong></label>

                            <select name="servers" id="server-dropdown" class="form-control">
                                <option value="">Select Server</option>
                                @foreach ($mikrotiks as $id => $name)
                                    <option value="{{ $id }}" @selected(old('servers') == $id)>{{ $name }}
                                    </option>
                                @endforeach
                            </select>

                            @error('servers')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="card-footer">
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-arrow-alt-circle-right"></i> &nbsp; {{ __('customer.button.export') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-layouts.cpanel.settings.app-layout>
