<x-layouts.cpanel.settings.app-layout title="{{ __('autoisolir.title.index') }}">

    <div class="card-header py-3">
        <h5 class="m-0 font-weight-bold text-primary">{{ __('autoisolir.header.index') }}</h5>
        @can('server-create')
            <span><a class="btn btn-sm btn-success mt-2" href="{{ route('autoisolir.create') }}"><i class="fa fa-plus"></i>
                    {{ __('autoisolir.button.create') }}</a></span>
        @endcan
    </div>

    <div class="card-body">
        <div style="overflow-x: auto;">
            <table id="autoisolirTable" cellspacing="0" width="100%"
                class="table  datatable-loading no-footer sortable searchable">
                <tr>
                    <th>{{ __('autoisolir.table.no') }}</th>
                    <th>{{ __('autoisolir.table.server-name') }}</th>
                    <th>{{ __('autoisolir.table.profile-name') }}</th>
                    <th>{{ __('autoisolir.table.ros-version') }}</th>
                    <th>{{ __('autoisolir.table.activation-date') }}</th>
                    <th>{{ __('autoisolir.table.comment-payment') }}</th>
                    <th>{{ __('autoisolir.table.comment-unpayment') }}</th>
                    <th>{{ __('autoisolir.table.status') }}</th>
                    <th width="80px">{{ __('customer.table.action') }}</th>
                </tr>
                @foreach ($autoisolirs as $key => $autoisolir)
                    <tr>
                        <td>{{ ++$i }}</td>
                        <td>{{ $autoisolir->mikrotik->name }}</td>

                        <td>{{ $autoisolir->profile->profile_name }}</td>
                        <td>
                            @if ($autoisolir->mikrotik->ros_version_id == 1)
                                {{ __('autoisolir.placeholder.select-version-67') }}
                            @elseif ($autoisolir->mikrotik->ros_version_id == 2)
                                {{ __('autoisolir.placeholder.select-version-710') }}
                            @endif
                        </td>
                        <td>
                            @if ($autoisolir->activation_date === \App\Enums\MikrotikStatus::PPPOE_ENABLE->value)
                                <span class="badge text-white bg-danger">
                                    <i class="fas fa-lock"></i> {{ __('autoisolir.status.disabled') }}
                                </span>
                            @endif
                            @if ($autoisolir->activation_date === \App\Enums\MikrotikStatus::PPPOE_DISABLE->value)
                                <span class="badge text-white bg-success">
                                    <i class="fas fa-lock-open"></i> {{ __('autoisolir.status.enabled') }}
                                </span>
                            @endif
                        </td>
                        <td>
                            <span class="badge bg-danger-soft text-danger">
                                <i class="fas fa-comment-alt"></i> {{ $autoisolir->comment_payment }}
                            </span>
                        </td>
                        <td>
                            <span class="badge bg-danger-soft text-danger">
                                <i class="fas fa-comment-alt"></i> {{ $autoisolir->comment_unpayment }}
                            </span>
                        </td>
                        <td>
                            @if ($autoisolir->disabled === \App\Enums\MikrotikStatus::PPPOE_ENABLE->value)
                                <span class="badge text-white bg-success">
                                    <i class="fas fa-lock-open"></i> {{ __('autoisolir.status.enabled') }}
                                </span>
                            @endif
                            @if ($autoisolir->disabled === \App\Enums\MikrotikStatus::PPPOE_DISABLE->value)
                                <span class="badge text-white bg-danger">
                                    <i class="fas fa-lock"></i> {{ __('autoisolir.status.disabled') }}
                                </span>
                            @endif
                        </td>
                        <td>
                            <x-drop-down-action>
                                @can('server-edit')
                                    <li>
                                        @if ($autoisolir->disabled === \App\Enums\MikrotikStatus::PPPOE_DISABLE->value)
                                            <form method="POST" class="d-inline"
                                                action="{{ route('autoisolir.enable', $autoisolir->id) }}">
                                                @csrf
                                                @method('put')
                                                <a class="dropdown-item" href="#"
                                                    onclick="event.preventDefault(); this.closest('form').submit();">
                                                    <i class="fa fa-check me-2 text-success"></i>
                                                    {{ __('autoisolir.action.enable') }}
                                                </a>
                                            </form>
                                        @elseif ($autoisolir->disabled === \App\Enums\MikrotikStatus::PPPOE_ENABLE->value)
                                            <form method="POST" class="d-inline"
                                                action="{{ route('autoisolir.disable', $autoisolir->id) }}">
                                                @csrf
                                                @method('put')
                                                <a class="dropdown-item" href="#"
                                                    onclick="event.preventDefault(); this.closest('form').submit();">
                                                    <i class="fa fa-times text-warning me-2"></i>
                                                    {{ __('autoisolir.action.disable') }}
                                                </a>
                                            </form>
                                        @endif
                                    </li>

                                    <li>
                                        @if ($autoisolir->disabled === \App\Enums\MikrotikStatus::PPPOE_ENABLE->value)
                                            <form method="POST" class="d-inline"
                                                action="{{ route('autoisolir.force_disable', $autoisolir->id) }}">
                                                @csrf
                                                @method('put')
                                                <a class="dropdown-item" href="#"
                                                    onclick="event.preventDefault(); this.closest('form').submit();">
                                                    <i class="fa fa-times text-warning me-2"></i>
                                                    {{ __('autoisolir.action.force-disable') }}
                                                </a>
                                            </form>
                                        @endif
                                    </li>
                                    <li>
                                        <a class="dropdown-item" href="{{ route('autoisolir.edit', $autoisolir->id) }}">
                                            <i class="far fa-edit me-2 text-success"></i>
                                            {{ __('autoisolir.action.edit') }}
                                        </a>
                                    </li>
                                @endcan

                                <li>
                                    <a class="dropdown-item" href="{{ route('autoisolir.show', $autoisolir->id) }}">
                                        <i class="far fa-eye me-2 text-success"></i>
                                        {{ __('autoisolir.action.view') }}
                                    </a>
                                </li>


                                @can('server-delete')
                                    <li>
                                        {!! Form::open([
                                            'method' => 'DELETE',
                                            'route' => ['autoisolir.destroy', $autoisolir->id],
                                            'style' => 'display:inline',
                                        ]) !!}
                                        <a href="#" class="dropdown-item"
                                            onclick="event.preventDefault(); this.closest('form').submit();">
                                            <i class="fa fa-trash me-2 text-danger"></i>
                                            {{ __('autoisolir.action.delete') }}
                                        </a>
                                        {!! Form::close() !!}
                                    </li>
                                @endcan

                            </x-drop-down-action>
                        </td>


                    </tr>
                @endforeach
            </table>
        </div>
        {{ $autoisolirs->links() }}
    </div>
</x-layouts.cpanel.settings.app-layout>
