<x-layouts.cpanel.settings.app-layout title="Edit Router">
    <div class="card-header py-3">
        <h5 class="m-0 font-weight-bold text-primary"> {{ __('router.header.edit') }}</h5>
    </div>

    <div class="row">
        <div class="col-lg-12">
            {!! Form::model($router, ['method' => 'PATCH','route' => ['routers.update', $router->id]]) !!}
            <div class="card-body col-lg-8">

                <div class="row align-items-center">
                    <div class="col-md-12 mb-3">
                        <label for="name"><strong>{{ __('router.label.type') }}</strong></label>
                        <div class="input-group">
                            {!! Form::text('name', null, array('placeholder' => __('router.placeholder.type'),'class' => 'form-control')) !!}
                        </div>
                        @if ($errors->has('name'))
                        <span class="text-danger">{{ $errors->first('name') }}</span>
                        @endif
                    </div>
                </div>


                <div class="card-footer">
                    <button type="submit" class="btn btn-sm btn-success"><i class="fa fa-save"></i> &nbsp {{ __('router.button.save') }}</button>
                    <a class="btn btn-sm btn-secondary" href="{{ URL::previous() }}"><i class="fa fa-arrow-left"></i>&nbsp{{ __('server.button.cancel') }}</a>

                </div>
                {!! Form::close() !!}
            </div>
        </div>
</x-layouts.cpanel.settings.app-layout>