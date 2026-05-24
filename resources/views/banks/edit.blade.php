<x-layouts.cpanel.settings.app-layout title="Edit Account Bank">

    <div class="card-header py-3">
        <h5 class="m-0 font-weight-bold text-primary">
            <i class="fas fa-edit mr-2"></i> {{ __('bank.header.edit') }}
        </h5>
    </div>

    <div class="row">
        <div class="col-lg-12">
            {!! Form::model($bank, ['method' => 'PATCH', 'route' => ['banks.update', $bank->slug]]) !!}
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
                            {!! Form::text('name', null, [
                                'placeholder' => __('bank.placeholder.name'),
                                'class' => 'form-control',
                                'id' => 'accountName',
                                'required' => true,
                            ]) !!}
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
                            <div class="custom-control custom-radio mr-4">
                                {!! Form::radio('type', 'bank', true, [
                                    'class' => 'custom-control-input',
                                    'id' => 'type_bank',
                                    'required' => true,
                                ]) !!}
                                <label class="custom-control-label font-weight-bold text-dark cursor-pointer"
                                    for="type_bank">
                                    <i class="fas fa-university text-info mr-1"></i> {{ __('bank.label.bank-account') }}
                                </label>
                            </div>
                            <div class="custom-control custom-radio">
                                {!! Form::radio('type', 'ewallet', false, [
                                    'class' => 'custom-control-input',
                                    'id' => 'type_ewallet',
                                    'required' => true,
                                ]) !!}
                                <label class="custom-control-label font-weight-bold text-dark cursor-pointer"
                                    for="type_ewallet">
                                    <i class="fas fa-wallet text-warning mr-1"></i> {{ __('bank.label.ewallet') }}
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
                            {!! Form::text('account_number', null, [
                                'placeholder' => __('bank.placeholder.account_number'),
                                'class' => 'form-control text-monospace',
                                'id' => 'account_number',
                                'required' => true,
                            ]) !!}
                        </div>
                        @if ($errors->has('account_number'))
                            <span class="text-danger small mt-1 d-block">{{ $errors->first('account_number') }}</span>
                        @endif
                    </div>
                </div>

            </div>

            <div class="card-footer bg-light d-flex justify-content-start align-items-center">
                <a href="{{ route('banks.show', $bank) }}" class="btn btn-sm btn-outline-secondary mr-2">
                    <i class="fas fa-arrow-left mr-1"></i> Kembali
                </a>

                <button type="submit" class="btn btn-sm btn-success">
                    <i class="fa fa-save mr-1"></i> {{ __('bank.button.update') }}
                </button>
            </div>
            {!! Form::close() !!}
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
