<x-layouts.app-layout title="PPP Profiles">

    <div class="card-header py-3">
        <h5 class="m-0 font-weight-bold text-primary">{{ __('package.profile.header.import') }}</h5>
        <a class="btn btn-sm btn-info mt-2" href="{{ route('ppp-profiles.index') }}">
            <i class="fa fa-arrow-left"></i> {{ __('package.profile.button.back') }}
        </a>
    </div>

    <div class="card-body">
        <div class="row align-items-center mb-3">
            <div class="col-auto">
                <label for="mikrotikSelector" class="col-form-label">
                    <strong>{{ __('package.profile.label.server') }}</strong>
                </label>
            </div>
            <div class="col">
                <select class="form-control form-control-sm" id="mikrotikSelector">
                    <option value="">Select Server</option>
                    <!-- Mikrotik servers will be populated here -->
                    @foreach ($mikrotikServers as $id => $name)
                        <option value="{{ $id }}">{{ $name }}</option>
                    @endforeach
                </select>
                <span id="mikrotikSelectorError" class="text-danger d-none">Wajib pilih mikrotik</span>
            </div>
        </div>

        <div id="importedProfilesTableWrapper">
            <hr />
            <div class="card-body">
                <table id="importedProfilesTable" class="table datatable-loading no-footer sortable searchable"
                    width="100%">
                    <thead>
                        <tr>
                            <th class="text-center align-middle">
                                <div class="d-flex flex-column align-items-center" style="gap: 0.25rem;">
                                    <label for="checkAllProfiles" class="m-0">
                                        {{ __('package.profile.table.select') }}
                                    </label>
                                    <input type="checkbox" id="checkAllProfiles" style="position: static; margin: 0;">
                                </div>
                            </th>
                            <th class="text-center align-middle">{{ __('package.profile.table.status') }}</th>
                            <th class="text-center align-middle">{{ __('package.profile.table.name') }}</th>
                            <th class="text-center align-middle">{{ __('package.profile.table.dns_server') }}</th>
                            <th class="text-center align-middle">{{ __('package.profile.table.use_encryption') }}</th>
                            <th class="text-center align-middle">{{ __('package.profile.table.rate_limit') }}</th>
                            <th class="text-center align-middle">{{ __('package.profile.table.only_one') }}</th>
                            {{-- <th class="text-center align-middle">{{ __('package.profile.table.max_upload') }}</th>
                            <th class="text-center align-middle">{{ __('package.profile.table.max_download') }}</th>
                            <th class="text-center align-middle">{{ __('package.profile.table.min_upload') }}</th>
                            <th class="text-center align-middle">{{ __('package.profile.table.min_download') }}</th>
                            <th class="text-center align-middle">{{ __('package.profile.table.cir') }}</th>
                            <th class="text-center align-middle">{{ __('package.profile.table.priority') }}</th> --}}
                            <th class="text-center align-middle">{{ __('package.profile.table.server') }}</th>
                            <th class="text-center align-middle text-end">{{ __('package.table.action') }}</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
                <div class="d-flex justify-content-end mt-3">
                    <button type="button" class="btn btn-primary" id="confirmImportBtn">
                        <i class="fa fa-save"></i> Save to database
                    </button>
                </div>
            </div>
        </div>
    </div>

    @section('custom_scripts')
        <script>
            $(document).ready(function() {
                function appendProfilesToTable(profiles) {
                    let table = $('#importedProfilesTable tbody');
                    table.empty(); // Clear existing table data

                    profiles.forEach(profile => {
                        // Tentukan badge berdasarkan status
                        let statusLabel = '';
                        if (profile.status === 'mikrotik') {
                            statusLabel = '<span class="badge bg-success text-white">Mikrotik</span>';
                        } else if (profile.status === 'conflict') {
                            let tooltipText = '';
                            if (profile.difference) {
                                tooltipText = Object.entries(profile.difference).map(([key, value]) => {
                                    return `${key}: Mikrotik(${value.mikrotik}) vs DB(${value.db})`;
                                }).join('\n');
                            }
                            statusLabel =
                                `<span class="badge bg-danger text-white" data-bs-toggle="tooltip" title="${tooltipText}">Conflict</span>`;
                        } else {
                            statusLabel = '<span class="badge bg-secondary text-white">N/A</span>';
                        }

                        let rowHTML = `
<tr
    data-profile_name="${profile.profile_name ?? ''}"
    data-max_upload="${profile.max_upload ?? ''}"
    data-max_download="${profile.max_download ?? ''}"
    data-min_upload="${profile.min_upload ?? ''}"
    data-min_download="${profile.min_download ?? ''}"
    data-rasio_cir="${profile.rasio_cir ?? ''}"
    data-priority="${profile.priority ?? ''}"
    data-mikrotik_name="${profile.mikrotik_name ?? ''}"
    data-db_profile_id="${profile.db_profile_id ?? ''}"
    data-db_profile_name="${profile.db_profile_name ?? ''}"
    data-dns_server="${profile.dns_server ?? ''}"
    data-use_encryption="${profile.use_encryption ?? ''}"
    data-rate_limit="${profile.rate_limit ?? ''}"
    data-only_one="${profile.only_one ?? ''}"
>
    <td class="text-center align-middle">
        <input type="checkbox" class="profile-checkbox">
    </td>
    <td class="text-center align-middle">${statusLabel}</td>
    <td class="text-center align-middle">${profile.profile_name || 'N/A'}</td>
    <td class="text-center align-middle">${profile.dns_server || 'N/A'}</td>
    <td class="text-center align-middle">${profile.use_encryption || 'N/A'}</td>
    <td class="text-center align-middle">${profile.rate_limit || 'N/A'}</td>
    <td class="text-center align-middle">${profile.only_one || 'N/A'}</td>
    <td class="text-center align-middle">${profile.mikrotik_name || 'N/A'}</td>
    <td class="text-center align-middle">
        <button type="button" class="btn btn-danger btn-sm delete-row">Delete</button>
    </td>
</tr>
        `;
                        table.append(rowHTML);
                    });

                    // Inisialisasi ulang tooltip setelah render ulang
                    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
                    tooltipTriggerList.map(el => new bootstrap.Tooltip(el));
                }


                $('#mikrotikSelector').on('change', function() {
                    let mikrotikId = $(this).val();
                    if (!mikrotikId) return;

                    function fetchProfiles() {
                        Swal.fire({
                            title: 'Mengambil data...',
                            text: 'Mohon tunggu sebentar.',
                            allowOutsideClick: false,
                            didOpen: () => {
                                Swal.showLoading();
                            }
                        });

                        $.ajax({
                            url: '/cpanel/packets/ppp-profiles/' + encodeURIComponent(mikrotikId) +
                                '/list',
                            method: 'GET',
                            data: {
                                mikrotik_id: mikrotikId
                            },
                            success: function(response) {
                                Swal.close(); // Tutup loading
                                appendProfilesToTable(response);
                            },
                            error: function() {
                                Swal.close(); // Tutup loading dulu
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Gagal!',
                                    text: 'Gagal mengambil profil dari Mikrotik.',
                                    showCancelButton: true,
                                    confirmButtonText: 'Coba Lagi',
                                    cancelButtonText: 'Tutup',
                                }).then((result) => {
                                    if (result.isConfirmed) {
                                        fetchProfiles(); // Retry
                                    }
                                });
                            }
                        });
                    }

                    fetchProfiles();
                });

                $(document).on('click', '.delete-row', function() {
                    $(this).closest('tr').remove();
                });

                $('#checkAllProfiles').on('change', function() {
                    const isChecked = $(this).is(':checked');
                    $('.profile-checkbox').prop('checked', isChecked);
                });
            });

            $(document).on('click', '.delete-row', function() {
                const row = $(this).closest('tr');
                const profileName = row.find('td:eq(2)').text().trim(); // Ambil nama profile dari kolom ke-3
                row.remove();

                // Cek apakah masih ada row dengan nama profile yang sama
                const matchingRows = $('#importedProfilesTable tbody tr').filter(function() {
                    return $(this).find('td:eq(2)').text().trim() === profileName;
                });

                if (matchingRows.length === 1) {
                    const remainingRow = matchingRows.first();
                    const statusCell = remainingRow.find('td:eq(1)');

                    // Ubah label jadi Mikrotik tanpa tooltip
                    statusCell.html('<span class="badge bg-success text-white">Mikrotik</span>');
                }
            });

            $('#confirmImportBtn').on('click', function() {
                const selectedProfiles = [];
                $('#importedProfilesTable tbody tr').each(function() {
                    const isChecked = $(this).find('.profile-checkbox').prop('checked');
                    if (isChecked) {
                        const profile = {
                            profile_name: $(this).find('td:eq(2)').text().trim(),
                            dns_server: $(this).find('td:eq(3)').text().trim(),
                            use_encryption: $(this).find('td:eq(4)').text().trim(),
                            rate_limit: $(this).find('td:eq(5)').text().trim(),
                            only_one: $(this).find('td:eq(6)').text().trim(),
                            mikrotik_name: $(this).find('td:eq(7)').text().trim(),
                        };
                        selectedProfiles.push(profile);
                    }
                });

                if (selectedProfiles.length === 0) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'No Profiles Selected',
                        text: 'Please select at least one profile to save.',
                    });
                    return;
                }

                // Kirim data ke backend
                $.ajax({
                    url: '{{ route('ppp.profiles.import') }}',
                    method: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        profiles: selectedProfiles,
                    },
                    success: function(response) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Data Saved',
                            text: 'Selected profiles have been successfully saved.',
                        });
                    },
                    error: function() {
                        Swal.fire({
                            icon: 'error',
                            title: 'Failed!',
                            text: 'Failed to save profiles.',
                        });
                    }
                });

            });
        </script>
    @endsection


    <style>
        #importedProfilesTableWrapper {
            max-width: 100%;
            overflow-x: auto;
        }

        #importedProfilesTable {
            width: 100%;
            table-layout: fixed;
        }
    </style>
</x-layouts.app-layout>
