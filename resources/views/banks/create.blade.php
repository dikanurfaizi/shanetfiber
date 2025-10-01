<x-layouts.cpanel.settings.app-layout title="Add Acount Bank">
    <div class="card-header py-3">
        <h5 class="m-0 font-weight-bold text-primary"> {{ __('bank.header.create') }}</h5>
    </div>

    <div class="row">
        <div class="col-lg-12">
            {!! Form::open(['route' => 'banks.store', 'method' => 'POST', 'id' => 'bankForm']) !!}
            <div class="card-body col-lg-8">

                {{-- Admin Area --}}
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

                {{-- Account Name --}}
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

                {{-- Bank Name --}}
                <div class="row align-items-center">
                    <div class="col-md-12 mb-3">
                        <label for="bank">
                            <strong>{{ __('bank.label.bank-name') }} <span class="text-danger">*</span></strong>
                        </label>
                        <select id="bank_name_select" name="bank" class="form-control">
                            <option value="">{{ __('bank.placeholder.select_bank') }}</option>
                            @foreach ($banks as $bank)
                                <option value="{{ $bank['short_name'] }}">
                                    {{ $bank['short_name'] }} - {{ $bank['name'] }}
                                </option>
                            @endforeach
                        </select>

                        @if ($errors->has('bank'))
                            <span class="text-danger">{{ $errors->first('bank') }}</span>
                        @endif
                    </div>
                </div>

                {{-- Account Number --}}
                <div class="row align-items-center">
                    <div class="col-md-12 mb-3">
                        <label for="account_number"><strong>{{ __('bank.label.account-number') }} <span
                                    class="text-danger">*</span></strong></label>
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
                    {{ __('bank.button.save') }}</button>
            </div>
            {!! Form::close() !!}
        </div>
    </div>

    {{-- Script --}}
    @section('custom_scripts')
        <script>
            // Function to update the account name field and disable it
            function toggleAccountName() {
                const switchSameAsAdmin = document.getElementById('sameAsAdminSwitch');
                const userSelect = document.getElementById('user_id');
                const accountName = document.getElementById('accountName');
                const errorMessage = document.getElementById('user_id_error');

                if (switchSameAsAdmin.checked) {
                    if (!userSelect.value) {
                        // Kalau admin area belum dipilih
                        errorMessage.style.display = 'inline';
                        accountName.value = '';
                        accountName.setAttribute('readonly', true);
                    } else {
                        // Kalau admin area sudah dipilih
                        const selectedOption = userSelect.options[userSelect.selectedIndex];
                        accountName.value = selectedOption.text;
                        accountName.setAttribute('readonly', true);
                        errorMessage.style.display = 'none';
                    }
                } else {
                    // Jika switch dimatikan
                    accountName.removeAttribute('readonly');
                    errorMessage.style.display = 'none';
                }
            }

            // Ensure the function is called when page is loaded
            document.addEventListener('DOMContentLoaded', function() {
                toggleAccountName(); // Call it to set initial state based on the switch
            });

            // Validate form before submission
            document.getElementById('bankForm').addEventListener('submit', function(event) {
                const userSelect = document.getElementById('user_id');
                const errorMessage = document.getElementById('user_id_error');
                const accountName = document.getElementById('accountName');
                const switchSameAsAdmin = document.getElementById('sameAsAdminSwitch');

                if (switchSameAsAdmin.checked && !userSelect.value) {
                    event.preventDefault(); // Prevent form submission
                    errorMessage.style.display = 'inline'; // Show error message
                    userSelect.classList.add('is-invalid'); // Add invalid class to dropdown
                    accountName.classList.add('is-invalid'); // Add invalid class to account name
                } else {
                    errorMessage.style.display = 'none'; // Hide error message
                    userSelect.classList.remove('is-invalid'); // Remove invalid class from dropdown
                    accountName.classList.remove('is-invalid'); // Remove invalid class from account name
                }
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
