<x-layouts.cpanel.settings.app-layout title="Delete Server">

    <div class="card-header py-3 alert-danger">
        <h5 class="m-0 fw-bold">{{ __('server.header.delete', ['server' => $server->name]) }}</h5>
    </div>

    <div class="row">
        <div class="col-lg-12">

            <form action="{{ route('servers.destroy', ['server' => $server->slug]) }}" method="POST">
                @csrf
                @method('DELETE')

                <div class="card-body col-lg-10">
                    <div class="col-md-12 mb-3">
                        <div class="alert alert-danger">
                            <strong>{{ __('server.info.are_you_sure_to_delete_server', ['server' => $server->name]) }}</strong><br><br>
                            {{ __('server.info.warning-delete', ['server' => $server->name]) }}<br>
                            {{ __('After delete, it cannot be recovered.') }}<br><br>
                            {{ __('server.info.press-button', ['button-delete' => 'delete', 'button-cancel' => 'cancel', 'server' => $server->name]) }}

                            <div class="mt-3">
                                @can('server-delete')
                                    <button type="submit" class="btn btn-sm btn-danger">
                                        <i class="fa fa-trash me-2"></i>&nbsp;{{ __('server.button.delete') }}
                                    </button>
                                @endcan
                                <a class="btn btn-sm btn-secondary" href="{{ url()->previous() }}">
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
