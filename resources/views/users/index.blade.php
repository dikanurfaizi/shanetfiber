<x-layouts.cpanel.settings.app-layout title="Users">
    <div class="card-header py-3">
        <h5 class="m-0 fw-bold text-primary">Users</h5>
        <span><a class="btn btn-sm btn-success mt-2" href="{{ route('users.create') }}"><i class="fas fa-user-plus"></i>
                Add User</a></span>
    </div>

    <div class="card-body">
        <div style="overflow-x: auto;">
            <table id="customersTable" cellspacing="0" width="100%"
                class="table  datatable-loading no-footer sortable searchable">

                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email Address</th>
                        <th>Status</th>
                        <th>Created at</th>
                        <th>Updated in</th>
                        <th class="text-center" width="230px">Action</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach ($users as $user)
                        <tr>
                            <td>{{ $user->name }}</td>
                            <td>{{ $user->email }}</td>
                            <td>
                                @if ($user->disabled === \App\Enums\MikrotikStatus::PPPOE_ENABLE->value)
                                    <span class="badge text-white bg-success">
                                        <i class="fas fa-lock-open"></i> {{ __('user.info.enabled') }}
                                    </span>
                                @elseif ($user->disabled === \App\Enums\MikrotikStatus::PPPOE_DISABLE->value)
                                    <span class="badge text-white bg-danger">
                                        <i class="fas fa-lock"></i> {{ __('user.info.disabled') }}
                                    </span>
                                @endif
                            </td>
                            <td>{{ $user->created_at }}</td>
                            <td>{{ $user->updated_at->diffForhumans() }}</td>
                            <td class="d-flex items-center justify-content-center">
                                @can('user-list')
                                    <a href="{{ route('users.show', $user->id) }}" data-bs-toggle="tooltip"
                                        title="{{ __('users.action.view') }}"
                                        class="btn btn-datatable btn-icon btn-transparent-dark me-2 user-show">
                                        <i class="text-primary fas fa-external-link-alt"></i>
                                    </a>
                                @endcan
                                @can('user-edit')
                                    <a href="{{ route('users.edit', $user->id) }}" data-bs-toggle="tooltip"
                                        title="{{ __('roles.action.edit') }}"
                                        class="btn btn-datatable btn-icon btn-transparent-dark me-2 user-edit">
                                        <i class="text-primary fas fa-edit"></i>
                                    </a>
                                    @if ($user->disabled === \App\Enums\MikrotikStatus::PPPOE_DISABLE->value)
                                        <form method="POST" class="d-inline"
                                            action="{{ route('user.enable', $user->id) }}">
                                            @csrf
                                            @method('put')
                                            <a data-bs-toggle="tooltip" data-bs-placement="top"
                                                title="{{ __('user.action.enable') }}"
                                                class="btn btn-datatable btn-icon btn-transparent-dark me-2 user-edit"
                                                onclick="event.preventDefault(); this.closest('form').submit();">
                                                <i class="text-success fa fa-check"></i>
                                            </a>
                                        </form>
                                    @elseif ($user->disabled === \App\Enums\MikrotikStatus::PPPOE_ENABLE->value)
                                        <form method="POST" class="d-inline"
                                            action="{{ route('user.disable', $user->id) }}">
                                            @csrf
                                            @method('put')
                                            <a data-bs-toggle="tooltip" data-bs-placement="top"
                                                title="{{ __('roles.action.disable') }}" class="btn"
                                                onclick="event.preventDefault(); this.closest('form').submit();">
                                                <i class=" fa fa-times text-danger me-2"></i>
                                            </a>
                                        </form>
                                    @endif
                                @endcan

                                @can('user-delete')
                                    <a href="{{ route('users.delete', $user->id) }}" data-bs-toggle="tooltip"
                                        title="{{ __('roles.action.delete') }}"
                                        class="btn btn-datatable btn-icon btn-transparent-dark me-2 user-delete">
                                        <i class="far fa-trash-alt text-danger"></i>
                                    </a>
                                @endcan

                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        {{ $users->links() }}
    </div>
</x-layouts.cpanel.settings.app-layout>
