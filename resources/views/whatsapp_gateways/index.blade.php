<x-layouts.cpanel.settings.app-layout title="WhatsApp Broadcast">
    <div class="card-header py-3">
        <h5 class="m-0 font-weight-bold text-primary">WhatsApp Broadcast</h5>
        
        <span><a class="btn btn-sm btn-success mt-2" href="{{ route('whatsapp-gateway.create') }}"><i
                    class="fa fa-plus"></i>
                Add</a></span>
    </div>

    <div class="card-body">
        <form method="GET" action="{{ route('whatsapp-gateway.index') }}" class="mb-3">
            <div class="input-group">
                <input type="text" name="search" class="form-control" placeholder="Search by name or description"
                    value="{{ request()->get('search') }}">
                <button class="btn btn-outline-secondary" type="submit">Search</button>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th class="border-gray-200" width="50px">#</th>
                        <th class="border-gray-200">Name</th>
                        <th class="border-gray-200">Auto Send</th>
                        <th class="border-gray-200">Description</th>
                        <th class="border-gray-200" width="250px">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($whatsappGateways as $whatsappGateway)
                        <tr>
                            <td><span class="fw-normal">{{ ++$i }}</span></td>
                            <td><span class="fw-normal">{{ $whatsappGateway->name }}</span></td>
                            <td><span class="fw-normal">{{ ucfirst($whatsappGateway->status) }}</span></td>
                            <td><span class="fw-normal">{{ $whatsappGateway->description }}</span></td>
                            <td>
                                @can('server-edit')
                                    <button type="button" class="btn btn-datatable btn-icon btn-transparent-dark me-2"
                                        data-bs-toggle="modal" data-bs-target="#detailModal"
                                        data-id="{{ $whatsappGateway->id }}" data-name="{{ $whatsappGateway->name }}"
                                        data-status="{{ $whatsappGateway->status }}"
                                        data-description="{{ $whatsappGateway->description }}"
                                        data-scheduled-date="{{ $whatsappGateway->schedule_date }}"
                                        data-scheduled-time="{{ $whatsappGateway->schedule_time }}"
                                        data-message="{{ $whatsappGateway->message }}">
                                        <i class="fas fa-info-circle"></i> Details
                                    </button>
                                @endcan
                                @can('server-edit')
                                    <a href="{{ route('whatsapp-gateway.edit', $whatsappGateway->id) }}"
                                        data-bs-toggle="tooltip" data-bs-placement="top" title="Edit WhatsApp Scheduler"
                                        class="btn btn-datatable btn-icon btn-transparent-dark me-2 router-edit">
                                        <i class="fas fa-edit"></i> Edit
                                    </a>
                                @endcan
                                @can('server-delete')
                                    <button type="button" class="btn btn-datatable btn-icon btn-transparent-dark me-2"
                                        data-bs-toggle="tooltip" data-bs-placement="top" title="Delete WhatsApp Scheduler"
                                        onclick="confirmDelete({{ $whatsappGateway->id }})">
                                        <i class="fas fa-trash"></i> Delete
                                    </button>
                                    <form id="delete-form-{{ $whatsappGateway->id }}"
                                        action="{{ route('whatsapp-gateway.destroy', $whatsappGateway->id) }}"
                                        method="POST" style="display: none;">
                                        @csrf
                                        @method('DELETE')
                                    </form>
                                @endcan
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            {!! $whatsappGateways->links() !!}
        </div>
        {{-- <div class="row align-items-center">
            <div class="col-md-12 mb-3">
                {!! Form::model($webSystem, [
                    'method' => 'PATCH',
                    'route' => ['websystem.updatewhatsappgateway', $webSystem->slug],
                ]) !!}
                <div class="card-footer">
                    @if ($webSystem->whatsapp_gateway === 'disable')
                        <button type="submit" class="btn btn-success" id="button-save">
                            <i class="fa fa-check me-2"></i> Enable WhatsApp Broadcast
                        </button>
                    @else
                        <button type="submit" class="btn btn-danger" id="button-save">
                            <i class="fa fa-times"></i> Disable WhatsApp Broadcast
                        </button>
                    @endif
                </div>
                {!! Form::close() !!}
            </div>
        </div> --}}
    </div>

    <!-- Modal for Details -->
    <div class="modal fade" id="detailModal" tabindex="-1" role="dialog" aria-labelledby="detailModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="detailModalLabel">WhatsApp Broadcast Details</h5>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p><strong>Nama:</strong> <span id="name"></span></p>
                    <p><strong>Status Auto Send:</strong> <span id="status"></span></p>
                    <p><strong>Description:</strong> <span id="description"></span></p>
                    <p><strong>WhatsApp Sent Every Date:</strong> <span id="scheduledDate"></span></p>
                    <p><strong>WhatsApp Sent At:</strong> <span id="scheduledTime"></span></p>
                    <p><strong>Message:</strong> <span id="message"></span></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    @section('custom_scripts')
        <script>
            const detailModal = document.getElementById('detailModal');
            detailModal.addEventListener('show.bs.modal', event => {
                const button = event.relatedTarget; // Button that triggered the modal

                // Extract info from data attributes
                const name = button.getAttribute('data-name');
                const status = button.getAttribute('data-status');
                const description = button.getAttribute('data-description');
                const scheduledDate = button.getAttribute('data-scheduled-date'); // This is the date
                const scheduledTime = button.getAttribute('data-scheduled-time'); // This is the time
                const message = button.getAttribute('data-message');

                const capitalizedStatus = status.charAt(0).toUpperCase() + status.slice(1);

                // Update the modal's content.
                document.getElementById('name').textContent = name;
                document.getElementById('status').textContent = capitalizedStatus;
                document.getElementById('description').textContent = description;
                document.getElementById('scheduledDate').textContent = scheduledDate;
                document.getElementById('scheduledTime').textContent = scheduledTime;
                document.getElementById('message').textContent = message;
            });
        </script>

        <script>
            function confirmDelete(id) {
                Swal.fire({
                    title: 'Are you sure?',
                    text: "You won't be able to revert this!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, delete it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        document.getElementById('delete-form-' + id).submit();
                    }
                })
            }
        </script>
    @endsection
</x-layouts.cpanel.settings.app-layout>
