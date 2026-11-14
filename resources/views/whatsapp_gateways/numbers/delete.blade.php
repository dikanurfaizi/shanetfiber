<x-layouts.cpanel.settings.app-layout title="{{ __('whatsapp.number.page-title-deleted-index') }}">

    <div class="card-header py-3 alert-danger">
        <h5 class="m-0 fw-bold"> {{ __('whatsapp.number.page-title-deleted-index') }}</h5>
    </div>

    <div class="row">
        <div class="col-lg-12">
            <form action="{{ route('whatsappnumber.destroy', ['whatsappnumber' => $whatsappnumber->slug]) }}"
                method="POST">
                @csrf
                @method('DELETE')

                <div class="card-body col-lg-10">
                    <div class="col-md-12 mb-3">
                        <div class="alert alert-danger">
                            <strong>{{ __('whatsapp.number.message.delete-number', ['number' => $whatsappnumber->number]) }}</strong><br><br>

                            <div class="mt-3">
                                @can('server-delete')
                                    <button type="submit" class="btn btn-sm btn-danger">
                                        <i class="fa fa-trash me-2"></i>&nbsp;{{ __('server.button.delete') }}
                                    </button>
                                @endcan

                                <a class="btn btn-sm btn-secondary" href="{{ URL::previous() }}">
                                    <i class="fa fa-arrow-left"></i>&nbsp;{{ __('server.button.cancel') }}
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</x-layouts.cpanel.settings.app-layout>
