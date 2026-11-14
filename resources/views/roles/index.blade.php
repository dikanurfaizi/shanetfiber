<x-layouts.cpanel.settings.app-layout title="{{ __('roles.title.index') }}">

    <div class="card-header py-3">
        <h5 class="m-0 fw-bold text-primary">{{ __('roles.header.index') }}</h5>
        <span>
            <a class="btn btn-sm btn-success mt-2" href="{{ route('roles.create') }}">
                <i class="fa fa-plus"></i> {{ __('roles.button.create') }}
            </a>
        </span>
    </div>

    <div class="card-body">
        <div style="overflow-x: auto;">
            <table id="rolesTable" cellspacing="0" width="100%"
                class="table datatable-loading no-footer sortable searchable">
                <thead>
                    <tr>
                        <th width="40px">{{ __('roles.table.no') }}</th>
                        <th>{{ __('roles.table.name') }}</th>
                        <th>{{ __('roles.table.badge') }}</th>
                        <th width="280px">{{ __('roles.table.action') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($roles as $key => $role)
                        <tr>
                            <td>{{ ++$i }}</td>
                            <td>{{ $role->name }}</td>
                            <td><span class="badge bg-success text-white">{{ $role->name }}</span></td>
                            <td>
                                @can('role-list')
                                    <a href="{{ route('roles.show', $role->id) }}" data-bs-toggle="tooltip"
                                        data-bs-placement="top" title="{{ __('roles.action.view') }}"
                                        class="btn btn-datatable btn-icon btn-transparent-dark me-2 router-show">
                                        <i class="fas fa-external-link-alt text-success"></i>
                                    </a>
                                @endcan
                                @can('role-edit')
                                    <a href="{{ route('roles.edit', $role->id) }}" data-bs-toggle="tooltip"
                                        data-bs-placement="top" title="{{ __('roles.action.edit') }}"
                                        class="btn btn-datatable btn-icon btn-transparent-dark me-2 router-edit">
                                        <i class="fas fa-edit text-primary"></i>
                                    </a>
                                @endcan

                                @can('role-delete')
                                    <form action="{{ route('roles.destroy', $role->id) }}" method="POST"
                                        style="display:inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                            class="btn text-danger btn-datatable btn-icon btn-transparent-dark me-2"
                                            data-bs-toggle="tooltip" data-bs-placement="top"
                                            title="{{ __('roles.action.delete') }}">
                                            <i class="far fa-trash-alt"></i>
                                        </button>
                                    </form>
                                @endcan
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        {!! $roles->links() !!}
    </div>

</x-layouts.cpanel.settings.app-layout>
