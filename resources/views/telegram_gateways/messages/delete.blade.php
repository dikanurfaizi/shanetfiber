<x-layouts.cpanel.settings.app-layout title="{{ __('telegram.message.page-title-deleted-index') }}">
    <div class="row">
        <div class="col-lg-12">
            <form action="{{ route('telegrammessage.destroy', ['telegrammessage' => $telegrammessage->slug]) }}"
                method="POST">
                @csrf
                @method('DELETE')

                <div class="card-body col-lg-10">
                    <div class="col-md-12 mb-3">
                        <div class="alert alert-danger">
                            <strong>{{ __('telegram.message.message.delete-message', ['name' => $telegrammessage->name]) }}</strong>

                            <div class="mt-4">
                                <button type="submit" class="btn btn-sm btn-danger me-1">
                                    <i class="fa fa-trash me-1"></i> {{ __('telegram.message.button.delete-message') }}
                                </button>

                                <a class="btn btn-sm btn-secondary" href="{{ url()->previous() }}">
                                    <i class="fa fa-arrow-left me-1"></i> {{ __('telegram.message.button.cancel') }}
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</x-layouts.cpanel.settings.app-layout>
