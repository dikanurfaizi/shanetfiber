<x-layouts.cpanel.settings.app-layout title="Create Type Ont">
    <div class="card-header py-3">
        <h5 class="m-0 fw-bold text-primary">{{ __('router.header.create') }}</h5>
    </div>

    <div class="row">
        <div class="col-lg-12">

            <form action="{{ route('routers.store') }}" method="POST">
                @csrf

                <div class="card-body col-lg-8">

                    <div class="row align-items-center">
                        <div class="col-md-12 mb-3">
                            <label for="name"><strong>{{ __('router.label.type') }}</strong></label>
                            <div class="input-group">
                                <input type="text" name="name" id="name" class="form-control"
                                    placeholder="{{ __('router.placeholder.type') }}" value="{{ old('name') }}">
                            </div>
                            @error('name')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="row align-items-center">
                        <div class="col-md-12 mb-3">
                            <label for="server-dropdown"><strong>{{ __('router.label.brand') }}</strong></label>
                            <select name="merk_router_id" id="server-dropdown" class="form-control">
                                <option value="">Select Brand</option>
                                @foreach ($merkRouters as $id => $name)
                                    <option value="{{ $id }}" @selected(old('merk_router_id') == $id)>{{ $name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('merk_router_id')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="row align-items-center">
                        <div class="col-md-12 mb-3">
                            <div class="d-flex justify-content-between">
                                <div class="form-switch">
                                    <input class="form-check-input" value="true" type="checkbox" id="checkConnection"
                                        name="server_type" @checked(old('server_type') === 'true')>
                                    <label class="form-check-label" for="checkConnection">
                                        {{ __('router.label.server-type') }}
                                    </label>
                                </div>
                                <div id="badge-connected">
                                    <a class="btn btn-sm btn-primary" href="{{ route('brand.index') }}">
                                        <i class="fa fa-server"></i> &nbsp; {{ __('router.button.show-brand-router') }}
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card-footer">
                        <button type="submit" class="btn btn-sm btn-success">
                            <i class="fa fa-save"></i> &nbsp; {{ __('router.button.save') }}
                        </button>
                        <a class="btn btn-sm btn-secondary" href="{{ url()->previous() }}">
                            <i class="fa fa-arrow-left"></i> &nbsp; {{ __('server.button.cancel') }}
                        </a>
                    </div>

                </div>
            </form>

        </div>
    </div>
</x-layouts.cpanel.settings.app-layout>
