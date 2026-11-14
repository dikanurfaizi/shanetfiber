<x-layouts.cpanel.settings.app-layout title="{{ __('whatsapp.message.page-title-index') }}">

    <div class="card-header py-3 d-flex justify-content-between align-items-center">
        <h5 class="m-0 fw-bold text-primary">{{ __('whatsapp.message.page-title-index') }}</h5>
        <a class="btn btn-sm btn-success" href="{{ route('whatsappmessage.create') }}">
            <i class="fa fa-plus me-1"></i> {{ __('whatsapp.message.button.create') }}
        </a>
    </div>

    <div class="card-body">
        <div class="table-responsive">
            <table id="packagesTable" cellspacing="0" width="100%"
                class="table table-striped table-bordered table-sm datatable-loading no-footer sortable searchable">
                <thead>
                    <tr>
                        <th width="50">#</th>
                        <th>{{ __('whatsapp.message.table.name') }}</th>
                        <th>{{ __('whatsapp.message.table.incoming_message') }}</th>
                        <th>{{ __('whatsapp.message.table.description') }}</th>
                        <th>{{ __('whatsapp.message.table.status') }}</th>
                        <th class="text-end" width="100">{{ __('whatsapp.message.table.actions') }}</th>
                    </tr>
                </thead>
                <tbody>

                    @foreach ($messages as $data)
                        <tr class="@if ($data->disabled === 'true' || $data->disabled === true) text-secondary @else text-primary @endif">
                            <td><span class="badge rounded-pill bg-light text-dark">{{ ++$i }}</span></td>
                            <td>
                                <span class="fw-semibold">
                                    <i class="fas fa-chalkboard me-1 text-muted"></i> {{ $data->name }}
                                </span>
                            </td>
                            <td>
                                <code>{{ $data->incoming_message }}</code>
                            </td>
                            <td>
                                <span class="text-muted">
                                    {{ $data->description ?? '-' }}
                                </span>
                            </td>
                            <td>
                                @if ($data->disabled == false)
                                    <span class="badge text-white bg-success">
                                        <i class="fas fa-lock-open me-1"></i> {{ __('package.info.enabled') }}
                                    </span>
                                @else
                                    <span class="badge text-white bg-secondary">
                                        <i class="fas fa-lock me-1"></i> {{ __('package.info.disabled') }}
                                    </span>
                                @endif
                            </td>
                            <td class="text-end">
                                <x-drop-down-action>
                                    <li>
                                        <a class="dropdown-item"
                                            href="{{ route('whatsappmessage.show', ['whatsappmessage' => $data->slug]) }}">
                                            <i class="far fa-eye me-2 text-success"></i>
                                            {{ __('whatsapp.message.button.show-message') }}
                                        </a>
                                    </li>

                                    <li>
                                        <a class="dropdown-item"
                                            href="{{ route('whatsappmessage.edit', ['whatsappmessage' => $data->slug]) }}">
                                            <i class="far fa-edit me-2 text-success"></i>
                                            {{ __('whatsapp.message.button.edit-message') }}
                                        </a>
                                    </li>

                                    <li>
                                        <form method="POST" class="d-inline"
                                            action="{{ route('whatsappmessage.disable', ['whatsappmessage' => $data->slug]) }}">
                                            @csrf
                                            @method('PUT')
                                            <a class="dropdown-item" href="#"
                                                onclick="event.preventDefault(); this.closest('form').submit();">
                                                @if ($data->disabled == false)
                                                    <i class="fa fa-times text-warning me-2"></i>
                                                    {{ __('whatsapp.message.button.disable-message') }}
                                                @else
                                                    <i class="fa fa-check text-success me-2"></i>
                                                    {{ __('whatsapp.message.button.enable-message') }}
                                                @endif
                                            </a>
                                        </form>
                                    </li>

                                    <li>
                                        <a class="dropdown-item text-danger"
                                            href="{{ route('whatsappmessage.delete', ['whatsappmessage' => $data->slug]) }}">
                                            <i class="fa fa-trash me-2"></i>
                                            {{ __('whatsapp.message.button.delete-message') }}
                                        </a>
                                    </li>
                                </x-drop-down-action>
                            </td>
                        </tr>
                    @endforeach

                </tbody>
            </table>
        </div>

        <div class="mt-3">
            {{ $messages->links() }}
        </div>
    </div>

</x-layouts.cpanel.settings.app-layout>
