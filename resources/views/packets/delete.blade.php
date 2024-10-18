<x-layouts.app-layout title="Delete Paket">

    <div class="card-header py-3 alert-danger">
        <h5 class="m-0 font-weight-bold"> {{ __('paket.header.delete') }}</h5>
        <span>{{ $packet->name }} on server <span class="badge bg-success text-white">
                <i class="fas fa-server"></i> {{$packet->mikrotik->name}}</span>
        </span>
    </div>

    <div class="row">
        <div class="col-lg-12">

            {!! Form::open(['method' => 'DELETE','route' => ['packets.destroy', ['packet' => $packet->slug]]]) !!}
            <div class="card-body col-lg-10">
                <div class="col-md-12 mb-3">
                    <div class="alert alert-danger">
                        <strong>{{ __('Are you sure to delete paket ') }}{{$packet->name }} ?</strong><br><br>
                        {{ __('Warning!!! Deleting a packet will delete their all member on this paket.') }}<br>
                        {{ __('After delete, it cannot be recovered.') }}<br><br>
                        {{ __('Press') }}
                        <i class="fa fa-trash me-2"></i>&nbsp<strong>{{ __('paket.button.delete') }}</strong>
                        {{ __('to delete this paket.') }}<br>

                        {{ __('Press') }}
                        <i class="fa fa-arrow-left"></i>&nbsp<strong>{{ __('paket.button.cancel') }}</strong>
                        {{ __('to cancel this operation.') }}
                        <div class="mt-3">
                            @can ('packet-delete')
                            <button type="submit" class="btn btn-sm btn-danger"><i class="fa fa-trash me-2"></i>&nbsp{{ __('paket.button.delete') }}</button>
                            @endcan
                            <a class="btn btn-sm btn-secondary" href="{{ URL::previous() }}"><i class="fa fa-arrow-left"></i>&nbsp{{ __('paket.button.cancel') }}</a>
                        </div>
                    </div>

                </div>
            </div>
            {!! Form::close() !!}
        </div>
    </div>
</x-layouts.app-layout>