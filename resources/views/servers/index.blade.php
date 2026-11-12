<x-layouts.cpanel.settings.app-layout :title="__('server.title.index')">
    <div class="card-header py-3">
        <h5 class="m-0 fw-bold text-primary">{{ __('server.header.index') }}</h5>
        @can('server-create')
            <span>
                <a class="btn btn-sm btn-primary mt-2" href="{{ route('servers.preview') }}">
                    <i class="fa fa-file-import me-1"></i> {{ __('server.button.import') }}
                </a>
            </span>
        @endcan
    </div>

    <div class="card-body">
        <div style="overflow-x: auto;">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th class="border-gray-200" width="50px">#</th>
                        <th class="border-gray-200">{{ __('server.table.name') }}</th>
                        <th class="border-gray-200">{{ __('server.table.host') }}</th>
                        <th class="border-gray-200">{{ __('server.label.port') }} (API)</th>
                        <th class="border-gray-200">{{ __('server.label.coa_port') }}</th>
                        <th class="border-gray-200">RADIUS Status</th>
                        <th class="border-gray-200 text-center" width="280px">{{ __('server.table.action') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($mikrotiks as $mikrotik)
                        <tr>
                            <td><span class="fw-normal">{{ ++$i }}</span></td>
                            <td><span class="mikrotik-name fw-normal fw-bold">{{ $mikrotik->name }}</span></td>
                            <td><span class="mikrotik-host fw-normal font-monospace">{{ $mikrotik->host }}</span></td>
                            <td><span class="mikrotik-port fw-normal font-monospace">{{ $mikrotik->port }}</span></td>
                            <td><span class="fw-normal font-monospace">{{ $mikrotik->coa_port ?? 3799 }}</span></td>
                            <td>
                                @if (!empty($mikrotik->radius_secret))
                                    <span class="badge bg-success text-white">
                                        <i class="fas fa-key me-1"></i> Secret Set
                                    </span>
                                @else
                                    <span class="badge bg-warning text-dark">
                                        <i class="fas fa-exclamation-circle me-1"></i> No Secret
                                    </span>
                                @endif
                            </td>

                            <td class="text-center">
                                {{-- Show Mikrotik Details --}}
                                <a href="{{ route('mikrotiks.show', $mikrotik->slug) }}" data-bs-toggle="tooltip"
                                    data-bs-placement="top" title="Show Mikrotik"
                                    class="btn btn-datatable btn-icon btn-transparent-dark me-2 router-show">
                                    <i class="button-show-mikrotik fas fa-external-link-alt"></i>
                                </a>

                                @can('server-edit')
                                    {{-- Edit Server --}}
                                    <a href="{{ route('servers.edit', $mikrotik->slug) }}" data-bs-toggle="tooltip"
                                        data-bs-placement="top" title="{{ __('server.title.edit') }}"
                                        class="btn btn-datatable btn-icon btn-transparent-dark me-2 router-edit">
                                        <i class="fas fa-edit"></i>
                                    </a>

                                    {{-- Toggle Enable / Disable --}}
                                    @if ($mikrotik->disabled === \App\Enums\MikrotikStatus::PPPOE_ENABLE->value)
                                        <form method="POST" class="d-inline"
                                            action="{{ route('servers.disable', $mikrotik->slug) }}">
                                            @csrf
                                            @method('put')
                                            <a data-bs-toggle="tooltip" data-bs-placement="top" title="Disable Server"
                                                class="btn btn-datatable btn-icon btn-transparent-dark me-2"
                                                onclick="event.preventDefault(); this.closest('form').submit();">
                                                <i class="fa fa-times me-2 text-danger"></i>
                                            </a>
                                        </form>
                                    @else
                                        <form method="POST" class="d-inline"
                                            action="{{ route('servers.enable', $mikrotik->slug) }}">
                                            @csrf
                                            @method('put')
                                            <a data-bs-toggle="tooltip" data-bs-placement="top" title="Enable Server"
                                                class="btn btn-datatable btn-icon btn-transparent-dark me-2"
                                                onclick="event.preventDefault(); this.closest('form').submit();">
                                                <i class="fa fa-check me-2 text-success"></i>
                                            </a>
                                        </form>
                                    @endif
                                @endcan

                                {{-- Test Connection Button --}}
                                <a href="javascript:void(0)" data-id="{{ $mikrotik->id }}" data-bs-toggle="tooltip"
                                    data-bs-placement="top" title="{{ __('server.label.test_connection') }}"
                                    class="btn-check-online btn btn-datatable btn-icon btn-transparent-dark me-2">
                                    <i class="fas fa-exchange-alt"></i>
                                </a>

                                {{-- Delete Server --}}
                                @can('server-delete')
                                    <a href="{{ route('servers.delete', $mikrotik->slug) }}"
                                        class="btn btn-datatable btn-icon btn-transparent-dark me-2" data-bs-placement="top"
                                        title="{{ __('server.button.delete') }}" id="btn-hapus-router" type="button">
                                        <i class="far fa-trash-alt text-danger"></i>
                                    </a>
                                @endcan
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="mt-3">
                {!! $mikrotiks->links() !!}
            </div>
        </div>
    </div>

    @section('custom_scripts')
        <script>
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            $(document).on('click', '.btn-check-online', function(e) {
                e.preventDefault();

                const $this = $(this);
                const $row = $this.closest('tr');

                const selectedRouterId = $this.data('id');
                const url = "{{ route('check-online', ':id') }}".replace(':id', selectedRouterId);

                $.ajax({
                    url: url,
                    method: "POST",
                    cache: false,
                    data: {
                        'id': selectedRouterId,
                    },
                    success: function(response) {
                        new Notify({
                            status: response.status,
                            title: response.title,
                            text: response.message,
                            effect: 'slide',
                            speed: 500,
                            showCloseButton: true,
                            autotimeout: 5000,
                            autoclose: true
                        });

                        $row.find('.mikrotik-name, .mikrotik-host, .mikrotik-port, .button-show-mikrotik')
                            .removeClass('text-danger')
                            .addClass('text-success');
                    },
                    error: function(data) {
                        new Notify({
                            status: 'error',
                            title: '{{ __('server.label.disconnected') }}',
                            text: data.responseJSON && data.responseJSON.message ? data.responseJSON
                                .message : 'Terjadi kesalahan',
                            effect: 'slide',
                            speed: 500,
                            showCloseButton: true,
                            autotimeout: 5000,
                            autoclose: true
                        });

                        $row.find('.mikrotik-name, .mikrotik-host, .mikrotik-port, .button-show-mikrotik')
                            .removeClass('text-success')
                            .addClass('text-danger');
                    }
                });
            });
        </script>
    @endsection
</x-layouts.cpanel.settings.app-layout>
