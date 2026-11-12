<x-layouts.app-layout title="Packages">

    <div class="card-header py-3">
        <h5 class="m-0 fw-bold text-primary">{{ __('package.header.index') }} - {{ count($packages) }} Packages</h5>
        <span><a class="btn btn-sm btn-success mt-2" href="{{ route('packets.create') }}"><i class="fa fa-plus"></i>
                {{ __('package.button.create') }}</a></span>
    </div>

    <div class="card-body">
        <div class="table-responsive">
            <table id="packagesTable" cellspacing="0" width="100%"
                class="table datatable-loading no-footer sortable searchable">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>{{ __('package.table.name') }}</th>
                        <th>{{ __('package.table.price') }}</th>
                        <th>{{ __('package.table.profile') }}</th>
                        <th>{{ __('package.table.status') }}</th>
                        <th>{{ __('package.table.description') }}</th>
                        <th class="text-end">{{ __('package.table.action') }}</th>
                    </tr>
                </thead>
                <tbody>

                    @foreach ($packages as $package)
                        <tr class="@if ($package->disabled === 'true') text-secondary @else text-primary @endif">
                            <td><span class="badge rounded-pill">{{ ++$i }}</span></td>
                            <td><span class="badge"><i class="fas fa-chalkboard"></i> {{ $package->name }}</span></td>
                            <td><span
                                    class="badge @if ($package->disabled === 'true') text-secondary @else text-primary @endif">
                                    @if ($package->price === 0)
                                        {{ __('Free') }}
                                    @else
                                        @moneyIDR($package->price)
                                    @endif
                                </span></td>
                            <td>
                                <span
                                    class="badge @if ($package->disabled === 'true') text-secondary @else text-primary @endif">
                                    {{ $package->profile }}</span>
                            </td>
                            <td>
                                @if ($package->disabled === \App\Enums\MikrotikStatus::PPPOE_ENABLE->value)
                                    <span class="badge text-white bg-success">
                                        <i class="fas fa-lock-open"></i> {{ __('package.info.enabled') }}
                                    </span>
                                @endif
                                @if ($package->disabled === \App\Enums\MikrotikStatus::PPPOE_DISABLE->value)
                                    <span class="badge text-white bg-secondary">
                                        <i class="fas fa-lock"></i> {{ __('package.info.disabled') }}
                                    </span>
                                @endif

                            </td>
                            <td><span class="badge bg-blue-soft">{{ $package->description ?? 'No Description' }}</span>
                            </td>
                            <td class="d-flex items-center justify-content-end">
                                <x-drop-down-action>
                                    @can('packet-list')
                                        <li>
                                            <a class="dropdown-item" href="{{ route('packets.show', $package->slug) }}">
                                                <i class="far fa-eye me-2 text-success"></i>
                                                {{ __('package.action.show') }}
                                            </a>
                                        </li>
                                    @endcan
                                    @can('packet-edit')
                                        <li>
                                            <a class="dropdown-item" href="{{ route('packets.edit', $package->slug) }}">
                                                <i class="far fa-edit me-2 text-success"></i>
                                                {{ __('package.action.edit') }}
                                            </a>
                                        </li>
                                        <li>
                                            @if ($package->disabled === \App\Enums\MikrotikStatus::PPPOE_ENABLE->value)
                                                <form method="POST" class="d-inline"
                                                    action="{{ route('disable.package', $package->slug) }}">
                                                    @csrf
                                                    @method('put')
                                                    <a class="dropdown-item" href="#"
                                                        onclick="event.preventDefault(); this.closest('form').submit();">
                                                        <i class="fa fa-times text-warning me-2"></i>
                                                        {{ __('package.profile.action.disable') }}
                                                    </a>
                                                </form>
                                            @else
                                                <form method="POST" class="d-inline"
                                                    action="{{ route('enable.package', $package->slug) }}">
                                                    @csrf
                                                    @method('put')
                                                    <a class="dropdown-item" href="#"
                                                        onclick="event.preventDefault(); this.closest('form').submit();">
                                                        <i class="fa fa-check me-2 text-success"></i>
                                                        {{ __('package.profile.action.enable') }}
                                                    </a>
                                                </form>
                                            @endif

                                        </li>
                                    @endcan
                                    @can('packet-delete')
                                        <li>
                                            <a class="dropdown-item" href="{{ route('package.delete', $package->slug) }}">
                                                <i class="fa fa-trash me-2 text-danger"></i>
                                                {{ __('package.action.delete') }}
                                            </a>
                                        </li>
                                    @endcan
                                </x-drop-down-action>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        {{ $packages->links() }}
    </div>
</x-layouts.app-layout>
