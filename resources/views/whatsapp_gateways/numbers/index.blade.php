<x-layouts.cpanel.settings.app-layout title="{{ __('whatsapp.number.page-title-index') }}">

    <div class="card-header py-3 d-flex justify-content-between align-items-center">
        <h5 class="m-0 fw-bold text-primary">{{ __('whatsapp.number.page-title-index') }}</h5>
        <a class="btn btn-sm btn-success" href="{{ route('whatsappnumber.create') }}">
            <i class="fa fa-plus me-1"></i> {{ __('whatsapp.number.button.add-new-number') }}
        </a>
    </div>

    <div class="card-body">
        <div class="table-responsive">
            <table id="packagesTable" cellspacing="0" width="100%"
                class="table table-striped table-bordered table-sm datatable-loading no-footer sortable searchable">
                <thead>
                    <tr>
                        <th width="50">#</th>
                        <th>{{ __('whatsapp.number.table.name') }}</th>
                        <th>{{ __('whatsapp.number.table.number') }}</th>
                        <th>{{ __('whatsapp.number.table.description') }}</th>
                        <th>{{ __('whatsapp.number.table.status') }}</th>
                        <th class="text-end" width="100">{{ __('whatsapp.number.table.actions') }}</th>
                    </tr>
                </thead>
                <tbody>

                    @foreach ($numbers as $number)
                        <tr class="@if ($number->disabled === 'true' || $number->disabled === true) text-secondary @else text-primary @endif">
                            <td><span class="badge rounded-pill bg-light text-dark">{{ ++$i }}</span></td>
                            <td>
                                <span class="fw-semibold">
                                    <i class="fas fa-chalkboard me-1 text-muted"></i> {{ $number->name }}
                                </span>
                            </td>
                            <td>
                                <code>{{ $number->number }}</code>
                            </td>
                            <td>
                                <span class="text-muted">
                                    {{ $number->description ?? '-' }}
                                </span>
                            </td>
                            <td>
                                @if ($number->disabled == false)
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
                                            href="{{ route('whatsappnumber.show', ['whatsappnumber' => $number->slug]) }}">
                                            <i class="far fa-eye me-2 text-success"></i>
                                            {{ __('whatsapp.number.button.show-number') }}
                                        </a>
                                    </li>

                                    <li>
                                        <a class="dropdown-item"
                                            href="{{ route('whatsappnumber.edit', ['whatsappnumber' => $number->slug]) }}">
                                            <i class="far fa-edit me-2 text-success"></i>
                                            {{ __('whatsapp.number.button.edit-number') }}
                                        </a>
                                    </li>

                                    <li>
                                        <form method="POST" class="d-inline"
                                            action="{{ route('whatsappnumber.disable', ['whatsappnumber' => $number->slug]) }}">
                                            @csrf
                                            @method('PUT')
                                            <a class="dropdown-item" href="#"
                                                onclick="event.preventDefault(); this.closest('form').submit();">
                                                @if ($number->disabled == false)
                                                    <i class="fa fa-times text-warning me-2"></i>
                                                    {{ __('whatsapp.number.button.disable-number') }}
                                                @else
                                                    <i class="fa fa-check text-success me-2"></i>
                                                    {{ __('whatsapp.number.button.enable-number') }}
                                                @endif
                                            </a>
                                        </form>
                                    </li>

                                    <li>
                                        <a class="dropdown-item text-danger"
                                            href="{{ route('whatsappnumber.delete', ['whatsappnumber' => $number->slug]) }}">
                                            <i class="fa fa-trash me-2"></i>
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

        <div class="mt-3">
            {{ $numbers->links() }}
        </div>
    </div>

</x-layouts.cpanel.settings.app-layout>
