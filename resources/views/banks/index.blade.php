<x-layouts.cpanel.settings.app-layout title="Banks">

    <div class="card-header py-3 d-flex flex-row justify-content-between align-items-center">
        <h5 class="m-0 font-weight-bold text-primary">{{ __('bank.header.index') }}</h5>
        <a class="btn btn-sm btn-success flex-shrink-0" href="{{ route('banks.create') }}">
            <i class="fa fa-plus mr-1"></i> {{ __('bank.button.create') }}
        </a>
    </div>

    <div class="card-body p-3 p-md-4">

        {{-- =============================================================== --}}
        {{-- DESKTOP VIEW --}}
        {{-- =============================================================== --}}
        <div class="d-none d-md-block" style="overflow-x: auto;">
            <table id="banksTable" cellspacing="0" width="100%"
                class="table datatable-loading no-footer sortable searchable">
                <thead>
                    <tr>
                        <th width="5%">#</th>
                        <th>{{ __('bank.table.name') }}</th>
                        <th>{{ __('bank.table.bank') }}</th>
                        <th>{{ __('bank.table.type') }}</th>
                        <th>{{ __('bank.table.account-number') }}</th>
                        <th>{{ __('bank.table.status') }}</th>
                        <th class="text-right" width="10%">{{ __('bank.table.action') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @php $desktopI = $i; @endphp
                    @foreach ($banks as $bank)
                        <tr>
                            <td>
                                <span
                                    class="badge badge-pill badge-light text-secondary border">{{ ++$desktopI }}</span>
                            </td>
                            <td>
                                <span class="font-weight-bold {{ $bank->is_active ? 'text-primary' : 'text-muted' }}">
                                    {{ $bank->name }}
                                </span>
                            </td>
                            <td>
                                <span class="badge badge-pill badge-light text-dark border text-uppercase">
                                    {{ $bank->bank }}
                                </span>
                            </td>
                            {{-- 👈 PILIHAN WARNA BADGE SESUAI TIPE (DESKTOP) --}}
                            <td>
                                @if ($bank->type === 'ewallet')
                                    <span
                                        class="badge badge-warning text-dark text-uppercase font-weight-bold px-2 py-1">
                                        <i class="fas fa-wallet mr-1"></i> E-Wallet
                                    </span>
                                @else
                                    <span class="badge badge-info text-white text-uppercase font-weight-bold px-2 py-1">
                                        <i class="university mr-1"></i> Bank
                                    </span>
                                @endif
                            </td>
                            <td>
                                <span class="text-monospace font-weight-bold text-dark">
                                    {{ implode(' ', str_split($bank->account_number, 4)) }}
                                </span>
                            </td>
                            <td>
                                @if ($bank->is_active)
                                    <span class="badge text-white bg-success">
                                        <i class="fas fa-lock-open mr-1"></i> {{ __('bank.info.enabled') }}
                                    </span>
                                @else
                                    <span class="badge text-white bg-danger">
                                        <i class="fas fa-lock mr-1"></i> {{ __('bank.info.disabled') }}
                                    </span>
                                @endif
                            </td>
                            <td class="text-right">
                                <x-drop-down-action>
                                    <li>
                                        <a class="dropdown-item" href="{{ route('banks.show', $bank) }}">
                                            <i class="far fa-eye mr-2 text-info"></i> {{ __('bank.action.show') }}
                                        </a>
                                    </li>
                                    <li>
                                        @if ($bank->is_active)
                                            <form method="POST" class="d-inline"
                                                action="{{ route('bank.disable', $bank) }}">
                                                @csrf @method('put')
                                                <a class="dropdown-item" href="#"
                                                    onclick="event.preventDefault(); this.closest('form').submit();">
                                                    <i class="fa fa-times text-warning mr-2"></i>
                                                    {{ __('bank.action.disable') }}
                                                </a>
                                            </form>
                                        @else
                                            <form method="POST" class="d-inline"
                                                action="{{ route('bank.enable', $bank) }}">
                                                @csrf @method('put')
                                                <a class="dropdown-item" href="#"
                                                    onclick="event.preventDefault(); this.closest('form').submit();">
                                                    <i class="fa fa-check text-success mr-2"></i>
                                                    {{ __('bank.action.enable') }}
                                                </a>
                                            </form>
                                        @endif
                                    </li>
                                    <li>
                                        {!! Form::open([
                                            'method' => 'DELETE',
                                            'route' => ['banks.destroy', $bank],
                                            'style' => 'display:inline',
                                            'id' => 'delete-form-desktop-' . $bank->slug,
                                        ]) !!}
                                        <a href="#" class="dropdown-item js-delete-btn" data-type="desktop"
                                            data-slug="{{ $bank->slug }}"
                                            data-info="{{ $bank->name }} ({{ $bank->bank }})">
                                            <i class="fa fa-trash mr-2 text-danger"></i> {{ __('bank.action.delete') }}
                                        </a>
                                        {!! Form::close() !!}
                                    </li>
                                </x-drop-down-action>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- =============================================================== --}}
        {{-- MOBILE VIEW --}}
        {{-- =============================================================== --}}
        <div class="d-md-none">
            @php $mobileI = $i; @endphp
            @foreach ($banks as $bank)
                <div class="card shadow-sm border mb-3 text-left">
                    <div
                        class="card-header bg-light p-2 d-flex justify-content-between align-items-center border-bottom">
                        <span class="badge badge-pill badge-secondary px-2 py-1">#{{ ++$mobileI }}</span>
                        <div>
                            @if ($bank->disabled === 'false')
                                <span class="badge text-white bg-success py-1"><i class="fas fa-lock-open mr-1"></i>
                                    {{ __('bank.info.enabled') }}</span>
                            @else
                                <span class="badge text-white bg-danger py-1"><i class="fas fa-lock mr-1"></i>
                                    {{ __('bank.info.disabled') }}</span>
                            @endif
                        </div>
                    </div>

                    <div class="card-body p-3">
                        {{-- 👈 BERBAGAI PILIHAN BADGE SESUAI TIPE (MOBILE) --}}
                        <div class="mb-2">
                            <small class="text-muted d-block font-weight-bold mb-1">Tipe Channel</small>
                            @if ($bank->type === 'ewallet')
                                <span class="badge badge-warning text-dark text-uppercase font-weight-bold px-2 py-1">
                                    <i class="fas fa-wallet mr-1"></i> E-Wallet
                                </span>
                            @else
                                <span class="badge badge-info text-white text-uppercase font-weight-bold px-2 py-1">
                                    <i class="university mr-1"></i> Bank
                                </span>
                            @endif
                        </div>
                        <div class="mb-2">
                            <small class="text-muted d-block font-weight-bold mb-0">{{ __('bank.table.bank') }}</small>
                            <span
                                class="badge badge-pill badge-light text-dark border text-uppercase px-2 py-1 h6 font-weight-bold">
                                {{ $bank->bank }}
                            </span>
                        </div>
                        <div class="mb-2">
                            <small
                                class="text-muted d-block font-weight-bold mb-0">{{ __('bank.table.account-number') }}</small>
                            <span
                                class="h5 font-weight-bold text-dark text-monospace">{{ implode(' ', str_split($bank->account_number, 4)) }}</span>
                        </div>
                        <div class="mb-0">
                            <small class="text-muted d-block font-weight-bold mb-0">{{ __('bank.table.name') }}</small>
                            <span
                                class="font-weight-bold {{ $bank->disabled === 'false' ? 'text-primary' : 'text-muted' }}">
                                {{ $bank->name }}
                            </span>
                        </div>
                    </div>

                    <div class="card-footer bg-white p-2 d-flex justify-content-end border-top-0">
                        <x-drop-down-action>
                            <li>
                                <a class="dropdown-item py-2" href="{{ route('banks.show', $bank) }}">
                                    <i class="far fa-eye mr-2 text-info"></i> {{ __('bank.action.show') }}
                                </a>
                            </li>
                            <li>
                                @if ($bank->disabled === 'false')
                                    <form method="POST" class="d-inline" action="{{ route('bank.disable', $bank) }}">
                                        @csrf @method('put')
                                        <a class="dropdown-item py-2" href="#"
                                            onclick="event.preventDefault(); this.closest('form').submit();">
                                            <i class="fa fa-times text-warning mr-2"></i>
                                            {{ __('bank.action.disable') }}
                                        </a>
                                    </form>
                                @else
                                    <form method="POST" class="d-inline" action="{{ route('bank.enable', $bank) }}">
                                        @csrf @method('put')
                                        <a class="dropdown-item py-2" href="#"
                                            onclick="event.preventDefault(); this.closest('form').submit();">
                                            <i class="fa fa-check text-success mr-2"></i>
                                            {{ __('bank.action.enable') }}
                                        </a>
                                    </form>
                                @endif
                            </li>
                            <li>
                                {!! Form::open([
                                    'method' => 'DELETE',
                                    'route' => ['banks.destroy', $bank],
                                    'style' => 'display:inline',
                                    'id' => 'delete-form-mobile-' . $bank->slug,
                                ]) !!}
                                <a href="#" class="dropdown-item py-2 js-delete-btn" data-type="mobile"
                                    data-slug="{{ $bank->slug }}"
                                    data-info="{{ $bank->name }} ({{ $bank->bank }})">
                                    <i class="fa fa-trash mr-2 text-danger"></i> {{ __('bank.action.delete') }}
                                </a>
                                {!! Form::close() !!}
                            </li>
                        </x-drop-down-action>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="d-flex justify-content-end mt-3">
            {{ $banks->links() }}
        </div>
    </div>

    @section('custom_scripts')
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const deleteButtons = document.querySelectorAll('.js-delete-btn');

                deleteButtons.forEach(button => {
                    button.addEventListener('click', function(event) {
                        event.preventDefault();

                        const type = this.getAttribute('data-type');
                        const slug = this.getAttribute('data-slug');
                        const info = this.getAttribute('data-info');

                        const targetFormId = 'delete-form-' + type + '-' + slug;

                        Swal.fire({
                            title: 'Hapus Rekening Pusat?',
                            text: `Apakah Anda yakin ingin menghapus data akun rekening ${info} secara permanen?`,
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonColor: '#e3342f',
                            cancelButtonColor: '#6c757d',
                            confirmButtonText: 'Ya, Hapus!',
                            cancelButtonText: 'Batal',
                            reverseButtons: true
                        }).then((result) => {
                            if (result.isConfirmed) {
                                document.getElementById(targetFormId).submit();
                            }
                        });
                    });
                });
            });
        </script>
    @endsection

</x-layouts.cpanel.settings.app-layout>
