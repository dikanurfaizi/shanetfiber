<x-layouts.cpanel.settings.app-layout title="Detail Akun Rekening Pusat">

    <div class="card-header py-3">
        <h5 class="m-0 fw-bold text-primary">
            <i class="fas fa-university me-2"></i> {{ __('bank.header.show') }}
        </h5>
    </div>

    <div class="card-body p-3 p-md-4">
        <div class="row">
            <div class="col-lg-5 border-lg-right mb-4 mb-lg-0 pb-3 pb-lg-0">
                <h6 class="fw-bold text-secondary mb-3">
                    <i class="fas fa-info-circle me-1"></i> {{ __('bank.label.info') }}
                </h6>

                <div class="mb-3">
                    <label class="text-muted small mb-1"><strong>{{ __('bank.label.bank-name') }}</strong></label>
                    <div class="h5 fw-bold text-dark">
                        <span class="badge rounded-pill bg-primary text-uppercase px-2 py-1">
                            <i class="fas fa-university me-1"></i> {{ $bank->bank }}
                        </span>
                    </div>
                </div>

                {{-- 👈 TAMBAHAN KOMPONEN VISUAL: IDENTIFIKASI TIPE CHANNEL REKENING PUSAT --}}
                <div class="mb-3">
                    <label class="text-muted small mb-1"><strong>{{ __('bank.label.type') }}</strong></label>
                    <div>
                        @if ($bank->type === 'ewallet')
                            <span class="badge bg-warning text-dark text-uppercase fw-bold px-2 py-1">
                                <i class="fas fa-wallet me-1"></i> {{ __('bank.label.ewallet') }}
                            </span>
                        @else
                            <span class="badge bg-info text-white text-uppercase fw-bold px-2 py-1">
                                <i class="fas fa-credit-card me-1"></i> {{ __('bank.label.bank-account') }}
                            </span>
                        @endif
                    </div>
                </div>

                <div class="mb-3">
                    <label class="text-muted small mb-1"><strong>{{ __('bank.label.account-number') }}</strong></label>
                    <div class="h5 fw-bold text-dark text-monospace" style="letter-spacing: 0.5px;">
                        <i
                            class="fas fa-hashtag text-muted me-2"></i>{{ implode(' ', str_split($bank->account_number, 4)) }}
                    </div>
                </div>

                <div class="mb-3">
                    <label class="text-muted small mb-1"><strong>{{ __('bank.label.name') }}</strong></label>
                    <div class="h6 text-dark fw-bold">
                        <i class="fas fa-user text-muted me-2"></i>{{ $bank->name }}
                    </div>
                </div>

                <div class="mb-3">
                    <label class="text-muted small mb-1"><strong>{{ __('bank.label.account-status') }}</strong></label>
                    <div>
                        @if ($bank->disabled === 'false')
                            <span class="badge rounded-pill bg-success px-2 py-1">
                                <i class="fas fa-check-circle me-1"></i> {{ __('bank.info.enabled') }}
                            </span>
                        @else
                            <span class="badge rounded-pill bg-danger px-2 py-1">
                                <i class="fas fa-times-circle me-1"></i> {{ __('bank.info.disabled') }}
                            </span>
                        @endif
                    </div>
                </div>

                <hr class="my-3">

                <div class="d-flex flex-wrap align-items-center justify-content-start style-gap-2">
                    <a href="{{ route('banks.index') }}" class="btn btn-sm btn-outline-secondary m-1">
                        <i class="fas fa-arrow-left me-1"></i> {{ __('bank.button.back') }}
                    </a>
                    <a href="{{ route('banks.edit', $bank->slug) }}" class="btn btn-sm btn-primary m-1">
                        <i class="fa fa-edit me-1"></i> {{ __('bank.button.update') }}
                    </a>
                </div>
            </div>

            <div class="col-lg-7 ps-lg-4">
                <div class="d-flex flex-row justify-content-between align-items-center mb-3">
                    <h6 class="fw-bold text-secondary m-0">{{ __('bank.label.connected-admins') }}</h6>

                    <button type="button" class="btn btn-sm btn-success flex-shrink-0 ms-2" data-bs-toggle="modal"
                        data-bs-target="#assignAdminModal">
                        <i class="fa fa-plus me-1"></i>
                        <span class="d-none d-sm-inline">{{ __('bank.button.assign-admin') }}</span>
                    </button>
                </div>

                <div class="table-responsive-custom">
                    <table class="table table-bordered table-striped table-hover valgin-middle table-responsive-card">
                        <thead class="bg-light text-dark">
                            <tr>
                                <th width="10%">{{ __('bank.table.no') }}</th>
                                <th>{{ __('bank.table.admin-name') }}</th>
                                <th>{{ __('bank.table.contact') }}</th>
                                <th width="20%" class="text-center">{{ __('bank.table.action') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($connectedAdmins as $user)
                                <tr>
                                    <td data-label="{{ __('bank.table.no') }}">
                                        {{ ($connectedAdmins->currentPage() - 1) * $connectedAdmins->perPage() + $loop->iteration }}
                                    </td>
                                    <td data-label="{{ __('bank.table.admin-name') }}">
                                        <span class="fw-bold text-dark">{{ $user->name }}</span>
                                    </td>
                                    <td data-label="{{ __('bank.table.contact') }}">
                                        <span class="text-monospace text-muted">{{ $user->contact ?? '-' }}</span>
                                    </td>
                                    <td data-label="{{ __('bank.table.action') }}" class="text-center-md">
                                        {!! Form::open([
                                            'route' => ['banks.unlink-user', $bank],
                                            'method' => 'DELETE',
                                            'id' => 'unlink-form-' . $user->id,
                                            'class' => 'd-inline',
                                        ]) !!}
                                        {!! Form::hidden('user_id', $user->id) !!}

                                        <button type="button"
                                            class="btn btn-xs w-100 btn-outline-danger js-unlink-btn toggle-width-btn"
                                            data-id="{{ $user->id }}" data-name="{{ $user->name }}"
                                            title="{{ __('bank.button.unlink') }}">
                                            <i class="fa fa-unlink me-1"></i> {{ __('bank.button.unlink') }}
                                        </button>
                                        {!! Form::close() !!}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-4 data-empty-card">
                                        <i class="fas fa-exclamation-circle fa-2x mb-2 text-warning"></i><br>
                                        {{ __('bank.text.no-connected-admins') }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="d-flex justify-content-end mt-3">
                    {!! $connectedAdmins->appends(request()->query())->links('pagination::bootstrap-4') !!}
                </div>
            </div>
        </div>
    </div>

    {{-- =============================================================== --}}
    {{-- MODAL ASSIGN ADMIN --}}
    {{-- =============================================================== --}}
    <div class="modal fade" id="assignAdminModal" tabindex="-1" role="dialog" aria-labelledby="assignAdminModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-md" role="document">
            <div class="modal-content">
                {!! Form::open(['route' => ['banks.link-user', $bank], 'method' => 'POST']) !!}
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title" id="assignAdminModalLabel">
                        <i class="fas fa-link me-2"></i> {{ __('bank.button.assign-admin') }}
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="select_user_id"><strong>{{ __('bank.placeholder.select_admin') }} <span
                                    class="text-danger">*</span></strong></label>
                        {!! Form::select('user_id', $availableUsers, null, [
                            'placeholder' => '-- ' . __('bank.placeholder.select_admin') . ' --',
                            'class' => 'form-control select2-modal',
                            'id' => 'select_user_id',
                            'required' => true,
                            'style' => 'width: 100%',
                        ]) !!}
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-secondary"
                        data-bs-dismiss="modal">{{ __('bank.button.cancel') }}</button>
                    <button type="submit" class="btn btn-sm btn-success">
                        <i class="fa fa-save me-1"></i> {{ __('bank.button.assign-admin') }}
                    </button>
                </div>
                {!! Form::close() !!}
            </div>
        </div>
    </div>

    @section('custom_scripts')
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                if (typeof $.fn.select2 !== 'undefined') {
                    $('.select2-modal').select2({
                        dropdownParent: $('#assignAdminModal')
                    });
                }

                $('#assignAdminModal').on('hidden.bs.modal', function() {
                    $('#select_user_id').val('').trigger('change');
                });

                const unlinkButtons = document.querySelectorAll('.js-unlink-btn');
                unlinkButtons.forEach(button => {
                    button.addEventListener('click', function() {
                        const userId = this.getAttribute('data-id');
                        const userName = this.getAttribute('data-name');

                        Swal.fire({
                            title: "{{ __('bank.text.unlink_title') ?? 'Putuskan Akses?' }}",
                            text: `{{ __('bank.text.unlink-confirmation') }} ${userName}?`,
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonColor: '#e3342f',
                            cancelButtonColor: '#6c757d',
                            confirmButtonText: "{{ __('bank.button.unlink') }}",
                            cancelButtonText: "{{ __('bank.button.cancel') }}",
                            reverseButtons: true
                        }).then((result) => {
                            if (result.isConfirmed) {
                                document.getElementById('unlink-form-' + userId).submit();
                            }
                        });
                    });
                });
            });
        </script>
    @endsection

    @section('custom_styles')
        <style>
            .valgin-middle td {
                vertical-align: middle !important;
            }

            .btn-xs {
                padding: .25rem .4rem;
                font-size: .75rem;
                line-height: 1.5;
                border-radius: .15rem;
            }

            .style-gap-2 {
                gap: 0.5rem;
            }

            @media (max-width: 767.98px) {
                .border-lg-right {
                    border-right: none !important;
                    border-bottom: 2px dashed #e3e6f0;
                }

                .table-responsive-card thead {
                    display: none;
                }

                .table-responsive-card,
                .table-responsive-card tbody,
                .table-responsive-card tr,
                .table-responsive-card td {
                    display: block;
                    width: 100%;
                }

                .table-responsive-card tr {
                    background: #ffffff !important;
                    border: 1px solid #e3e6f0;
                    border-radius: 6px;
                    box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.05);
                    margin-bottom: 12px;
                    padding: 8px 12px;
                }

                .table-responsive-card td {
                    text-align: right !important;
                    padding: 6px 4px !important;
                    border: none !important;
                    border-bottom: 1px solid #f8f9fc !important;
                    position: relative;
                    padding-left: 45% !important;
                }

                .table-responsive-card td:before {
                    content: attr(data-label);
                    position: absolute;
                    left: 4px;
                    width: 40%;
                    text-align: left;
                    font-weight: bold;
                    color: #4e73df;
                    font-size: 0.8rem;
                }

                .table-responsive-card td.data-empty-card {
                    text-align: center !important;
                    padding-left: 0 !important;
                }

                .table-responsive-card td.data-empty-card:before {
                    display: none;
                }

                .toggle-width-btn {
                    width: 100% !important;
                    display: block;
                    margin-top: 5px;
                }

                .text-center-md {
                    text-align: center !important;
                    padding-left: 0 !important;
                }

                .text-center-md:before {
                    display: none;
                }
            }

            @media (min-width: 992px) {
                .border-lg-right {
                    border-right: 1px solid #e3e6f0 !important;
                }

                .toggle-width-btn {
                    width: auto !important;
                }
            }
        </style>
    @endsection

</x-layouts.cpanel.settings.app-layout>
