<x-layouts.app-layout title="Delete Package">

    <div class="card-header py-3 alert-danger">
        <h5 class="m-0 fw-bold"> {{ __('package.header.delete') }}</h5>
        <span>{{ $packet->name }}</span>
    </div>

    <div class="row">
        <div class="col-lg-12">

            <form action="{{ route('packets.destroy', ['packet' => $packet->slug]) }}" method="POST">
                @csrf
                @method('DELETE')
            <div class="card-body col-lg-10">
                <div class="col-md-12 mb-3">
                    <div class="alert alert-danger">
                        <strong>{{ __('Are you sure to delete package ') }}{{ $packet->name }} ?</strong><br><br>
                        {{ __('Warning!!! Deleting a packet will delete their all member on this package.') }}<br>
                        {{ __('After delete, it cannot be recovered.') }}<br><br>
                        {{ __('Press') }}
                        <i class="fa fa-trash me-2"></i>&nbsp<strong>{{ __('package.button.delete') }}</strong>
                        {{ __('to delete this package.') }}<br>

                        {{ __('Press') }}
                        <i class="fa fa-arrow-left"></i>&nbsp<strong>{{ __('package.button.cancel') }}</strong>
                        {{ __('to cancel this operation.') }}
                        <div class="mt-3">
                            @can('packet-delete')
                                <button type="submit" class="btn btn-sm btn-danger"><i
                                        class="fa fa-trash me-2"></i>&nbsp{{ __('package.button.delete') }}</button>
                            @endcan
                            <a class="btn btn-sm btn-secondary" href="{{ URL::previous() }}"><i
                                    class="fa fa-arrow-left"></i>&nbsp{{ __('package.button.cancel') }}</a>
                        </div>
                    </div>

                </div>
            </div>
            </form>
        </div>
    </div>
</x-layouts.app-layout>
