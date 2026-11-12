<x-layouts.cpanel.settings.app-layout title="Crate Type Ont">
    <div class="card-header py-3">
        <h5 class="m-0 fw-bold text-primary"> {{ __('router.header.create') }}</h5>
    </div>

    <div class="row">
        <div class="col-lg-12">

            {!! Form::open(['route' => 'routers.store', 'method' => 'POST']) !!}

            <div class="card-body col-lg-8">

                <div class="row align-items-center">
                    <div class="col-md-12 mb-3">
                        <label for="name"><strong>{{ __('router.label.type') }}</strong></label>
                        <div class="input-group">
                            {!! Form::text('name', null, ['placeholder' => __('router.placeholder.type'), 'class' => 'form-control']) !!}
                        </div>
                        @if ($errors->has('name'))
                            <span class="text-danger">{{ $errors->first('name') }}</span>
                        @endif
                    </div>
                </div>

                <div class="row align-items-center">
                    <div class="col-md-12 mb-3">
                        <label for="brand"><strong>{{ __('router.label.brand') }}</strong></label>
                        {!! Form::select('merk_router_id', $merkRouters, null, [
                            'placeholder' => 'Select Brand',
                            'class' => 'form-control',
                            'id' => 'server-dropdown',
                        ]) !!}
                        @if ($errors->has('merk_router_id'))
                            <span class="text-danger">{{ $errors->first('merk_router_id') }}</span>
                        @endif
                    </div>
                </div>
                <div class="row align-items-center">
                    <div class="col-md-12 mb-3">
                        <div class="d-flex justify-content-between">
                            <div class="form-switch">
                                <input class="form-check-input" value="true" type="checkbox" id="checkConnection"
                                    name="server_type">
                                <label class="form-check-label" for="checkConnection">
                                    {{ __('router.label.server-type') }}
                                </label>
                            </div>
                            <div id="badge-connected">
                                <a class="btn btn-sm btn-primary" href="{{ route('brand.index') }}"><i
                                        class="fa fa-server"></i>&nbsp{{ __('router.button.show-brand-router') }}</a>

                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-footer">
                    <button type="submit" class="btn btn-sm btn-success"><i class="fa fa-save"></i> &nbsp
                        {{ __('router.button.save') }}</button>
                    <a class="btn btn-sm btn-secondary" href="{{ URL::previous() }}"><i
                            class="fa fa-arrow-left"></i>&nbsp{{ __('server.button.cancel') }}</a>

                </div>
                {!! Form::close() !!}
            </div>
        </div>
    </div>
</x-layouts.cpanel.settings.app-layout>
