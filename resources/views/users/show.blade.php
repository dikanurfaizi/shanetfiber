<x-layouts.cpanel.settings.app-layout title="{{ __('user.title.show') }}">
    <div class="card-header py-3">
        <h5 class="m-0 fw-bold text-primary">{{ __('user.header.show') }}</h5>
    </div>
    <div class="row">
        <div class="col-lg-12">
            <!-- Content-->
            <div class="card-body col-lg-8">
                <div class="col-xs-12 col-sm-12 col-md-12">
                    <div class="mb-3">
                        <strong>{{ __('user.label.name') }}</strong>
                        {{ $user->name }}
                    </div>
                </div>
                <div class="col-xs-12 col-sm-12 col-md-12">
                    <div class="mb-3">
                        <strong>{{ __('user.label.email') }}</strong>
                        {{ $user->email }}
                    </div>
                </div>
                <div class="col-xs-12 col-sm-12 col-md-12 mb-3">
                    <div class="mb-3">
                        <strong>{{ __('user.label.roles') }}</strong>
                        @if (!empty($user->getRoleNames()))
                            @foreach ($user->getRoleNames() as $v)
                                <span class="badge bg-success text-white">{{ $v }}</span>
                            @endforeach
                        @endif
                    </div>
                </div>
            </div>
            <div class="card-footer">
                <a class="btn btn-sm btn-secondary" href="{{ route('users.index') }}"><i class="fa fa-arrow-left"></i>
                    &nbsp {{ __('user.button.back') }}</a>
                @can('user-edit')
                    <a class="btn btn-sm btn-primary" href="{{ route('users.edit', $user->id) }}"><i
                            class="fas fa-user-edit"></i> &nbsp {{ __('user.button.edit') }}</a>
                @endcan
            </div>
        </div>
    </div>
</x-layouts.cpanel.settings.app-layout>
