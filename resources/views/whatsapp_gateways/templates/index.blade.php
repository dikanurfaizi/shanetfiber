<x-layouts.cpanel.settings.app-layout title="{{ __('whatsapp.template.page-title-index') }}">

    <div class="card-header py-3">
        <h5 class="m-0 font-weight-bold text-primary">{{ __('whatsapp.template.page-title-index') }}</h5>
        <button id="btn-sync-template" class="btn btn-sm btn-warning mt-2">
            <i class="fas fa-sync-alt"></i> {{ __('whatsapp.template.button.sync') }}
        </button>
        <span><a class="btn btn-sm btn-success mt-2" href="{{ route('whatsapptemplate.create') }}"><i
                    class="fa fa-plus"></i>
                {{ __('whatsapp.template.button.create') }}</a></span>
    </div>

    <div class="card-body">
        <div style="overflow-x: auto;">
            <table id="packagesTable" cellspacing="0" width="100%"
                class="table datatable-loading no-footer sortable searchable">
                <thead>
                    <tr>
                        <th>{{ __('whatsapp.template.table.no') }}</th>
                        <th>{{ __('whatsapp.template.table.name') }}</th>
                        <th>{{ __('whatsapp.template.table.language') }}</th>
                        <th>{{ __('whatsapp.template.table.category') }}</th>
                        <th>{{ __('whatsapp.template.table.status') }}</th>
                        <th class="text-end">{{ __('whatsapp.template.table.actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($templates as $data)
                        <tr class="@if (strcasecmp($data->status, 'APPROVED') !== 0) text-secondary @else text-primary @endif">
                            <td><span class="badge rounded-pill">{{ ++$i }}</span></td>
                            <td><span class="badge"><i class="fas fa-chalkboard"></i> {{ $data->name }}</span></td>
                            <td>
                                {{ $data->language }}
                            </td>

                            <td>
                                <span>
                                    {{ $data->category }}
                                </span>
                            </td>
                            <td>
                                @if (strcasecmp($data->status, 'APPROVED') === 0)
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
                                            href="{{ route('whatsapptemplate.show', $data->id) }}">
                                            <i class="far fa-eye me-2 text-success"></i>
                                            {{ __('whatsapp.template.button.show') }}
                                        </a>
                                    </li>

                                    <li>
                                        <a class="dropdown-item"
                                            href="{{ route('whatsapptemplate.edit', $data->id) }}">
                                            <i class="far fa-edit me-2 text-success"></i>
                                            {{ __('whatsapp.template.button.edit') }}
                                        </a>
                                    </li>
                                    <li>
                                        <form method="POST" class="d-inline"
                                            action="{{ route('whatsapptemplate.disable', $data->id) }}">
                                            @csrf
                                            @method('put')
                                            <a class="dropdown-item" href="#"
                                                onclick="event.preventDefault(); this.closest('form').submit();">
                                                @if ($data->disabled == false)
                                                    <i class="fa fa-times text-warning me-2"></i>
                                                    {{ __('whatsapp.template.button.disable') }}
                                                @else
                                                    <i class="fa fa-check me-2 text-success"></i>
                                                    {{ __('whatsapp.template.button.enable') }}
                                                @endif
                                            </a>
                                        </form>
                                    <li>
                                        <a class="dropdown-item"
                                            href="{{ route('whatsapptemplate.delete', $data->id) }}">
                                            <i class="fa fa-trash me-2 text-danger"></i>
                                            {{ __('whatsapp.template.button.delete') }}
                                        </a>
                                    </li>
                                </x-drop-down-action>
                            </td>
                        </tr>
                    @endforeach

                </tbody>
            </table>
        </div>
        {{ $templates->links() }}

    </div>

    @section('custom_scripts')
        <script>
            document.addEventListener("DOMContentLoaded", () => {
                const btn = document.querySelector("#btn-sync-template");

                btn.addEventListener("click", () => {
                    btn.disabled = true;

                    const original = btn.innerHTML;
                    btn.innerHTML = `<span class="spinner-border spinner-border-sm me-1"></span> Syncing...`;

                    $.ajax({
                        url: "{{ route('whatsapptemplate.sync') }}",
                        type: "POST",
                        data: {
                            _token: $('meta[name="csrf-token"]').attr("content"),
                        },
                        success: function(res) {
                            new Notify({
                                status: res.success ? "success" : "error",
                                title: "Template Sync",
                                text: res.message,
                                effect: "slide",
                                autoclose: true,
                            });

                            if (res.success) {
                                setTimeout(() => location.reload(), 1000);
                            }
                        },
                        error: function() {
                            new Notify({
                                status: "error",
                                title: "Template Sync",
                                text: "Failed to sync templates.",
                            });
                        },
                        complete: function() {
                            btn.disabled = false;
                            btn.innerHTML = original;
                        }
                    });
                });
            });
        </script>
    @endsection

</x-layouts.cpanel.settings.app-layout>
