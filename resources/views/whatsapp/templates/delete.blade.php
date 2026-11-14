<x-layouts.cpanel.settings.app-layout title="{{ __('whatsapp.message.page-title-deleted-index') }}">
    <div class="row">
        <div class="col-lg-12">
            <form action="{{ route('whatsappmessage.destroy', ['whatsappmessage' => $whatsappmessage->slug]) }}"
                method="POST">
                @csrf
                @method('DELETE')

                <div class="card-body col-lg-10">
                    <div class="col-md-12 mb-3">
                        <div class="alert alert-danger">
                            <strong>{{ __('whatsapp.message.message.delete-message', ['name' => $whatsappmessage->name]) }}</strong><br><br>

                            <div class="mt-3">
                                <button type="submit" class="btn btn-sm btn-danger">
                                    <i class="fa fa-trash me-2"></i>&nbsp;{{ __('server.button.delete') }}
                                </button>

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
