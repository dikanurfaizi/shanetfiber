<x-layouts.cpanel.settings.app-layout title="Servers">
    <div class="card-header py-3">
        <h5 class="m-0 font-weight-bold text-primary">Servers</h5>
        @can ('server-create')
        <span><a class="btn btn-sm btn-success mt-2" href="{{ route('servers.create')  }}"><i class="fa fa-plus"></i> Add Server</a></span>
        @endcan
    </div>

    <div class="card-body">

        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th class="border-gray-200" width="50px">#</th>
                        <th class="border-gray-200">Name</th>
                        <th class="border-gray-200">Host</th>
                        <th class="border-gray-200">Port</th>
                        <th class="border-gray-200" width="280px">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($mikrotiks as $mikrotik)
                    <tr>
                        <td><span class="fw-normal">{{ ++$i }}</span></td>
                        <td><span id="mikrotik-name" class="fw-normal">{{ $mikrotik->name }}</span></td>
                        <td><span id="mikrotik-host" class="fw-normal">{{ $mikrotik->host }}</span></td>
                        <td><span id="mikrotik-port" class="fw-normal">{{ $mikrotik->port }}</span></td>

                        <td>

                            <a href="{{route('mikrotiks.show',$mikrotik->slug)}}" data-bs-toggle="tooltip" data-bs-placement="top" title="Show Mikrotik" class="btn btn-datatable btn-icon btn-transparent-dark me-2 router-show">
                                <i id="button-show-mikrotik" class="fas fa-external-link-alt"></i>
                            </a>
                            @can ('server-edit')
                            <a href="{{route('servers.edit',$mikrotik->slug)}}" data-bs-toggle="tooltip" data-bs-placement="top" title="Edit Mikrotik" class="btn btn-datatable btn-icon btn-transparent-dark me-2 router-edit">
                                <i class="fas fa-edit"></i>
                            </a>
                            @if ($mikrotik->disabled === \App\Enums\MikrotikStatus::PPPOE_ENABLE->value)
                            <form method="POST" class="d-inline" action="{{ route('servers.disable', $mikrotik->slug) }}">
                                @csrf
                                @method('put')
                                <a data-bs-toggle="tooltip" data-bs-placement="top" title="Disable Server" class="btn btn-datatable btn-icon btn-transparent-dark me-2" onclick="event.preventDefault(); this.closest('form').submit();">
                                    <i class="fa fa-times me-2"></i>
                                </a>
                            </form>
                            @else
                            <form method="POST" class="d-inline" action="{{ route('servers.enable', $mikrotik->slug) }}">
                                @csrf
                                @method('put')
                                <a data-bs-toggle="tooltip" data-bs-placement="top" title="Enable Server" class="btn btn-datatable btn-icon btn-transparent-dark me-2" onclick="event.preventDefault(); this.closest('form').submit();">
                                    <i class="fa fa-check me-2 text-success"></i>
                                </a>
                            </form>
                            @endif
                            @endcan
                            <a href="javascript:void(0)" id="check-online" data-id="{{$mikrotik->id}}" data-bs-toggle="tooltip" data-bs-placement="top" title="Cek Mikrotik Status" class="btn btn-datatable btn-icon btn-transparent-dark me-2">
                                <i class="fas fa-exchange-alt"></i>
                            </a>
                            @can ('server-delete')
                            <a href="{{route('servers.delete',$mikrotik->slug)}}" class="btn btn-datatable btn-icon btn-transparent-dark me-2" data-bs-placement="top" title="Hapus Mikrotik" id="btn-hapus-router" type="button">
                                <i class="far fa-trash-alt"></i>
                            </a>
                            @endcan
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            {!! $mikrotiks->links() !!}
        </div>
    </div>
    @section('custom_scripts')
    <script>
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        $(document).on('click', '#check-online', function(e) {
            e.preventDefault();
            const selectedRouterId = $(this).data('id');
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
                    $('#mikrotik-name').removeClass('text-danger');
                    $('#mikrotik-host').removeClass('text-danger');
                    $('#mikrotik-port').removeClass('text-danger');
                    $('#button-show-mikrotik').removeClass('text-danger');
                    $('#mikrotik-name').addClass('text-success');
                    $('#mikrotik-host').addClass('text-success');
                    $('#mikrotik-port').addClass('text-success');
                    $('#button-show-mikrotik').addClass('text-success');
                },
                error: function(data) {
                    new Notify({
                        status: 'error',
                        title: 'Gagal terkoneksi!',
                        text: data.responseJSON && data.responseJSON.message ? data.responseJSON.message : 'Terjadi kesalahan',
                        effect: 'slide',
                        speed: 500,
                        showCloseButton: true,
                        autotimeout: 5000,
                        autoclose: true
                    });
                    $('#mikrotik-name').removeClass('text-success');
                    $('#mikrotik-host').removeClass('text-success');
                    $('#mikrotik-port').removeClass('text-success');
                    $('#button-show-mikrotik').removeClass('text-success');
                    $('#mikrotik-name').addClass('text-danger');
                    $('#mikrotik-host').addClass('text-danger');
                    $('#mikrotik-port').addClass('text-danger');
                    $('#button-show-mikrotik').addClass('text-danger');
                }
            });
        });
    </script>
    @endsection
</x-layouts.cpanel.settings.app-layout>