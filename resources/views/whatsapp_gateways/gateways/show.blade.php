<x-layouts.cpanel.settings.app-layout title="Gateway Provider Detail">

    {{-- Header --}}
    <div class="card-header py-3 d-flex justify-content-between align-items-center">
        <h5 class="m-0 font-weight-bold text-primary">Provider: {{ $gateway->name }}</h5>

        <a href="{{ route('whatsappgateway.index') }}" class="btn btn-sm btn-outline-secondary">
            <i class="fa fa-arrow-left"></i> Back
        </a>
    </div>

    <div class="row mt-4">

        {{-- Provider Info --}}
        <div class="col-md-12 mb-4">
            <div class="card shadow-sm p-4">
                {!! Form::open(['route' => ['whatsappgateway.edit', $gateway->slug], 'method' => 'GET']) !!}
                <h6 class="font-weight-bold mb-3 text-muted">Provider Information</h6>

                <div class="row mb-3">
                    <div class="col-md-3 text-muted">Type</div>
                    <div class="col-md-9">{{ ucfirst($gateway->type) }} Provider</div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-3 text-muted">Status</div>
                    <div class="col-md-9">
                        <span
                            class="badge @if ($gateway->is_active == true) bg-success @else bg-secondary @endif text-white">
                            @if ($gateway->is_active == true)
                                Active
                            @else
                                Inactive
                            @endif
                        </span>
                    </div>
                </div>

                <button class="btn btn-sm btn-secondary mt-2">
                    <i class="fa fa-edit"></i> Edit Provider
                </button>
                {!! Form::close() !!}
            </div>
        </div>

        {{-- Accounts Section --}}
        <div class="col-md-12">
            <div class="card shadow-sm p-4">

                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="font-weight-bold text-muted">Accounts under this Provider</h6>

                    <a href="{{ route('whatsappgateway.accounts.create', $gateway->slug) }}"
                        class="btn btn-sm btn-success">
                        <i class="fa fa-plus"></i> Add Account
                    </a>
                </div>

                <div class="table-responsive">
                    <table class="table table-striped table-bordered table-sm">
                        <thead class="thead-light">
                            <tr>
                                <th>ID</th>
                                @if ($gateway->type === 'onprem')
                                    <th>Phone Number</th>
                                @else
                                    <th>WhatsApp Business Account ID</th>
                                @endif
                                <th>Purpose</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>

                        <tbody>
                            @if ($gateway->accounts->isEmpty())
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-3">
                                        No accounts registered for this provider.
                                    </td>
                                </tr>
                            @else
                                @foreach ($gateway->accounts as $account)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>
                                            <code>{{ $account->display_number }}</code>
                                        </td>
                                        <td>
                                            <span
                                                class="badge bg-info text-white">{{ $account->purpose ?? 'default' }}</span>
                                        </td>

                                        <td>
                                            <span
                                                class="badge 
                    @if ($account->is_active) bg-success 
                    @else bg-secondary @endif text-white">
                                                {{ $account->is_active ? 'Active' : 'Inactive' }}
                                            </span>
                                        </td>

                                        <td>
                                            <div class="d-flex align-items-center" style="gap: 8px;">

                                                <a href="{{ route('whatsappgateway.accounts.edit', [$gateway->slug, $account->id]) }}"
                                                    class="btn btn-sm btn-warning">
                                                    <i class="fa fa-edit"></i>
                                                </a>

                                                <form
                                                    action="{{ route('whatsappgateway.accounts.destroy', [$gateway->slug, $account->id]) }}"
                                                    method="POST" id="deleteForm" class="m-0 p-0">
                                                    @csrf
                                                    @method('DELETE')

                                                    <button type="button" class="btn btn-sm btn-danger btnDelete">
                                                        <i class="fa fa-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            @endif
                        </tbody>
                    </table>
                </div>

            </div>
        </div>

        {{-- @if ($gateway->type === 'onprem') --}}
        {{-- Test Gowa REST --}}
        <div class="col-md-12">
            <div class="card shadow-sm p-4">
                <form action="{{ route('whatsappgateway.accounts.test', $gateway->slug) }}" method="POST">
                    @csrf

                    <div class="mb-3">
                        <label class="font-weight-bold text-muted">Connection Test</label>
                        <input type="text" name="receive_number" class="form-control" placeholder="628xxxxxxx"
                            required>
                    </div>

                    <button class="btn btn-primary">
                        <i class="fa fa-paper-plane"></i> Test Send Message
                    </button>
                </form>
            </div>
        </div>
        {{-- @endif --}}

    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.btnDelete').forEach(btn => {

                btn.addEventListener('click', function(e) {
                    e.preventDefault(); // Mencegah submit otomatis

                    const form = this.closest('form');

                    Swal.fire({
                        title: 'Hapus Akun Ini?',
                        text: "Aksi ini tidak dapat dibatalkan. Semua nomor telepon juga akan terhapus permanen.",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#d33',
                        cancelButtonColor: '#3085d6',
                        confirmButtonText: 'Ya, hapus',
                        cancelButtonText: 'Batal'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            form.submit();
                        }
                    });
                });

            });
        });
    </script>

</x-layouts.cpanel.settings.app-layout>
