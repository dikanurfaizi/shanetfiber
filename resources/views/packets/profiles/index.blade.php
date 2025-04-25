<x-layouts.app-layout title="PPP Profiles">

    <div class="card-header py-3">
        <h5 class="m-0 font-weight-bold text-primary">{{ __('paket.profile.header.index') }}</h5>
        <span>
            <a class="btn btn-sm btn-success mt-2" href="{{ route('ppp-profiles.create') }}"><i class="fa fa-plus"></i>
                {{ __('paket.profile.button.create') }}
            </a>
        </span>
        <span>
            <a class="btn btn-sm btn-info mt-2" href="{{ route('ppp.profiles.import.data') }}">
                <i class="fa fa-upload"></i> {{ __('paket.profile.button.import') }}
            </a>
        </span>
    </div>

    <div class="card-body">
        <table id="paketsTable" cellspacing="0" width="100%"
            class="table datatable-loading no-footer sortable searchable">
            <thead>
                <tr>
                    <th>#</th>
                    <th>{{ __('paket.profile.table.name') }}</th>
                    <th>{{ __('paket.profile.table.max_upload') }}</th>
                    <th>{{ __('paket.profile.table.max_download') }}</th>
                    <th>{{ __('paket.profile.table.min_upload') }}</th>
                    <th>{{ __('paket.profile.table.min_download') }}</th>
                    <th>{{ __('paket.profile.table.cir') }}</th>
                    <th>{{ __('paket.profile.table.priority') }}</th>
                    <th class="text-end">{{ __('paket.table.action') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($profiles as $profile)
                    <tr>
                        <td><span class="badge rounded-pill text-secondary">{{ ++$i }}</span></td>
                        <td><span class="badge text-primary">{{ $profile->profile_name }}</span></td>
                        <td><span class="badge text-primary">{{ $profile->max_upload }}</span></td>
                        <td><span class="badge text-primary">{{ $profile->max_download }}</span></td>
                        <td><span class="badge text-primary">{{ $profile->min_upload }}</span></td>
                        <td><span class="badge text-primary">{{ $profile->min_download }}</span></td>
                        <td>
                            <span class="badge text-primary">
                                @if ($profile->rasio_cir)
                                    1 : {{ $profile->rasio_cir }}
                                @endif
                            </span>
                        </td>
                        <td><span class="badge text-primary">{{ $profile->priority }}</span></td>
                        <td class="d-flex items-center justify-content-end">
                            <x-drop-down-action>
                                @can('packet-list')
                                    <li>
                                        <a class="dropdown-item" href="{{ route('profiles.show', $profile->id) }}">
                                            <i class="far fa-eye me-2 text-success"></i>
                                            {{ __('paket.profile.action.show') }}
                                        </a>
                                    </li>
                                @endcan
                                @can('packet-edit')
                                    <li>
                                        <a class="dropdown-item" href="{{ route('profiles.edit', $profile->id) }}">
                                            <i class="far fa-edit me-2 text-success"></i>
                                            {{ __('paket.profile.action.edit') }}
                                        </a>
                                    </li>
                                    <li>
                                        @if ($profile->disabled === \App\Enums\MikrotikStatus::PPPOE_ENABLE->value)
                                            <form method="POST" class="d-inline"
                                                action="{{ route('profile.disable', $profile->id) }}">
                                                @csrf
                                                @method('put')
                                                <a class="dropdown-item" href="#"
                                                    onclick="event.preventDefault(); this.closest('form').submit();">
                                                    <i class="fa fa-times text-warning me-2"></i>
                                                    {{ __('paket.profile.action.disable') }}
                                                </a>
                                            </form>
                                        @else
                                            <form method="POST" class="d-inline"
                                                action="{{ route('profile.enable', $profile->id) }}">
                                                @csrf
                                                @method('put')
                                                <a class="dropdown-item" href="#"
                                                    onclick="event.preventDefault(); this.closest('form').submit();">
                                                    <i class="fa fa-check me-2 text-success"></i>
                                                    {{ __('paket.profile.action.enable') }}
                                                </a>
                                            </form>
                                        @endif

                                    </li>
                                @endcan
                                @can('packet-delete')
                                    <li>
                                        <a class="dropdown-item" href="{{ route('profile.delete', $profile->id) }}">
                                            <i class="fa fa-trash me-2 text-danger"></i>
                                            {{ __('paket.profile.action.delete') }}
                                        </a>
                                    </li>
                                @endcan
                            </x-drop-down-action>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        {{ $profiles->links() }}
    </div>

</x-layouts.app-layout>
