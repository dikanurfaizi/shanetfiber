<x-layouts.cpanel.settings.app-layout title="{{ __('user.title.delete') }}">

    <div class="card-header py-3 alert-danger">
        <h5 class="m-0 fw-bold"> {{ __('user.header.delete', ['user' => $user->name]) }}</h5>
    </div>

    <div class="row">
        <div class="col-lg-12">

            {!! Form::open(['method' => 'DELETE', 'route' => ['users.destroy', $user->id]]) !!}
            <div class="card-body col-lg-10">
                <div class="col-md-12 mb-3">
                    <div class="alert alert-danger">
                        <strong>{{ __('user.info.are_you_sure_to_delete_user', ['user' => $user->name]) }}</strong><br>
                        {{ __('user.info.after-delete') }}<br><br>
                        {{ __('user.info.press-button', ['button-delete' => 'delete', 'button-cancel' => 'cancel', 'user' => $user->name]) }}
                        <div class="mt-3">
                            @can('user-delete')
                                <button type="submit" class="btn btn-sm btn-danger"><i
                                        class="fa fa-trash me-2"></i>&nbsp{{ __('user.button.delete') }}</button>
                            @endcan
                            <a class="btn btn-sm btn-secondary" href="{{ URL::previous() }}"><i
                                    class="fa fa-arrow-left"></i>&nbsp{{ __('user.button.cancel') }}</a>
                        </div>
                    </div>
                </div>
            </div>
            {!! Form::close() !!}
        </div>
    </div>
</x-layouts.cpanel.settings.app-layout>
