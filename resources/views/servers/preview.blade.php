<x-layouts.cpanel.settings.app-layout title="Preview Server">
    <div class="card-header py-3">
        <h5 class="m-0 font-weight-bold text-primary">{{ __('server.title.import') }}</h5>
    </div>
    <div class="row">
        <div class="col-lg-12">
            {!! Form::open(['method' => 'POST', 'id' => 'preview-form', 'url' => '']) !!}
            @csrf
            <div class="card-body col-lg-8">
                {{-- Dropdown Mikrotik --}}
                <div class="row align-items-center">
                    <div class="col-md-12 mb-3">
                        <label for="mikrotik-select"><strong>{{ __('server.label.choose') }}</strong><span
                                class="text-danger">*</span></label>
                        <div class="input-group">
                            {!! Form::select('mikrotik_id', $servers->pluck('name', 'slug'), $selectedMikrotik->slug ?? null, [
                                'placeholder' => __('server.placeholder.server'),
                                'class' => 'form-control',
                                'id' => 'mikrotik-select',
                            ]) !!}
                            <div class="input-group-append">
                                <div class="input-group-text">
                                    <span class="fas fa-server"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div id="customer-info-box">
                    @if (isset($customers))
                        <div class="alert alert-info">
                            <i class="fas fa-users"></i>
                            {{ __('server.label.total') }}
                            <strong>{{ $customers->total() ?? count($customers) }}</strong>
                        </div>
                    @endif
                </div>

                {{-- Tombol Preview --}}
                <div>
                    {{-- Tombol Preview: warna hijau, ikon mata --}}
                    <button type="button" id="btn-import" class="btn btn-success mt-4">
                        <i class="fa fa-file-import"></i> {{ __('server.button.import') }}
                    </button>

                    {{-- Tombol Cancel: warna merah atau abu-abu, ikon panah kiri --}}
                    <a type="button" class="btn btn-secondary mt-4" href="{{ route('servers.index') }}">
                        <i class="fa fa-arrow-left"></i> {{ __('server.button.cancel') }}
                    </a>
                </div>
            </div>
            {!! Form::close() !!}

            {{-- Konten lainnya bisa kamu tambahkan di sini --}}
        </div>
    </div>

    @section('custom_scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const mikrotikSelect = document.getElementById('mikrotik-select');
                const infoBox = document.getElementById('customer-info-box');
                const importBtn = document.getElementById('btn-import');

                // Fungsi reusable untuk ambil data preview berdasarkan slug Mikrotik
                function fetchPreviewData(slug) {
                    return fetch(`/cpanel/settings/servers/mikrotik/${slug}/preview`, {
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        })
                        .then(response => {
                            if (!response.ok) throw new Error('Gagal fetch preview.');
                            return response.json();
                        });
                }

                // Saat dropdown berubah, update tampilan info
                mikrotikSelect.addEventListener('change', function() {
                    const slug = this.value;

                    if (!slug) {
                        infoBox.innerHTML = ''; // Kosongkan info jika placeholder dipilih
                        return;
                    }

                    fetchPreviewData(slug)
                        .then(data => {
                            infoBox.innerHTML = `
                    <div class="alert alert-info">
                        <i class="fas fa-users"></i>
                        Total Customer from <strong>${data.serverName}</strong>: <strong>${data.total}</strong>
                    </div>
                `;
                        })
                        .catch(error => {
                            console.error('Error fetching data:', error);
                            infoBox.innerHTML = '';
                        });
                });

                // Saat tombol import diklik
                importBtn.addEventListener('click', function() {
                    const slug = mikrotikSelect.value;

                    if (!slug) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Select Mikrotik',
                            text: 'Please select Mikrotik first'
                        });
                        return;
                    }

                    // Ambil data preview untuk konfirmasi import
                    fetchPreviewData(slug)
                        .then(data => {
                            Swal.fire({
                                title: 'Import Confirmation',
                                html: `Will import <strong>${data.total}</strong> customer from database to <strong>${data.serverName}</strong>.<br>Are you sure you want to continue?`,
                                icon: 'question',
                                showCancelButton: true,
                                confirmButtonText: 'Yes, go ahead',
                                cancelButtonText: 'Cancelled'
                            }).then(result => {
                                if (!result.isConfirmed) return;

                                // Jalankan POST import
                                fetch(`/cpanel/settings/servers/import/${slug}`, {
                                        method: 'POST',
                                        headers: {
                                            'Content-Type': 'application/json',
                                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                            'X-Requested-With': 'XMLHttpRequest'
                                        },
                                        body: JSON.stringify({})
                                    })
                                    .then(response => {
                                        if (!response.ok) throw new Error("Import Failed");
                                        return response.json();
                                    })
                                    .then(data => {
                                        Swal.fire({
                                            icon: 'success',
                                            title: 'Succeed',
                                            text: data.message ||
                                                'Import successfully started'
                                        });
                                    })
                                    .catch(error => {
                                        console.error(error);
                                        Swal.fire({
                                            icon: 'error',
                                            title: 'Gagal',
                                            text: 'An error occurred while importing'
                                        });
                                    });
                            });
                        })
                        .catch(error => {
                            console.error('Error during confirmation fetch:', error);
                            Swal.fire({
                                icon: 'error',
                                title: 'Failed',
                                text: 'Unable to retrieve customer data for confirmation'
                            });
                        });
                });
            });
        </script>
    @endsection

</x-layouts.cpanel.settings.app-layout>
