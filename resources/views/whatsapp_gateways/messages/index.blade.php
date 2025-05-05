<x-layouts.cpanel.settings.app-layout title="{{ __('whatsapp.message.page-title-index') }}">

    <div class="card-header py-3">
        <h5 class="m-0 font-weight-bold text-primary">{{ __('whatsapp.message.page-title-index') }}</h5>
        <span><a class="btn btn-sm btn-success mt-2" href="{{ route('whatsappmessage.create') }}"><i
                    class="fa fa-plus"></i>
                {{ __('whatsapp.message.button.create') }}</a></span>
    </div>

    <div class="card-body">
        <div style="overflow-x: auto;">
            <table id="paketsTable" cellspacing="0" width="100%"
                class="table datatable-loading no-footer sortable searchable">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>{{ __('whatsapp.message.table.name') }}</th>
                        <th>{{ __('whatsapp.message.table.incoming_message') }}</th>
                        <th>{{ __('whatsapp.message.table.description') }}</th>
                        <th>{{ __('whatsapp.message.table.status') }}</th>
                        <th class="text-end">{{ __('whatsapp.message.table.actions') }}</th>
                    </tr>
                </thead>
                <tbody>

                    @foreach ($messages as $data)
                        <tr class="@if ($data->disabled === 'true') text-secondary @else text-primary @endif">
                            <td><span class="badge rounded-pill">{{ ++$i }}</span></td>
                            <td><span class="badge"><i class="fas fa-chalkboard"></i> {{ $data->name }}</span></td>
                            <td>
                                {{ $data->incoming_message }}
                            </td>

                            <td>
                                <span>
                                    {{ $data->description }}
                                </span>
                            </td>
                            <td>
                                @if ($data->disabled == false)
                                    <span class="badge text-white bg-success">
                                        <i class="fas fa-lock-open"></i> {{ __('paket.info.enabled') }}
                                    </span>
                                @else
                                    <span class="badge text-white bg-secondary">
                                        <i class="fas fa-lock"></i> {{ __('paket.info.disabled') }}
                                    </span>
                                @endif

                            </td>

                            <td class="d-flex items-center justify-content-end">
                                <x-drop-down-action>

                                    <li>
                                        <a class="dropdown-item"
                                            href="{{ route('whatsappmessage.show', $data->slug) }}">
                                            <i class="far fa-eye me-2 text-success"></i>
                                            {{ __('whatsapp.message.button.show-message') }}
                                        </a>
                                    </li>

                                    <li>
                                        <a class="dropdown-item"
                                            href="{{ route('whatsappmessage.edit', $data->slug) }}">
                                            <i class="far fa-edit me-2 text-success"></i>
                                            {{ __('whatsapp.message.button.edit-message') }}
                                        </a>
                                    </li>
                                    <li>
                                        <form method="POST" class="d-inline"
                                            action="{{ route('whatsappmessage.disable', $data->slug) }}">
                                            @csrf
                                            @method('put')
                                            <a class="dropdown-item" href="#"
                                                onclick="event.preventDefault(); this.closest('form').submit();">
                                                @if ($data->disabled == false)
                                                    <i class="fa fa-times text-warning me-2"></i>
                                                    {{ __('whatsapp.message.button.disable-message') }}
                                                @else
                                                    <i class="fa fa-check me-2 text-success"></i>
                                                    {{ __('whatsapp.message.button.enable-message') }}
                                                @endif
                                            </a>
                                        </form>
                                    <li>
                                        <a class="dropdown-item"
                                            href="{{ route('whatsappmessage.delete', $data->slug) }}">
                                            <i class="fa fa-trash me-2 text-danger"></i>
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
        {{ $messages->links() }}

    </div>


</x-layouts.cpanel.settings.app-layout>
