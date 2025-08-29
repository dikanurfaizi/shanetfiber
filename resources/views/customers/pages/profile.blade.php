<x-layouts.customers.app-layout title="Profile">

    <div class="card-header py-3 bg-gradient-primary">
        <h5 class="m-0 font-weight-bold text-white d-flex align-items-center">
            <i class="fas fa-user me-3" style="font-size: 1.1em"></i>
            <span class="ml-2 text-wrap">{{ __('customer.page.header.profile') }} -
                {{ auth()->guard('customer')->user()->name }}</span>
        </h5>
    </div>

    <div class="card-body">
        <div class="row">
            <!-- Profile Information -->
            <div class="col-lg-6 d-flex">
                <div class="card shadow mb-4 w-100">
                    <div class="card-header py-3 bg-light">
                        <h6 class="m-0 font-weight-bold text-primary d-flex justify-content-between align-items-center">
                            <span>
                                <i class="fas fa-info-circle mr-2"></i>
                                {{ __('customer.header.profile-info') }}
                            </span>
                            <button class="btn btn-sm btn-primary" id="editProfileBtn">
                                <i class="fas fa-edit"></i> {{ __('customer.button.edit-profile') }}
                            </button>
                        </h6>
                    </div>
                    <div class="card-body">
                        <!-- View Mode -->
                        <div id="viewMode">
                            <table class="table table-borderless table-sm">
                                <tr>
                                    <td width="30%"><strong>{{ __('customer.label.name') }}</strong></td>
                                    <td>: {{ $customer->name }}</td>
                                </tr>
                                <tr>
                                    <td><strong>{{ __('customer.label.username') }}</strong></td>
                                    <td>: {{ $customer->username }}</td>
                                </tr>
                                <tr>
                                    <td><strong>{{ __('customer.label.contact') }}</strong></td>
                                    <td>: {{ $customer->contact_formatted }}</td>
                                </tr>
                                <tr>
                                    <td><strong>{{ __('customer.label.email') }}</strong></td>
                                    <td class="email-column">: {{ $customer->email }}</td>
                                </tr>
                                <tr>
                                    <td><strong>{{ __('customer.label.address') }}</strong></td>
                                    <td>: {{ $customer->address }}</td>
                                </tr>
                            </table>
                        </div>

                        <!-- Edit Mode (Initially Hidden) -->
                        <div id="editMode" style="display: none;">
                            <form id="profileForm" action="{{ route('customer.profile.update') }}" method="POST">
                                @csrf
                                @method('PUT')

                                <div class="form-group row">
                                    <label for="name" class="col-sm-3 col-form-label">
                                        <strong>{{ __('customer.label.name') }}</strong>
                                    </label>
                                    <div class="col-sm-9">
                                        <input type="text" class="form-control" id="name" name="name"
                                            value="{{ $customer->name }}" required>
                                    </div>
                                </div>

                                <div class="form-group row">
                                    <label class="col-sm-3 col-form-label">
                                        <strong>{{ __('customer.label.username') }}</strong>
                                    </label>
                                    <div class="col-sm-9">
                                        <input type="text" class="form-control" value="{{ $customer->username }}"
                                            disabled>
                                    </div>
                                </div>

                                <div class="form-group row">
                                    <label for="contact" class="col-sm-3 col-form-label">
                                        <strong>{{ __('customer.label.contact') }}</strong>
                                    </label>
                                    <div class="col-sm-9">
                                        <input type="text" class="form-control" id="contact" name="contact"
                                            value="{{ $customer->contact_formatted }}" required>
                                    </div>
                                </div>

                                <div class="form-group row">
                                    <label for="email" class="col-sm-3 col-form-label">
                                        <strong>{{ __('customer.label.email') }}</strong>
                                    </label>
                                    <div class="col-sm-9">
                                        <input type="email" class="form-control" id="email" name="email"
                                            value="{{ $customer->email }}" required>
                                    </div>
                                </div>

                                <div class="form-group row">
                                    <label for="address" class="col-sm-3 col-form-label">
                                        <strong>{{ __('customer.label.address') }}</strong>
                                    </label>
                                    <div class="col-sm-9">
                                        <textarea class="form-control" id="address" name="address" rows="3">{{ $customer->address }}</textarea>
                                    </div>
                                </div>

                                <div class="form-group row mt-4">
                                    <div class="col-sm-9 offset-sm-3">
                                        <button type="submit" class="btn btn-success mr-2">
                                            <i class="fas fa-save"></i> {{ __('customer.button.update-my-data') }}
                                        </button>
                                        <button type="button" id="cancelEditBtn" class="btn btn-secondary">
                                            <i class="fas fa-times"></i> {{ __('customer.button.cancel') }}
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Password Change Section -->
            <div class="col-lg-6 d-flex">
                <div class="card shadow mb-4 w-100">
                    <div class="card-header py-3 bg-light">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="fas fa-lock mr-2"></i>
                            {{ __('customer.header.change-password') }}
                        </h6>
                    </div>
                    <div class="card-body">
                        <form id="passwordForm" action="{{ route('customer.password.update') }}" method="POST">
                            @csrf
                            @method('PUT')

                            <!-- Current Password -->
                            <div class="form-group row">
                                <label for="current_password" class="col-sm-4 col-form-label">
                                    <strong>{{ __('customer.label.current-password') }}</strong>
                                </label>
                                <div class="col-sm-8">
                                    <div class="input-group">
                                        <input type="password" class="form-control" id="current_password"
                                            name="current_password" required>
                                        <div class="input-group-append">
                                            <span class="input-group-text toggle-password"
                                                data-target="#current_password">
                                                <i class="fas fa-eye-slash"></i>
                                            </span>
                                        </div>
                                    </div>
                                    <small
                                        class="text-muted">{{ __('customer.message.current-password-help') }}</small>
                                    <div class="invalid-feedback" id="current_password_error"></div>
                                </div>
                            </div>

                            <!-- New Password -->
                            <div class="form-group row mt-3">
                                <label for="new_password" class="col-sm-4 col-form-label">
                                    <strong>{{ __('customer.label.new-password') }}</strong>
                                </label>
                                <div class="col-sm-8">
                                    <div class="input-group">
                                        <input type="password" class="form-control" id="new_password"
                                            name="new_password" required>
                                        <div class="input-group-append">
                                            <span class="input-group-text toggle-password"
                                                data-target="#new_password">
                                                <i class="fas fa-eye-slash"></i>
                                            </span>
                                        </div>
                                    </div>

                                    <!-- Password Strength Meter -->
                                    <div class="password-strength mt-2">
                                        <div class="progress" style="height: 5px;">
                                            <div id="password-strength-bar" class="progress-bar" role="progressbar"
                                                style="width: 0%"></div>
                                        </div>
                                    </div>

                                    <!-- Password Requirements -->
                                    <div class="password-requirements mt-2">
                                        <small class="text-muted">{{ __('Password harus mengandung:') }}</small>
                                        <ul class="list-unstyled" id="password-rules">
                                            <li class="text-danger" id="rule-length"><i
                                                    class="fas fa-times-circle mr-1"></i> Minimal 8 karakter</li>
                                            <li class="text-danger" id="rule-uppercase"><i
                                                    class="fas fa-times-circle mr-1"></i> 1 huruf besar (A-Z)</li>
                                            <li class="text-danger" id="rule-lowercase"><i
                                                    class="fas fa-times-circle mr-1"></i> 1 huruf kecil (a-z)</li>
                                            <li class="text-danger" id="rule-number"><i
                                                    class="fas fa-times-circle mr-1"></i> 1 angka (0-9)</li>
                                            <li class="text-danger" id="rule-special"><i
                                                    class="fas fa-times-circle mr-1"></i> 1 karakter khusus (@$!%*?&)
                                            </li>
                                        </ul>
                                    </div>
                                    <div class="invalid-feedback" id="new_password_error"></div>
                                </div>
                            </div>

                            <!-- Confirm Password -->
                            <div class="form-group row mt-3">
                                <label for="new_password_confirmation" class="col-sm-4 col-form-label">
                                    <strong>{{ __('customer.label.confirm-password') }}</strong>
                                </label>
                                <div class="col-sm-8">
                                    <div class="input-group">
                                        <input type="password" class="form-control" id="new_password_confirmation"
                                            name="new_password_confirmation" required>
                                        <div class="input-group-append">
                                            <span class="input-group-text toggle-password"
                                                data-target="#new_password_confirmation">
                                                <i class="fas fa-eye-slash"></i>
                                            </span>
                                        </div>
                                    </div>
                                    <div id="password-match-feedback" class="mt-1"
                                        style="font-size: 0.875em; display: none;">
                                        <i class="fas mr-1"></i> <span></span>
                                    </div>
                                </div>
                            </div>

                            <!-- Forgot Password Link -->
                            <div class="form-group row mt-2">
                                <div class="col-sm-8 offset-sm-4">
                                    <a href="{{ route('password.request') }}" class="text-primary">
                                        <small><i class="fas fa-question-circle mr-1"></i> Lupa password?</small>
                                    </a>
                                </div>
                            </div>

                            <!-- Submit Button -->
                            <div class="form-group row mt-3">
                                <div class="col-sm-8 offset-sm-4">
                                    <button type="submit" class="btn btn-primary" id="submitBtn">
                                        <i class="fas fa-key mr-1"></i> {{ __('customer.button.change-password') }}
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="loadingBarContainer" class="progress mt-3" style="display:none; height: 6px;">
        <div id="loadingBar" class="progress-bar progress-bar-striped progress-bar-animated bg-success"
            role="progressbar" style="width: 100%"></div>
    </div>

    @section('custom_styles')
        <style>
            .table-borderless td,
            .table-borderless th {
                border: none;
            }

            .card {
                border-radius: 0.5rem;
            }

            .card-header {
                border-radius: 0.5rem 0.5rem 0 0 !important;
            }

            .form-control {
                border-radius: 0.25rem;
            }

            .bg-gradient-primary {
                background: linear-gradient(87deg, #4e73df 0, #224abe 100%) !important;
            }

            .password-strength {
                height: 5px;
                margin-top: 5px;
                background: #e9ecef;
                border-radius: 3px;
                overflow: hidden;
            }

            .password-strength-bar {
                height: 100%;
                width: 0;
                transition: width 0.3s ease;
            }

            .password-requirements ul {
                margin-top: 5px;
                margin-bottom: 0;
            }

            .password-requirements li {
                font-size: 0.8rem;
                margin-bottom: 2px;
            }

            .password-requirements i {
                width: 15px;
                text-align: center;
            }

            #password-match-feedback i {
                width: 15px;
                text-align: center;
            }

            .progress {
                background-color: #e9ecef;
                border-radius: 3px;
            }

            .progress-bar {
                transition: width 0.3s ease, background-color 0.3s ease;
            }

            .toggle-password {
                cursor: pointer;
                background-color: #fff;
                border-left: 0;
                transition: all 0.3s;
            }

            .toggle-password:hover {
                background-color: #f8f9fa;
            }

            .input-group-text {
                border-radius: 0 0.25rem 0.25rem 0 !important;
            }
        </style>
    @endsection

    @section('custom_scripts')
        <script>
            $(document).ready(function() {
                // Toggle between view and edit mode for profile
                $('#editProfileBtn').click(function() {
                    $('#viewMode').hide();
                    $('#editMode').show();
                });

                $('#cancelEditBtn').click(function() {
                    $('#editMode').hide();
                    $('#viewMode').show();
                });

                // Form submission handling for profile
                $('#profileForm').submit(function(e) {
                    e.preventDefault();

                    Swal.fire({
                        title: 'Mohon tunggu...',
                        text: 'Sedang memperbarui data',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    $.ajax({
                        url: $(this).attr('action'),
                        method: 'POST',
                        data: $(this).serialize(),
                        success: function(response) {
                            if (response.success) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Sukses',
                                    text: response.message,
                                    confirmButtonText: 'OK'
                                }).then(() => {
                                    location.reload();
                                });
                            }
                        },
                        error: function(xhr) {
                            if (xhr.status === 422) {
                                let errors = xhr.responseJSON.errors;
                                let errorMessages = '';

                                $.each(errors, function(key, value) {
                                    errorMessages += value[0] + '<br>';
                                });

                                Swal.fire({
                                    icon: 'error',
                                    title: 'Validasi Gagal',
                                    html: errorMessages,
                                    confirmButtonText: 'OK'
                                });
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: xhr.responseJSON?.message || 'Terjadi kesalahan',
                                    confirmButtonText: 'OK'
                                });
                            }
                        }
                    });
                });

                $(document).on('click', '.toggle-password', function() {
                    const target = $(this).data('target');
                    const input = $(target);
                    const icon = $(this).find('i');

                    if (input.attr('type') === 'password') {
                        input.attr('type', 'text');
                        icon.removeClass('fa-eye-slash').addClass('fa-eye');
                    } else {
                        input.attr('type', 'password');
                        icon.removeClass('fa-eye').addClass('fa-eye-slash');
                    }
                });

                // Password validation real-time feedback
                $('#new_password').on('input', function() {
                    const password = $(this).val();

                    // Check rules
                    const hasLength = password.length >= 8;
                    const hasUppercase = /[A-Z]/.test(password);
                    const hasLowercase = /[a-z]/.test(password);
                    const hasNumber = /[0-9]/.test(password);
                    const hasSpecial = /[@$!%*?&]/.test(password);

                    // Update rules display
                    updateRule('length', hasLength);
                    updateRule('uppercase', hasUppercase);
                    updateRule('lowercase', hasLowercase);
                    updateRule('number', hasNumber);
                    updateRule('special', hasSpecial);

                    // Calculate strength (0-4)
                    let strength = 0;
                    if (hasLength) strength += 1;
                    if (hasUppercase) strength += 1;
                    if (hasLowercase) strength += 1;
                    if (hasNumber) strength += 1;
                    if (hasSpecial) strength += 1;

                    // Update strength meter
                    const width = (strength / 5) * 100;
                    let color = '#dc3545'; // red

                    if (strength >= 4) {
                        color = '#28a745'; // green
                    } else if (strength >= 2) {
                        color = '#ffc107'; // yellow
                    }

                    $('#password-strength-bar').css({
                        'width': width + '%',
                        'background-color': color
                    });
                });

                // Confirm password matching
                $('#new_password, #new_password_confirmation').on('input', function() {
                    const password = $('#new_password').val();
                    const confirmPassword = $('#new_password_confirmation').val();

                    if (confirmPassword.length === 0) {
                        $('#password-match-feedback').hide();
                        return;
                    }

                    const feedback = $('#password-match-feedback');
                    const icon = feedback.find('i');
                    const text = feedback.find('span');

                    if (password === confirmPassword) {
                        icon.removeClass('fa-times text-danger').addClass('fa-check text-success');
                        text.text('Password cocok').removeClass('text-danger').addClass('text-success');
                    } else {
                        icon.removeClass('fa-check text-success').addClass('fa-times text-danger');
                        text.text('Password tidak cocok').removeClass('text-success').addClass('text-danger');
                    }

                    feedback.show();
                });

                function updateRule(rule, isValid) {
                    const ruleElement = $('#rule-' + rule);
                    const icon = ruleElement.find('i');

                    if (isValid) {
                        ruleElement.removeClass('text-danger').addClass('text-success');
                        icon.removeClass('fa-times-circle').addClass('fa-check-circle');
                    } else {
                        ruleElement.removeClass('text-success').addClass('text-danger');
                        icon.removeClass('fa-check-circle').addClass('fa-times-circle');
                    }
                }

                $(document).ready(function() {
                    $('#passwordForm').submit(function(e) {
                        e.preventDefault();
                        const form = $(this);
                        const submitBtn = $('#submitBtn');

                        submitBtn.prop('disabled', true);
                        submitBtn.html('<i class="fas fa-spinner fa-spin mr-1"></i> Memproses...');
                        $('#loadingBarContainer').show(); // tampilkan progress bar

                        $.ajax({
                            url: form.attr('action'),
                            method: 'POST',
                            data: form.serialize(),
                            success: function(response) {
                                if (response.success) {
                                    Swal.fire({
                                        icon: 'success',
                                        title: 'Sukses',
                                        text: response.message,
                                        confirmButtonText: 'OK'
                                    }).then(() => {
                                        if (response.requires_logout) {
                                            window.location.href = response
                                                .redirect;
                                        } else if (response.redirect) {
                                            window.location.href = response
                                                .redirect;
                                        }
                                    });
                                }
                            },
                            error: function(xhr) {
                                let message = 'Terjadi kesalahan';
                                let errors = {};

                                if (xhr.status === 422) {
                                    message = xhr.responseJSON.message;
                                    errors = xhr.responseJSON.errors;

                                    $.each(errors, function(field, messages) {
                                        const input = $(`#${field}`);
                                        const errorField = $(`#${field}_error`);

                                        input.addClass('is-invalid');
                                        errorField.text(messages[0]).show();
                                    });
                                } else {
                                    message = xhr.responseJSON?.message || message;
                                    Swal.fire('Error', message, 'error');
                                }
                            },
                            complete: function() {
                                submitBtn.prop('disabled', false);
                                submitBtn.html(
                                    '<i class="fas fa-key mr-1"></i> Ubah Password');
                                $('#loadingBarContainer')
                                    .hide(); // sembunyikan progress bar
                            }
                        });
                    });
                });
            });
        </script>
    @endsection

</x-layouts.customers.app-layout>
