<x-layouts.cpanel.settings.app-layout :title="__('server.title.import')">
    <div class="card-header py-3">
        <h5 class="m-0 fw-bold text-primary">{{ __('server.title.import') }}</h5>
    </div>
    <div class="row">
        <div class="col-lg-12">
            <form action="" method="POST" id="preview-form">
                @csrf

                <div class="card-body col-lg-8">
                    {{-- Dropdown Mikrotik --}}
                    <div class="row align-items-center">
                        <div class="col-md-12 mb-3">
                            <label for="mikrotik-select">
                                <strong>{{ __('server.label.choose') }}</strong>
                                <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <select name="mikrotik_id" id="mikrotik-select" class="form-control">
                                    <option value="">{{ __('server.placeholder.server') }}</option>
                                    @foreach ($servers->pluck('name', 'slug') as $slug => $name)
                                        <option value="{{ $slug }}" @selected(old('mikrotik_id', $selectedMikrotik->slug ?? null) == $slug)>
                                            {{ $name }}
                                        </option>
                                    @endforeach
                                </select>
                                <div class="input-group-append">
                                    <div class="input-group-text">
                                        <span class="fas fa-server"></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Alert Info Total Customer --}}
                    <div id="customer-info-box">
                        @if (isset($customers))
                            <div class="alert alert-info">
                                <i class="fas fa-users me-1"></i>
                                {{ __('server.import.total_from', ['name' => $selectedMikrotik->name ?? '']) }}
                                <strong>{{ $customers->total() ?? count($customers) }}</strong>
                            </div>
                        @endif
                    </div>

                    {{-- Action Buttons --}}
                    <div>
                        <button type="button" id="btn-import" class="btn btn-success mt-4">
                            <i class="fa fa-file-import me-1"></i> {{ __('server.button.import') }}
                        </button>

                        <a class="btn btn-secondary mt-4" href="{{ route('servers.index') }}">
                            <i class="fa fa-arrow-left me-1"></i> {{ __('server.button.cancel') }}
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    @section('custom_scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const mikrotikSelect = document.getElementById('mikrotik-select');
                const infoBox = document.getElementById('customer-info-box');
                const importBtn = document.getElementById('btn-import');

                const trans = {
                    totalFrom: "{{ __('server.import.total_from', ['name' => ':name']) }}",
                    selectWarningTitle: "{{ __('server.import.select_warning_title') }}",
                    selectWarningText: "{{ __('server.import.select_warning_text') }}",
                    confirmTitle: "{{ __('server.import.confirm_title') }}",
                    confirmText: {!! json_encode(__('server.import.confirm_text')) !!},
                    btnConfirm: "{{ __('server.import.btn_confirm') }}",
                    btnCancel: "{{ __('server.button.cancel') }}",
                    successTitle: "{{ __('server.import.success_title') }}",
                    successText: "{{ __('server.import.success_text') }}",
                    errorTitle: "{{ __('server.import.error_title') }}",
                    errorText: "{{ __('server.import.error_text') }}",
                    fetchErrorText: "{{ __('server.import.fetch_error_text') }}"
                };

                // Helper fetch preview data
                function fetchPreviewData(slug) {
                    const url = "{{ route('servers.preview.data', ':slug') }}".replace(':slug', slug);

                    return fetch(url, {
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'Accept': 'application/json'
                            }
                        })
                        .then(response => {
                            if (!response.ok) throw new Error(trans.fetchErrorText);
                            return response.json();
                        });
                }

                // Handler saat Dropdown berubah
                mikrotikSelect.addEventListener('change', function() {
                    const slug = this.value;

                    if (!slug) {
                        infoBox.innerHTML = '';
                        return;
                    }

                    fetchPreviewData(slug)
                        .then(data => {
                            const infoText = trans.totalFrom.replace(':name', data.serverName);
                            infoBox.innerHTML = `
                            <div class="alert alert-info">
                                <i class="fas fa-users me-1"></i>
                                ${infoText} <strong>${data.total}</strong>
                            </div>
                        `;
                        })
                        .catch(error => {
                            console.error('Error fetching data:', error);
                            infoBox.innerHTML = '';
                        });
                });

                // Handler saat Tombol Import diklik (HANYA 1 EVENT LISTENER)
                importBtn.addEventListener('click', function() {
                    const slug = mikrotikSelect.value;

                    if (!slug) {
                        Swal.fire({
                            icon: 'warning',
                            title: trans.selectWarningTitle,
                            text: trans.selectWarningText
                        });
                        return;
                    }

                    // Fetch data preview untuk konfirmasi import
                    fetchPreviewData(slug)
                        .then(data => {
                            const confirmMessage = trans.confirmText
                                .replace(':total', data.total)
                                .replace(':name', data.serverName);

                            Swal.fire({
                                title: trans.confirmTitle,
                                html: confirmMessage, // Menggunakan HTML agar format bold <strong> & <br> muncul rapi
                                icon: 'question',
                                showCancelButton: true,
                                confirmButtonText: trans.btnConfirm,
                                cancelButtonText: trans.btnCancel
                            }).then(result => {
                                if (!result.isConfirmed) return;

                                // Generate URL import via Laravel Route Helper
                                const importUrl = "{{ route('servers.import.customer', ':slug') }}"
                                    .replace(':slug', slug);

                                // Eksekusi proses import via POST
                                fetch(importUrl, {
                                        method: 'POST',
                                        headers: {
                                            'Content-Type': 'application/json',
                                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                            'X-Requested-With': 'XMLHttpRequest'
                                        },
                                        body: JSON.stringify({})
                                    })
                                    .then(response => {
                                        if (!response.ok) throw new Error(trans.errorText);
                                        return response.json();
                                    })
                                    .then(data => {
                                        Swal.fire({
                                            icon: 'success',
                                            title: trans.successTitle,
                                            text: data.message || trans.successText
                                        });
                                    })
                                    .catch(error => {
                                        console.error(error);
                                        Swal.fire({
                                            icon: 'error',
                                            title: trans.errorTitle,
                                            text: trans.errorText
                                        });
                                    });
                            });
                        })
                        .catch(error => {
                            console.error('Error during confirmation fetch:', error);
                            Swal.fire({
                                icon: 'error',
                                title: trans.errorTitle,
                                text: trans.fetchErrorText
                            });
                        });
                });
            });
        </script>
    @endsection
</x-layouts.cpanel.settings.app-layout>
