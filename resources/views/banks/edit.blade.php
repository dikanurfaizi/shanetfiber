<x-layouts.cpanel.settings.app-layout title="Edit Acount Bank">
    <div class="card-header py-3">
        <h5 class="m-0 font-weight-bold text-primary"> {{ __('bank.header.edit') }}</h5>
    </div>

    <div class="row">
        <div class="col-lg-12">
            {!! Form::model($bank, ['method' => 'PATCH', 'route' => ['banks.update', $bank->slug]]) !!}
            <div class="card-body col-lg-8">

                <div class="row align-items-center">
                    <div class="col-md-12 mb-3">
                        <label for="user_id"><strong>{{ __('bank.label.admin') }} <span
                                    class="text-danger">*</span></strong></label>
                        <div class="input-group">
                            {!! Form::select('user_id', $users->pluck('name', 'id'), null, [
                                'placeholder' => __('bank.placeholder.select_admin'),
                                'class' => 'form-control',
                                'id' => 'user_id',
                            ]) !!}
                        </div>
                        @if ($errors->has('user_id'))
                            <span class="text-danger">{{ $errors->first('user_id') }}</span>
                        @endif
                    </div>
                </div>

                <div class="row align-items-center">
                    <div class="col-md-12 mb-3">
                        <div class="d-flex justify-content-between">
                            <div class="form-switch">
                                <input class="form-check-input" type="checkbox" id="sameAsAdminSwitch"
                                    onclick="toggleAccountName()">
                                <label class="form-check-label"
                                    for="sameAsAdminSwitch"><strong>{{ __('bank.label.same_as_admin') }}</strong>
                                </label>
                            </div>

                        </div>
                    </div>
                </div>

                <div class="row align-items-center">
                    <div class="col-md-12 mb-3">
                        <label for="name"><strong>{{ __('bank.label.name') }} <span
                                    class="text-danger">*</span></strong></label>
                        <div class="input-group">
                            {!! Form::text('name', null, [
                                'placeholder' => __('bank.placeholder.name'),
                                'class' => 'form-control',
                                'id' => 'accountName',
                            ]) !!}
                        </div>
                        @if ($errors->has('name'))
                            <span class="text-danger">{{ $errors->first('name') }}</span>
                        @endif
                        <span id="user_id_error" class="text-danger"
                            style="display:none;">{{ __('bank.error.select_admin') }}</span>
                    </div>
                </div>

                <div class="row align-items-center">
                    <div class="col-md-12 mb-3">
                        <label for="bank"><strong>{{ __('bank.label.bank-name') }} <span
                                    class="text-danger">*</span></strong></label>
                        {{-- <select id="bank_name_select" name="bank" class="form-control">
                            <option value="">{{ __('bank.placeholder.select_bank') }}</option>
                        </select> --}}
                        <select id="bank_name_select" name="bank" class="form-control">
                            <option value="">{{ __('bank.placeholder.select_bank') }}</option>
                        </select>
                        @if ($errors->has('bank'))
                            <span class="text-danger">{{ $errors->first('bank') }}</span>
                        @endif
                    </div>
                </div>

                <div class="row align-items-center">
                    <div class="col-md-12 mb-3">
                        <label for="account_number"><strong>{{ __('bank.label.account-number') }}</strong></label>
                        <div class="input-group">
                            {!! Form::text('account_number', null, [
                                'placeholder' => __('bank.placeholder.account_number'),
                                'class' => 'form-control',
                            ]) !!}
                        </div>
                        @if ($errors->has('account_number'))
                            <span class="text-danger">{{ $errors->first('account_number') }}</span>
                        @endif
                    </div>
                </div>
            </div>
            <div class="card-footer">
                <button type="submit" class="btn btn-sm btn-success"><i class="fa fa-save"></i> &nbsp
                    {{ __('bank.button.update') }}</button>
            </div>
            {!! Form::close() !!}
        </div>
    </div>

    @section('custom_scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const sameAsAdminCheckbox = document.getElementById('sameAsAdminSwitch');
                const userSelect = document.getElementById('user_id');
                const accountName = document.getElementById('accountName');
                const errorMessage = document.getElementById('user_id_error');

                function syncAccountName() {
                    const selectedText = userSelect.options[userSelect.selectedIndex]?.text?.trim();
                    const currentName = accountName.value.trim();

                    // Jika nama admin sama dengan name input → centang otomatis
                    if (userSelect.value && selectedText === currentName && selectedText !== '') {
                        sameAsAdminCheckbox.checked = true;
                        applyAccountNameLock();
                    } else {
                        sameAsAdminCheckbox.checked = false;
                        accountName.removeAttribute('readonly');
                    }
                }

                function applyAccountNameLock() {
                    const selectedOption = userSelect.options[userSelect.selectedIndex];
                    if (!userSelect.value) {
                        sameAsAdminCheckbox.checked = false;
                        accountName.value = '';
                        accountName.removeAttribute('readonly');
                        errorMessage.style.display = 'inline';
                    } else if (sameAsAdminCheckbox.checked) {
                        accountName.value = selectedOption.text.trim();
                        accountName.setAttribute('readonly', true);
                        errorMessage.style.display = 'none';
                    } else {
                        accountName.removeAttribute('readonly');
                        errorMessage.style.display = 'none';
                    }
                }

                // Assign ke global supaya onclick di HTML tetap jalan
                window.toggleAccountName = function() {
                    if (!userSelect.value) {
                        sameAsAdminCheckbox.checked = false;
                        accountName.value = '';
                        accountName.removeAttribute('readonly');
                        errorMessage.style.display = 'inline';
                    } else {
                        applyAccountNameLock();
                    }
                };

                // Event: saat user pilih dropdown admin
                userSelect.addEventListener('change', function() {
                    if (!userSelect.value) {
                        sameAsAdminCheckbox.checked = false;
                        accountName.value = '';
                        accountName.removeAttribute('readonly');
                        errorMessage.style.display = 'inline';
                    } else {
                        errorMessage.style.display = 'none';
                        if (sameAsAdminCheckbox.checked) {
                            applyAccountNameLock();
                        }
                    }
                });

                // Validasi sebelum submit
                document.querySelector('form').addEventListener('submit', function(event) {
                    if (sameAsAdminCheckbox.checked && !userSelect.value) {
                        event.preventDefault();
                        errorMessage.style.display = 'inline';
                        userSelect.classList.add('is-invalid');
                        accountName.classList.add('is-invalid');
                    } else {
                        errorMessage.style.display = 'none';
                        userSelect.classList.remove('is-invalid');
                        accountName.classList.remove('is-invalid');
                    }
                });

                // Inisialisasi saat halaman load
                syncAccountName();
            });
        </script>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                fetch('/data/indonesia-bank.json')
                    .then(response => response.json())
                    .then(data => {
                        let select = document.getElementById('bank_name_select');

                        // Sort ascending berdasarkan code numerik, yang null taruh bawah
                        data.sort((a, b) => {
                            let codeA = a.code || 'ZZZ';
                            let codeB = b.code || 'ZZZ';
                            return codeA.localeCompare(b.code);
                        });

                        let selectedBank = "{{ old('bank', $bank->bank ?? '') }}".trim().toLowerCase();

                        data.forEach(function(bank) {
                            let option = document.createElement('option');
                            let code = bank.code ? bank.code.toString().padStart(3, '0') : '---';
                            option.value = bank.short_name;
                            option.text = `${code} - ${bank.short_name} (${bank.name})`;

                            // Logic auto select berdasarkan short_name atau full name
                            let shortName = (bank.short_name || '').toLowerCase();
                            let fullName = (bank.name || '').toLowerCase();

                            // Cek kecocokan persis dengan short_name atau full_name
                            if (selectedBank === shortName || selectedBank === fullName ||
                                fullName.includes(selectedBank) || shortName.includes(selectedBank)) {
                                option.selected = true;
                            }

                            select.appendChild(option);
                        });

                        // Inisialisasi Select2
                        $(select).select2({
                            placeholder: "{{ __('bank.placeholder.select_bank') }}",
                            allowClear: true
                        });
                    })
                    .catch(error => {
                        console.error('Error loading bank list:', error);
                    });
            });
        </script>
    @endsection

    <style>
        .select2-container .select2-selection--single {
            height: 38px !important;
            padding: 6px 12px;
            border: 1px solid #ced4da;
            border-radius: 4px;
        }

        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 24px !important;
        }

        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 36px !important;
        }
    </style>

</x-layouts.cpanel.settings.app-layout>
