<x-layouts.cpanel.settings.app-layout title="{{ __('whatsapp.number.page-title-index') }}">

    <div class="card-header py-3">
        <h5 class="m-0 fw-bold text-primary">{{ __('whatsapp.number.page-title-index') }}</h5>
        <span><a class="btn btn-sm btn-success mt-2" href="{{ route('whatsappnumber.create') }}"><i class="fa fa-plus"></i>
                {{ __('whatsapp.number.button.add-new-number') }}</a></span>
    </div>

    <div class="card-body">
        <div style="overflow-x: auto;">
            <table id="packagesTable" cellspacing="0" width="100%"
                class="table datatable-loading no-footer sortable searchable">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>{{ __('whatsapp.number.table.name') }}</th>
                        <th>{{ __('whatsapp.number.table.number') }}</th>
                        <th>{{ __('whatsapp.number.table.description') }}</th>
                        <th>{{ __('whatsapp.number.table.status') }}</th>
                        <th class="text-end">{{ __('whatsapp.number.table.actions') }}</th>
                    </tr>
                </thead>
                <tbody>

                    @foreach ($numbers as $number)
                        <tr class="@if ($number->disabled === 'true') text-secondary @else text-primary @endif">
                            <td><span class="badge rounded-pill">{{ ++$i }}</span></td>
                            <td><span class="badge"><i class="fas fa-chalkboard"></i> {{ $number->name }}</span></td>
                            <td><span
                                    class="badge @if ($number->disabled === 'true') text-secondary @else text-primary @endif">
                                    {{ $number->number }}
                                </span></td>
                            <td>
                                <span>
                                    {{ $number->description }}
                                </span>
                            </td>
                            <td>
                                @if ($number->disabled == false)
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
                                            href="{{ route('whatsappnumber.show', $number->slug) }}">
                                            <i class="far fa-eye me-2 text-success"></i>
                                            {{ __('whatsapp.number.button.show-number') }}
                                        </a>
                                    </li>

                                    <li>
                                        <a class="dropdown-item"
                                            href="{{ route('whatsappnumber.edit', $number->slug) }}">
                                            <i class="far fa-edit me-2 text-success"></i>
                                            {{ __('whatsapp.number.button.edit-number') }}
                                        </a>
                                    </li>
                                    <li>

                                        <form method="POST" class="d-inline"
                                            action="{{ route('whatsappnumber.disable', $number->slug) }}">
                                            @csrf
                                            @method('put')
                                            <a class="dropdown-item" href="#"
                                                onclick="event.preventDefault(); this.closest('form').submit();">
                                                @if ($number->disabled == false)
                                                    <i class="fa fa-times text-warning me-2"></i>
                                                    {{ __('whatsapp.number.button.disable-number') }}
                                                @else
                                                    <i class="fa fa-check me-2 text-success"></i>
                                                    {{ __('whatsapp.number.button.enable-number') }}
                                                @endif
                                            </a>
                                        </form>

                                    <li>
                                        <a class="dropdown-item"
                                            href="{{ route('whatsappnumber.delete', $number->slug) }}">
                                            <i class="fa fa-trash me-2 text-danger"></i>
                                            {{ __('whatsapp.number.button.delete-number') }}
                                        </a>
                                    </li>

                                </x-drop-down-action>
                            </td>
                        </tr>
                    @endforeach

                </tbody>
            </table>
        </div>
        {{ $numbers->links() }}

    </div>
</x-layouts.cpanel.settings.app-layout>
