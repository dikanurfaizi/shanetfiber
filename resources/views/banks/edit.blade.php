<x-layouts.cpanel.settings.app-layout title="Edit Account Bank">

    <div class="card-header py-3">
        <h5 class="m-0 fw-bold text-primary">
            <i class="fas fa-edit me-2"></i> {{ __('bank.header.edit') }}
        </h5>
    </div>

    <div class="row">
        <div class="col-lg-12">
            <form action="{{ route('banks.update', $bank->slug) }}" method="POST">
                @csrf
                @method('PATCH')
            <div class="card-body col-lg-8">

                <div class="row align-items-center">
                    <div class="col-md-12 mb-3">
                        <label class="text-dark" for="accountName">
                            <strong>{{ __('bank.label.name') }} <span class="text-danger">*</span></strong>
                        </label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fas fa-user"></i></span>
                            </div>
                            <input type="text" name="name" id="accountName" class="form-control" placeholder="{{ __('bank.placeholder.name') }}" value="{{ old('name', $bank->name) }}" required>
                        </div>
                        @if ($errors->has('name'))
                            <span class="text-danger small mt-1 d-block">{{ $errors->first('name') }}</span>
                        @endif
                    </div>
                </div>

                {{-- 👈 TAMBAHAN BARU: INPUT PILIHAN TIPE CHANNEL MENGGUNAKAN RADIO BUTTON --}}
                <div class="row align-items-center">
                    <div class="col-md-12 mb-3">
                        <label class="text-dark">
                            <strong>{{ __('bank.label.type') }} <span class="text-danger">*</span></strong>
                        </label>
                        <div class="d-flex align-items-center mt-1">
                            <div class="form-check me-4">
                                <input type="radio" name="type" id="type_bank" value="bank" class="form-check-input" {{ old('type', $bank->type ?? 'bank') === 'bank' ? 'checked' : '' }} required>
                                <label class="form-check-label fw-bold text-dark cursor-pointer" for="type_bank">
                                    <i class="fas fa-university text-info me-1"></i> {{ __('bank.label.bank-account') }}
                                </label>
                            </div>
                            <div class="form-check">
                                <input type="radio" name="type" id="type_ewallet" value="ewallet" class="form-check-input" {{ old('type', $bank->type ?? 'bank') === 'ewallet' ? 'checked' : '' }} required>
                                <label class="form-check-label fw-bold text-dark cursor-pointer" for="type_ewallet">
                                    <i class="fas fa-wallet text-warning me-1"></i> {{ __('bank.label.ewallet') }}
                                </label>
                            </div>
                        </div>
                        @if ($errors->has('type'))
                            <span class="text-danger small mt-1 d-block">{{ $errors->first('type') }}</span>
                        @endif
                    </div>
                </div>

                <div class="row align-items-center">
                    <div class="col-md-12 mb-3">
                        <label class="text-dark" for="bank_name_select">
                            <strong>Nama Bank / E-Wallet <span class="text-danger">*</span></strong>
                        </label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fas fa-money-check-alt"></i></span>
                            </div>

                            <select id="bank_name_select" name="bank" class="form-control select2-edit" required>
                                <option value="">{{ __('bank.placeholder.select_bank') }}</option>
                                @foreach ($banks as $b)
                                    <option value="{{ $b['short_name'] }}"
                                        {{ old('bank', trim(strtoupper($bank->bank))) == trim(strtoupper($b['short_name'])) ? 'selected' : '' }}>
                                        {{ $b['short_name'] }} - {{ $b['name'] }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        @if ($errors->has('bank'))
                            <span class="text-danger small mt-1 d-block">{{ $errors->first('bank') }}</span>
                        @endif
                    </div>
                </div>

                <div class="row align-items-center">
                    <div class="col-md-12 mb-3">
                        <label class="text-dark" for="account_number">
                            <strong>{{ __('bank.label.account-number') }} <span class="text-danger">*</span></strong>
                        </label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fas fa-credit-card"></i></span>
                            </div>
                            <input type="text" name="account_number" id="account_number" class="form-control text-monospace" placeholder="{{ __('bank.placeholder.account_number') }}" value="{{ old('account_number', $bank->account_number) }}" required>
                        </div>
                        @if ($errors->has('account_number'))
                            <span class="text-danger small mt-1 d-block">{{ $errors->first('account_number') }}</span>
                        @endif
                    </div>
                </div>

            </div>

            <div class="card-footer bg-light d-flex justify-content-start align-items-center">
                <a href="{{ route('banks.show', $bank) }}" class="btn btn-sm btn-outline-secondary me-2">
                    <i class="fas fa-arrow-left me-1"></i> Kembali
                </a>

                <button type="submit" class="btn btn-sm btn-success">
                    <i class="fa fa-save me-1"></i> {{ __('bank.button.update') }}
                </button>
            </div>
            </form>
        </div>
    </div>

    @section('custom_scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                if (typeof $.fn.select2 !== 'undefined') {
                    $('.select2-edit').select2();
                }
            });
        </script>
    @endsection

    @section('custom_styles')
        <style>
            .select2-container .select2-selection--single {
                height: 38px !important;
                padding: 6px 12px;
                border: 1px solid #ced4da;
                border-top-left-radius: 0 !important;
                border-bottom-left-radius: 0 !important;
            }

            .select2-container--default .select2-selection--single .select2-selection__rendered {
                line-height: 24px !important;
                padding-left: 0 !important;
            }

            .select2-container--default .select2-selection--single .select2-selection__arrow {
                height: 36px !important;
            }

            .input-group-text {
                background-color: #f8f9fc;
                width: 42px;
                justify-content: center;
            }

            .cursor-pointer {
                cursor: pointer;
            }
        </style>
    @endsection

</x-layouts.cpanel.settings.app-layout>
