<x-layouts.app-layout title="Pakets">

    <div class="card-header py-3">
        <h5 class="m-0 font-weight-bold text-primary">{{ __('paket.header.index') }} - {{ count($pakets) }} Pakets</h5>
        <span><a class="btn btn-sm btn-success mt-2" href="{{ route('packets.create') }}"><i class="fa fa-plus"></i>
                {{ __('paket.button.create') }}</a></span>
    </div>

    <div class="card-body">
        <div style="overflow-x: auto;">
            <table id="paketsTable" cellspacing="0" width="100%"
                class="table datatable-loading no-footer sortable searchable">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>{{ __('paket.table.name') }}</th>
                        <th>{{ __('paket.table.price') }}</th>
                        <th>{{ __('paket.table.server') }}</th>
                        <th>{{ __('paket.table.status') }}</th>
                        <th>{{ __('paket.table.description') }}</th>
                        <th class="text-end">{{ __('paket.table.action') }}</th>
                    </tr>
                </thead>
                <tbody>

                    @foreach ($pakets as $paket)
                        <tr class="@if ($paket->disabled === 'true') text-secondary @else text-primary @endif">
                            <td><span class="badge rounded-pill">{{ ++$i }}</span></td>
                            <td><span class="badge"><i class="fas fa-chalkboard"></i> {{ $paket->name }}</span></td>
                            <td><span
                                    class="badge @if ($paket->disabled === 'true') text-secondary @else text-primary @endif">
                                    @if ($paket->price === 0)
                                        {{ __('Free') }}
                                    @else
                                        @moneyIDR($paket->price)
                                    @endif
                                </span></td>
                            <td>
                                <span
                                    class="badge @if ($paket->disabled === 'true') bg-secondary @else bg-primary @endif text-white">
                                    <i class="fas fa-server"></i> {{ $paket->mikrotik->name ?? 'no data' }}
                                </span>
                            </td>
                            <td>
                                @if ($paket->disabled === \App\Enums\MikrotikStatus::PPPOE_ENABLE->value)
                                    <span class="badge text-white bg-success">
                                        <i class="fas fa-lock-open"></i> {{ __('paket.info.enabled') }}
                                    </span>
                                @endif
                                @if ($paket->disabled === \App\Enums\MikrotikStatus::PPPOE_DISABLE->value)
                                    <span class="badge text-white bg-secondary">
                                        <i class="fas fa-lock"></i> {{ __('paket.info.disabled') }}
                                    </span>
                                @endif

                            </td>
                            <td><span class="badge bg-blue-soft">{{ $paket->description ?? 'No Description' }}</span>
                            </td>
                            <td class="d-flex items-center justify-content-end">
                                <x-drop-down-action>
                                    @can('packet-list')
                                        <li>
                                            <a class="dropdown-item" href="{{ route('packets.show', $paket->slug) }}">
                                                <i class="far fa-eye me-2 text-success"></i>
                                                {{ __('paket.action.show') }}
                                            </a>
                                        </li>
                                    @endcan
                                    @can('packet-edit')
                                        <li>
                                            <a class="dropdown-item" href="{{ route('packets.edit', $paket->slug) }}">
                                                <i class="far fa-edit me-2 text-success"></i>
                                                {{ __('paket.action.edit') }}
                                            </a>
                                        </li>
                                        <li>
                                            @if ($paket->disabled === \App\Enums\MikrotikStatus::PPPOE_ENABLE->value)
                                                <form method="POST" class="d-inline"
                                                    action="{{ route('paket.disable', $paket->slug) }}">
                                                    @csrf
                                                    @method('put')
                                                    <a class="dropdown-item" href="#"
                                                        onclick="event.preventDefault(); this.closest('form').submit();">
                                                        <i class="fa fa-times text-warning me-2"></i>
                                                        {{ __('paket.action.disable') }}
                                                    </a>
                                                </form>
                                            @else
                                                <form method="POST" class="d-inline"
                                                    action="{{ route('paket.enable', $paket->slug) }}">
                                                    @csrf
                                                    @method('put')
                                                    <a class="dropdown-item" href="#"
                                                        onclick="event.preventDefault(); this.closest('form').submit();">
                                                        <i class="fa fa-check me-2 text-success"></i>
                                                        {{ __('paket.action.enable') }}
                                                    </a>
                                                </form>
                                            @endif

                                        </li>
                                    @endcan
                                    @can('packet-delete')
                                        <li>
                                            <a class="dropdown-item" href="{{ route('paket.delete', $paket->slug) }}">
                                                <i class="fa fa-trash me-2 text-danger"></i>
                                                {{ __('paket.action.delete') }}
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
        {{ $pakets->links() }}
    </div>
</x-layouts.app-layout>
