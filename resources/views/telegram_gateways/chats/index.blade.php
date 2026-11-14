<x-layouts.cpanel.settings.app-layout title="User Telegram Management">

    <div class="card-header py-3 d-flex flex-column flex-md-row justify-content-between align-items-center gap-3">
        <h5 class="m-0 fw-bold text-primary">User Telegram Management</h5>
        <div class="d-flex flex-wrap gap-2">
            <button id="fetchTelegramBtn" class="btn btn-sm btn-info" data-bs-toggle="modal"
                data-bs-target="#telegramModal">
                <i class="fab fa-telegram"></i>
                <span class="d-none d-md-inline">Fetch from Telegram API</span>
                <span class="d-inline d-md-none">Fetch</span>
            </button>
        </div>
    </div>

    <div class="card-body">
        <!-- Search Form -->
        <div class="row mb-4">
            <div class="col-12 col-md-6">
                <form method="GET" action="{{ route('telegramchat.index') }}">
                    <div class="input-group search-group">
                        <input type="text" name="search" class="form-control me-2"
                            placeholder="Search by name, email or Telegram ID..." value="{{ request('search') }}">
                        <button class="btn btn-primary px-3" type="submit">
                            <i class="fa fa-search"></i> <span class="d-none d-md-inline">Search</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- User Table - Desktop View -->
        <div class="d-none d-md-block" style="overflow-x: auto;">
            <table class="table table-striped table-bordered">
                <thead class="thead-dark">
                    <tr>
                        <th>#</th>
                        <th>Name</th>
                        <th>Chat ID</th>
                        <th>Telegram Name</th>
                        <th>Telegram Username</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($chats as $index => $chat)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>
                                <div class="mb-3 mb-0">
                                    <select name="user_id_{{ $chat->id }}" class="form-control select2-user"
                                        data-id="{{ $chat->id }}" style="width: 100%">
                                        <option value="">Select Admin</option>
                                        @foreach ($users->pluck('name', 'id') as $userId => $userName)
                                            <option value="{{ $userId }}" @selected($chat->user_id == $userId)>
                                                {{ $userName }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </td>
                            <td>
                                @if ($chat->chat_id)
                                    <span class="badge bg-primary text-white">{{ $chat->chat_id }}</span>
                                @else
                                    <span class="badge bg-secondary text-white">Not connected</span>
                                @endif
                            </td>
                            <td>{{ $chat->first_name }} {{ $chat->last_name }}</td>
                            <td>
                                @if ($chat->username)
                                    <a href="https://t.me/{{ $chat->username }}" target="_blank">
                                        {{ '@' . $chat->username }}
                                    </a>
                                @else
                                    -
                                @endif
                            </td>
                            <td>
                                <div class="d-flex flex-wrap gap-2">
                                    <button class="btn btn-sm btn-success btn-update-user"
                                        data-id="{{ $chat->id }}" disabled>
                                        <i class="fa fa-save"></i> <span class="d-none d-lg-inline">Update</span>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-warning btn-test-message"
                                        data-id="{{ $chat->id }}">
                                        <i class="fa fa-paper-plane"></i> <span class="d-none d-lg-inline">Test</span>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-danger btn-confirm-delete"
                                        data-id="{{ $chat->id }}">
                                        <i class="fa fa-trash"></i> <span class="d-none d-lg-inline">Delete</span>
                                    </button>

                                    <form id="delete-form-{{ $chat->id }}" method="POST"
                                        action="{{ route('telegramchatid.soft_delete', $chat->id) }}"
                                        style="display: none;">
                                        @csrf
                                        @method('PATCH')
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- User Cards - Mobile View -->
        <div class="d-block d-md-none">
            @foreach ($chats as $index => $chat)
                <div class="card mb-3">
                    <div class="card-header bg-light d-flex justify-content-between align-items-center">
                        <strong>User #{{ $index + 1 }}</strong>
                        <div>
                            @if ($chat->chat_id)
                                <span class="badge bg-primary text-white">{{ $chat->chat_id }}</span>
                            @else
                                <span class="badge bg-secondary text-white">Not connected</span>
                            @endif
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="small text-muted">Telegram Name</label>
                            <p>{{ $chat->first_name }} {{ $chat->last_name }}</p>
                        </div>

                        <div class="mb-3">
                            <label class="small text-muted">Username</label>
                            <p>
                                @if ($chat->username)
                                    <a href="https://t.me/{{ $chat->username }}" target="_blank">
                                        {{ '@' . $chat->username }}
                                    </a>
                                @else
                                    -
                                @endif
                            </p>
                        </div>

                        <div class="mb-3">
                            <label class="small text-muted">Assign Admin</label>
                            <div class="mb-3 mb-3">
                                <select name="user_id_{{ $chat->id }}" class="form-control select2-user-mobile"
                                    data-id="{{ $chat->id }}">
                                    <option value="">Select Admin</option>
                                    @foreach ($users->pluck('name', 'id') as $userId => $userName)
                                        <option value="{{ $userId }}" @selected($chat->user_id == $userId)>
                                            {{ $userName }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="d-flex flex-wrap gap-2">
                            <button class="btn btn-sm btn-success btn-update-user" data-id="{{ $chat->id }}"
                                disabled>
                                <i class="fa fa-save"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-warning btn-test-message"
                                data-id="{{ $chat->id }}">
                                <i class="fa fa-paper-plane"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-danger btn-confirm-delete"
                                data-id="{{ $chat->id }}">
                                <i class="fa fa-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Pagination -->
        <div class="mt-3">
            {{ $chats->links() }}
        </div>
    </div>

    <!-- Modal for Telegram API Results -->
    <div class="modal fade" id="telegramModal" tabindex="-1" role="dialog" aria-labelledby="telegramModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="telegramModalLabel">Telegram API Results</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="telegramResults">
                    <!-- Results will be displayed here -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary d-none" id="saveTelegramBtn">Save</button>
                </div>
            </div>
        </div>
    </div>

    @section('custom_scripts')
        <script>
            $(document).ready(function() {
                // Debug check
                console.log("DOM Ready - Initializing Telegram Management Scripts");

                // Initialize select2 for desktop
                if ($.fn.select2) {
                    $('.select2-user').select2({
                        theme: 'bootstrap-5',
                        width: '100%'
                    });

                    $('.select2-user-mobile').select2({
                        theme: 'bootstrap-5',
                        width: '100%',
                        dropdownParent: $('.card-body')
                    });
                }

                // Common change handler
                $(document).on('change', '.select2-user, .select2-user-mobile', function() {
                    let id = $(this).data('id');
                    let selectedUserId = $(this).val();

                    if (selectedUserId && selectedUserId !== '') {
                        $(`.btn-update-user[data-id="${id}"]`).prop('disabled', false);
                    } else {
                        $(`.btn-update-user[data-id="${id}"]`).prop('disabled', true);
                    }
                });

                // Update user AJAX
                $(document).on('click', '.btn-update-user', function() {
                    let id = $(this).data('id');
                    let userId = $(`select[name="user_id_${id}"]`).val();

                    $.ajax({
                        url: `/cpanel/settings/telegramchat/${id}/update_telegram_data`,
                        type: 'PATCH',
                        data: {
                            _token: '{{ csrf_token() }}',
                            user_id: userId
                        },
                        success: function(res) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil',
                                text: res.message || 'Berhasil diperbarui',
                                showConfirmButton: false,
                                timer: 2000
                            });
                            $(`.btn-update-user[data-id="${id}"]`).prop('disabled', true);
                        },
                        error: function(err) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal',
                                text: 'Gagal memperbarui data.'
                            });
                        }
                    });
                });

                let newTelegramChatIds = [];

                $(document).on('hidden.bs.modal', '#telegramModal', function() {
                    $('.modal-backdrop').remove();
                    $('body').removeClass('modal-open').css({
                        'overflow': '',
                        'padding-right': ''
                    });
                });

                // 🔹 FIX UTAMA: Gunakan Event Delegation pada tombol Fetch Telegram
                $(document).on('click', '#fetchTelegramBtn', function(e) {
                    console.log("Fetch button clicked - Executing AJAX Request...");

                    // Set tampilan spinner di modal
                    $('#telegramResults').html(
                        '<div class="text-center py-4"><i class="fa fa-spinner fa-spin fa-2x mb-2"></i><p>Fetching data from Telegram...</p></div>'
                    );
                    $('#saveTelegramBtn').addClass('d-none');

                    // Buka modal secara manual (opsional jika data-toggle kadang glitch)
                    $('#telegramModal').modal('show');

                    let targetUrl = '{{ route('telegramchatid.get_updates') }}';
                    console.log("AJAX Target URL:", targetUrl);

                    $.ajax({
                        url: targetUrl,
                        method: 'GET',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        },
                        success: function(response) {
                            console.log("AJAX Success Response:", response);
                            let html = '';
                            newTelegramChatIds = [];

                            if (response.chat_ids && response.chat_ids.length > 0) {
                                newTelegramChatIds = response.chat_ids;
                                html += '<h6>Telegram Users (' + response.chat_ids.length +
                                    ')</h6>';
                                html +=
                                    '<div class="table-responsive"><table class="table table-sm table-bordered"><thead><tr><th>Chat ID</th><th>Username</th><th>First Name</th><th>Last Name</th></tr></thead><tbody>';

                                response.chat_ids.forEach(function(user) {
                                    html += `<tr>
                                    <td><code>${user.chat_id}</code></td>
                                    <td>${user.username ? '@' + user.username : '-'}</td>
                                    <td>${user.first_name || '-'}</td>
                                    <td>${user.last_name || '-'}</td>
                                </tr>`;
                                });

                                html += '</tbody></table></div>';
                                $('#saveTelegramBtn').removeClass('d-none');
                            } else {
                                let msg = response.message || 'Tidak ada chat ID baru ditemukan.';
                                html =
                                    `<div class="alert alert-warning mb-0"><i class="fa fa-exclamation-triangle me-1"></i> ${msg}</div>`;
                            }

                            $('#telegramResults').html(html);
                        },
                        error: function(xhr, status, error) {
                            console.error("AJAX Error Status:", status, error);
                            console.error("XHR Object:", xhr);

                            let errorMsg = 'Error fetching data from Telegram API (' + xhr.status +
                                ')';
                            if (xhr.responseJSON && xhr.responseJSON.message) {
                                errorMsg = xhr.responseJSON.message;
                            }
                            $('#telegramResults').html(
                                `<div class="alert alert-danger mb-0"><i class="fa fa-times-circle me-1"></i> ${errorMsg}</div>`
                            );
                        }
                    });
                });

                // Save button handler
                $(document).on('click', '#saveTelegramBtn', function() {
                    if (newTelegramChatIds.length === 0) return;

                    $.ajax({
                        url: '{{ route('telegramchat.store') }}',
                        method: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}',
                            chat_ids: newTelegramChatIds
                        },
                        success: function(response) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil',
                                text: response.message,
                                showConfirmButton: false,
                                timer: 2000,
                                willClose: () => {
                                    window.location.href =
                                        '{{ route('telegramchat.index') }}';
                                }
                            });
                            $('#telegramModal').modal('hide');
                        },
                        error: function(xhr) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal',
                                text: 'Gagal menyimpan data: ' + xhr.status,
                            });
                        }
                    });
                });

                // Delete confirmation
                $(document).on('click', '.btn-confirm-delete', function() {
                    let id = $(this).data('id');
                    Swal.fire({
                        title: 'Apakah Anda yakin?',
                        text: "Data Telegram ini tidak akan dihapus permanen",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#d33',
                        cancelButtonColor: '#3085d6',
                        confirmButtonText: 'Ya, hapus!',
                        cancelButtonText: 'Batal'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            document.getElementById('delete-form-' + id).submit();
                        }
                    });
                });

                // Test message
                $(document).on('click', '.btn-test-message', function() {
                    let id = $(this).data('id');

                    Swal.fire({
                        title: 'Mengirim pesan...',
                        html: 'Mohon tunggu sebentar',
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    $.ajax({
                        url: `/cpanel/settings/telegramchat/${id}/test_message`,
                        type: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(res) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Sukses',
                                text: res.message,
                                showConfirmButton: false,
                                timer: 1000
                            });
                        },
                        error: function() {
                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal',
                                text: 'Pesan tidak berhasil dikirim.'
                            });
                        }
                    });
                });
            });
        </script>
    @endsection

    @section('custom_styles')
        <style>
            /* Improved spacing for search field and button */
            .input-group.search-group {
                gap: 0.5rem;
            }

            .input-group.search-group .form-control {
                margin-right: 0.5rem;
            }

            /* Action buttons spacing */
            .d-flex.gap-2>* {
                margin: 0.2rem;
            }

            /* Badge text color consistency */
            .badge.text-white {
                color: white !important;
            }

            /* Responsive adjustments */
            @media (max-width: 767.98px) {
                .input-group.search-group {
                    flex-wrap: nowrap;
                }

                .input-group.search-group .form-control {
                    flex: 1;
                    min-width: 0;
                }

                .card-header h5 {
                    font-size: 1.1rem;
                }

                .pagination .page-item .page-link {
                    padding: 0.25rem 0.5rem;
                    font-size: 0.875rem;
                }

                /* Mobile card spacing */
                .card.mb-3 {
                    margin-bottom: 1rem !important;
                }

                .card-body>div {
                    margin-bottom: 1rem;
                }
            }

            /* Select2 adjustments */
            .select2-container {
                z-index: 9999 !important;
            }

            .select2-container--bootstrap-5 .select2-selection {
                border: 1px solid #ced4da !important;
                border-radius: 0.375rem;
                padding: 0.375rem 0.75rem;
                min-height: calc(1.5em + 0.75rem + 2px);
            }

            .select2-container--bootstrap-5 .select2-selection__rendered {
                color: #212529;
                line-height: 1.5;
            }

            .select2-container--bootstrap-5 .select2-selection__arrow {
                height: 100%;
            }
        </style>
    @endsection

</x-layouts.cpanel.settings.app-layout>
