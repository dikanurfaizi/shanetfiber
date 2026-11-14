<x-layouts.cpanel.settings.app-layout title="Delete Brand">

    <div class="card-header py-3 alert-danger">
        <h5 class="m-0 fw-bold">{{ __('router.header.delete-brand') }}</h5>
        <span>{{ $brand->name }}</span>
    </div>

    <div class="row">
        <div class="col-lg-12">

            <form action="{{ route('brand.destroy', ['brand' => $brand->id]) }}" method="POST">
                @csrf
                @method('DELETE')

                <div class="card-body col-lg-10">
                    <div class="col-md-12 mb-3">
                        <div class="alert alert-danger">
                            <strong>{{ __('Are you sure to delete router brand ') }}{{ $brand->name }}?</strong><br><br>
                            {{ __('After delete, it cannot be recovered.') }}<br><br>
                            {{ __('Press') }}
                            <i class="fa fa-trash me-2"></i>&nbsp;<strong>{{ __('router.button.delete') }}</strong>
                            {{ __('to delete this router brand.') }}<br>

                            {{ __('Press') }}
                            <i class="fa fa-arrow-left"></i>&nbsp;<strong>{{ __('router.button.cancel') }}</strong>
                            {{ __('to cancel this operation.') }}
                            <div class="mt-3">
                                @can('server-delete')
                                    <button type="submit" class="btn btn-sm btn-danger">
                                        <i class="fa fa-trash me-2"></i>&nbsp;{{ __('router.button.delete') }}
                                    </button>
                                @endcan
                                <a class="btn btn-sm btn-secondary" href="{{ url()->previous() }}">
                                    <i class="fa fa-arrow-left"></i>&nbsp;{{ __('router.button.cancel') }}
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </form>

        </div>
    </div>
</x-layouts.cpanel.settings.app-layout>
