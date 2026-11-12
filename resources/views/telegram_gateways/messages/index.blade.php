<x-layouts.cpanel.settings.app-layout title="{{ __('telegram.message.page-title-index') }}">

    <div class="card-header py-3">
        <h5 class="m-0 fw-bold text-primary">{{ __('telegram.message.page-title-index') }}</h5>
        <span><a class="btn btn-sm btn-success mt-2" href="{{ route('telegrammessage.create') }}"><i
                    class="fa fa-plus"></i>
                {{ __('telegram.message.button.create') }}</a></span>
    </div>

    <div class="card-body">
        <div style="overflow-x: auto;">
            <table id="packagesTable" cellspacing="0" width="100%"
                class="table datatable-loading no-footer sortable searchable">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>{{ __('telegram.message.table.name') }}</th>
                        <th>{{ __('telegram.message.table.incoming_message') }}</th>
                        <th>{{ __('telegram.message.table.description') }}</th>
                        <th>{{ __('telegram.message.table.status') }}</th>
                        <th class="text-end">{{ __('telegram.message.table.actions') }}</th>
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
                                        <i class="fas fa-lock-open"></i> {{ __('package.info.enabled') }}
                                    </span>
                                @else
                                    <span class="badge text-white bg-secondary">
                                        <i class="fas fa-lock"></i> {{ __('package.info.disabled') }}
                                    </span>
                                @endif

                            </td>

                            <td class="d-flex items-center justify-content-end">
                                <x-drop-down-action>

                                    <li>
                                        <a class="dropdown-item"
                                            href="{{ route('telegrammessage.show', $data->slug) }}">
                                            <i class="far fa-eye me-2 text-success"></i>
                                            {{ __('telegram.message.button.show-message') }}
                                        </a>
                                    </li>

                                    <li>
                                        <a class="dropdown-item"
                                            href="{{ route('telegrammessage.edit', $data->slug) }}">
                                            <i class="far fa-edit me-2 text-success"></i>
                                            {{ __('telegram.message.button.edit-message') }}
                                        </a>
                                    </li>
                                    <li>
                                        <form method="POST" class="d-inline"
                                            action="{{ route('telegrammessage.disable', $data->slug) }}">
                                            @csrf
                                            @method('put')
                                            <a class="dropdown-item" href="#"
                                                onclick="event.preventDefault(); this.closest('form').submit();">
                                                @if ($data->disabled == false)
                                                    <i class="fa fa-times text-warning me-2"></i>
                                                    {{ __('telegram.message.button.disable-message') }}
                                                @else
                                                    <i class="fa fa-check me-2 text-success"></i>
                                                    {{ __('telegram.message.button.enable-message') }}
                                                @endif
                                            </a>
                                        </form>
                                    <li>
                                        <a class="dropdown-item"
                                            href="{{ route('telegrammessage.delete', $data->slug) }}">
                                            <i class="fa fa-trash me-2 text-danger"></i>
                                            {{ __('telegram.message.button.delete-message') }}
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
