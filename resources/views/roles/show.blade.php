<x-layouts.cpanel.settings.app-layout title="Show Role">
    <div class="card-header py-3">
        <h5 class="m-0 fw-bold text-primary">{{ __('roles.header.show') }}</h5>
        @can('role-create')
            <a class="btn btn-sm btn-success mt-2" href="{{ route('roles.create') }}">
                <i class="fa fa-plus"></i> {{ __('roles.button.create') }}
            </a>
        @endcan
    </div>

    <!-- Content-->
    <div class="card-body">
        <div class="row mt-4 col-4">
            <div class="card-shadow">
                <div class="card">
                    <div class="card-header">
                        {{ __('roles.header.show') }}
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <div class="mb-3">
                                    <strong>{{ __('roles.label.name') }}</strong>
                                    {{ $role->name }}
                                </div>
                            </div>
                            <div class="col-md-12 mb-3">
                                <div class="mb-3">
                                    <strong>{{ __('roles.label.badge') }}</strong>
                                    <span class="badge bg-success text-white">{{ $role->name }}</span>
                                </div>
                            </div>
                            <div class="col-md-12 mb-3">
                                <div class="mb-3">
                                    <strong>{{ __('roles.label.permissions') }}</strong>
                                    @if (!empty($rolePermissions))
                                        @foreach ($rolePermissions as $v)
                                            <label class="label label-success"><span class="badge bg-danger text-white">
                                                    {{ $v->name }}</span></label>
                                        @endforeach
                                    @endif
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-lg-12 margin-tb">
                                    <div class="pull-right">
                                        <a class="btn btn-secondary" href="{{ route('roles.index') }}"><i
                                                class="fa fa-arrow-left"></i> &nbsp {{ __('roles.button.back') }}</a>
                                        @can('role-edit')
                                            <a class="btn btn-primary" href="{{ route('roles.edit', $role->id) }}"><i
                                                    class="fa fa-edit"></i> &nbsp {{ __('roles.button.edit') }}</a>
                                        @endcan
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layouts.cpanel.settings.app-layout>
