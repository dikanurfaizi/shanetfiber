<x-layouts.cpanel.settings.app-layout title="Delete Router">

    <div class="card-header py-3 alert-danger">
        <h5 class="m-0 font-weight-bold"> {{ __('router.header.delete') }}</h5>
        <span>{{ $router->merk_router->name }} {{ $router->name }}</span>
    </div>

    <div class="row">
        <div class="col-lg-12">

            {!! Form::open(['method' => 'DELETE','route' => ['routers.destroy', ['router' => $router->id]]]) !!}
            <div class="card-body col-lg-10">
                <div class="col-md-12 mb-3">
                    <div class="alert alert-danger">
                        <strong>{{ __('Are you sure to delete router ') }}{{$router->merk_router->name }} {{$router->name }} ?</strong><br><br>
                        {{ __('After delete, it cannot be recovered.') }}<br><br>
                        {{ __('Press') }}
                        <i class="fa fa-trash me-2"></i>&nbsp<strong>{{ __('router.button.delete') }}</strong>
                        {{ __('to delete this router.') }}<br>

                        {{ __('Press') }}
                        <i class="fa fa-arrow-left"></i>&nbsp<strong>{{ __('router.button.cancel') }}</strong>
                        {{ __('to cancel this operation.') }}
                        <div class="mt-3">
                            @can ('server-delete')
                            <button type="submit" class="btn btn-sm btn-danger"><i class="fa fa-trash me-2"></i>&nbsp{{ __('router.button.delete') }}</button>
                            @endcan
                            <a class="btn btn-sm btn-secondary" href="{{ URL::previous() }}"><i class="fa fa-arrow-left"></i>&nbsp{{ __('router.button.cancel') }}</a>
                        </div>
                    </div>

                </div>
            </div>
            {!! Form::close() !!}
        </div>
    </div>
</x-layouts.cpanel.settings.app-layout>