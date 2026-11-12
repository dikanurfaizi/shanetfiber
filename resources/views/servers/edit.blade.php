<x-layouts.cpanel.settings.app-layout :title="__('server.title.edit')">

    <div class="card-header py-3">
        <h5 class="m-0 fw-bold text-primary">{{ __('server.header.edit') }}</h5>
    </div>

    <div class="row">
        <div class="col-lg-12">
            {!! Form::model($server, ['method' => 'PATCH', 'route' => ['servers.update', $server->slug]]) !!}
            <div class="card-body col-lg-8">

                {{-- Nama Server --}}
                <div class="row align-items-center">
                    <div class="col-md-12 mb-3">
                        <label for="name"><strong>{{ __('server.label.name') }}</strong></label>
                        {!! Form::text('name', null, [
                            'placeholder' => __('server.placeholder.name'),
                            'class' => 'form-control',
                            'id' => 'name',
                        ]) !!}
                        <span id="error_name" class="text-danger">
                            @if ($errors->has('name'))
                                {{ $errors->first('name') }}
                            @endif
                        </span>
                    </div>
                </div>

                {{-- Host / IP Router --}}
                <div class="row align-items-center">
                    <div class="col-md-12 mb-3">
                        <label for="host"><strong>{{ __('server.label.host') }}</strong></label>
                        {!! Form::text('host', null, [
                            'placeholder' => __('server.placeholder.host'),
                            'class' => 'form-control',
                            'id' => 'host',
                        ]) !!}
                        <span id="error_host" class="text-danger">
                            @if ($errors->has('host'))
                                {{ $errors->first('host') }}
                            @endif
                        </span>
                    </div>
                </div>

                {{-- Username API --}}
                <div class="row align-items-center">
                    <div class="col-md-12 mb-3">
                        <label for="username"><strong>{{ __('server.label.username') }}</strong></label>
                        {!! Form::text('username', null, [
                            'placeholder' => __('server.placeholder.username'),
                            'class' => 'form-control',
                            'id' => 'username',
                        ]) !!}
                        <span id="error_username" class="text-danger">
                            @if ($errors->has('username'))
                                {{ $errors->first('username') }}
                            @endif
                        </span>
                    </div>
                </div>

                {{-- Password API --}}
                <div class="row align-items-center">
                    <div class="col-md-12 mb-3">
                        <label for="password"><strong>{{ __('server.label.password') }}</strong></label>
                        <div class="input-group">
                            <input type="password" name="password"
                                class="form-control @error('password') is-invalid @enderror" id="password"
                                value="{{ old('password', $server->password) }}"
                                placeholder="{{ __('server.placeholder.password') }}">
                            <div class="input-group-append">
                                <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                    <i class="fas fa-eye" id="toggleIcon"></i>
                                </button>
                            </div>
                        </div>
                        <span id="error_password" class="text-danger">
                            @if ($errors->has('password'))
                                {{ $errors->first('password') }}
                            @endif
                        </span>
                    </div>
                </div>

                {{-- Port API --}}
                <div class="row align-items-center">
                    <div class="col-md-12 mb-3">
                        <label for="port"><strong>{{ __('server.label.port') }}</strong></label>
                        {!! Form::text('port', null, [
                            'placeholder' => __('server.placeholder.port'),
                            'class' => 'form-control',
                            'id' => 'port',
                        ]) !!}
                        <span id="error_port" class="text-danger">
                            @if ($errors->has('port'))
                                {{ $errors->first('port') }}
                            @endif
                        </span>
                    </div>
                </div>

                {{-- Versi RouterOS --}}
                @if ($server->ros_version_id)
                    <div class="row align-items-center">
                        <div class="col-md-12 mb-3">
                            <label for="ros_version_id"><strong>{{ __('server.label.ros_version') }}</strong></label>
                            @if ($server->ros_version_id == 1)
                                {{ __('autoisolir.placeholder.select-version-67') }}
                            @elseif ($server->ros_version_id == 2)
                                {{ __('autoisolir.placeholder.select-version-710') }}
                            @endif
                        </div>
                    </div>
                @else
                    <div class="row align-items-center">
                        <div class="col-md-12 mb-3">
                            <label
                                for="ros_version_id"><strong>{{ __('autoisolir.label.select-version-ros') }}</strong></label>
                            <div class="input-group">
                                <select name="ros_version_id" class="form-control">
                                    <option value="">{{ __('autoisolir.placeholder.select-version-ros') }}
                                    </option>
                                    <option value="1">{{ __('autoisolir.placeholder.select-version-67') }}
                                    </option>
                                    <option value="2">{{ __('autoisolir.placeholder.select-version-710') }}
                                    </option>
                                </select>
                            </div>
                            @if ($errors->has('ros_version_id'))
                                <span class="text-danger">{{ $errors->first('ros_version_id') }}</span>
                            @endif
                        </div>
                    </div>
                @endif

                {{-- Tombol Uji Koneksi API MikroTik --}}
                <div class="row align-items-center mb-3">
                    <div class="col-md-12">
                        <button type="button" id="btn-test-api" class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-plug me-1"></i> {{ __('server.label.test_connection') }}
                        </button>
                        <span id="api-test-spinner" class="spinner-border spinner-border-sm text-primary ms-2 d-none"
                            role="status"></span>
                        <input type="hidden" id="is_connected" name="is_connected"
                            value="{{ $server->is_connected ?? 0 }}">
                        <span id="badge-connected" class="ms-2">
                            @if ($server->is_connected)
                                <span
                                    class="badge text-white bg-success font-size-12 align-middle">{{ __('server.label.connected') }}</span>
                            @else
                                <span
                                    class="badge bg-red text-white font-size-12 align-middle">{{ __('server.label.disconnected') }}</span>
                            @endif
                        </span>
                    </div>
                </div>

                {{-- ==================== FREERADIUS & COA SETTINGS ==================== --}}
                <hr class="my-4">
                <h6 class="fw-bold text-secondary mb-3">
                    <i class="fas fa-network-wired me-1"></i> {{ __('server.header.radius_section') }}
                </h6>

                {{-- RADIUS Shared Secret --}}
                <div class="row align-items-center">
                    <div class="col-md-12 mb-3">
                        <label for="radius_secret"><strong>{{ __('server.label.radius_secret') }}</strong></label>
                        <div class="input-group">
                            <input type="password" name="radius_secret"
                                class="form-control @error('radius_secret') is-invalid @enderror" id="radius_secret"
                                value="{{ old('radius_secret', $server->radius_secret) }}"
                                placeholder="{{ __('server.placeholder.radius_secret') }}">
                            <div class="input-group-append">
                                <button class="btn btn-outline-secondary" type="button" id="toggleRadiusSecret">
                                    <i class="fas fa-eye" id="toggleRadiusIcon"></i>
                                </button>
                            </div>
                        </div>
                        <small class="form-text text-muted">{{ __('server.help.radius_secret') }}</small>
                        <span id="error_radius_secret" class="text-danger">
                            @if ($errors->has('radius_secret'))
                                {{ $errors->first('radius_secret') }}
                            @endif
                        </span>
                    </div>
                </div>

                {{-- RADIUS CoA/PoD Port --}}
                <div class="row align-items-center">
                    <div class="col-md-12 mb-3">
                        <label for="coa_port"><strong>{{ __('server.label.coa_port') }}</strong></label>
                        {!! Form::number('coa_port', null, [
                            'placeholder' => __('server.placeholder.coa_port'),
                            'class' => 'form-control',
                            'id' => 'coa_port',
                        ]) !!}
                        <small class="form-text text-muted">{{ __('server.help.coa_port') }}</small>
                        <span id="error_coa_port" class="text-danger">
                            @if ($errors->has('coa_port'))
                                {{ $errors->first('coa_port') }}
                            @endif
                        </span>
                    </div>
                </div>

                {{-- Tombol AJAX Test RADIUS & CoA --}}
                <div class="row align-items-center mb-3">
                    <div class="col-md-12">
                        <button type="button" id="btn-test-radius" class="btn btn-outline-info btn-sm">
                            <i class="fas fa-bolt me-1"></i> {{ __('server.button.test_radius') }}
                        </button>
                        <span id="radius-test-spinner" class="spinner-border spinner-border-sm text-info ms-2 d-none"
                            role="status"></span>
                        <div id="radius-test-result" class="mt-2 d-none"></div>
                    </div>
                </div>
                {{-- ==================== END FREERADIUS SETTINGS ==================== --}}
            </div>

            <div class="card-footer">
                <a class="btn btn-primary" href="{{ route('servers.index') }}">
                    <i class="fa fa-arrow-left me-1"></i> {{ __('server.button.back') }}
                </a>
                <button type="submit" class="btn btn-success" id="button-save">
                    <i class="fa fa-save me-1"></i> {{ __('server.button.update') }}
                </button>
            </div>
            {!! Form::close() !!}
        </div>
    </div>

    @section('custom_scripts')
        <script>
            // Toggle Show / Hide Password
            $('#togglePassword').on('click', function() {
                const passwordInput = $('#password');
                const icon = $('#toggleIcon');

                // Cek tipe attribute input saat ini
                const type = passwordInput.attr('type') === 'password' ? 'text' : 'password';
                passwordInput.attr('type', type);

                // Toggle icon (fa-eye vs fa-eye-slash)
                icon.toggleClass('fa-eye fa-eye-slash');
            });

            // Toggle Show / Hide RADIUS Secret
            $('#toggleRadiusSecret').on('click', function(e) {
                e.preventDefault();
                const radiusInput = $('#radius_secret');
                const icon = $('#toggleRadiusIcon');

                // Ubah tipe input antara 'password' dan 'text'
                const isPassword = radiusInput.attr('type') === 'password';
                radiusInput.attr('type', isPassword ? 'text' : 'password');

                // Toggle icon (fa-eye vs fa-eye-slash)
                icon.toggleClass('fa-eye fa-eye-slash');
            });

            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            // 1. Handler Tombol Uji Koneksi API MikroTik
            $('#btn-test-api').on('click', function(e) {
                e.preventDefault();

                const btn = $(this);
                const spinner = $('#api-test-spinner');
                const badge = $('#badge-connected');

                const name = $('#name').val();
                const host = $('#host').val();
                const username = $('#username').val();
                const password = $('#password').val();
                const port = $('#port').val();

                // Bersihkan state error sebelumnya
                $('.is-invalid').removeClass('is-invalid');
                $('.text-danger').empty();

                btn.prop('disabled', true);
                spinner.removeClass('d-none');

                $.ajax({
                    url: "{{ route('test-con') }}",
                    method: "POST",
                    cache: false,
                    data: {
                        'name': name,
                        'host': host,
                        'password': password,
                        'username': username,
                        'port': port
                    },
                    success: function(response) {
                        btn.prop('disabled', false);
                        spinner.addClass('d-none');

                        if (response.status == 'success') {
                            badge.html(
                                '<span class="badge text-white bg-success font-size-12 align-middle">{{ __('server.label.connected') }}</span>'
                            );
                            $('#is_connected').val('1');
                        } else {
                            badge.html(
                                '<span class="badge bg-red text-white font-size-12 align-middle">{{ __('server.label.disconnected') }}</span>'
                            );
                            $('#is_connected').val('0');
                        }

                        new Notify({
                            status: response.status,
                            title: response.title || 'API Test',
                            text: response.message,
                            effect: 'slide',
                            speed: 500,
                            showCloseButton: true,
                            autotimeout: 5000,
                            autoclose: true
                        });
                    },
                    error: function(data) {
                        btn.prop('disabled', false);
                        spinner.addClass('d-none');

                        if (data.responseJSON && data.responseJSON.errors) {
                            if (data.responseJSON.errors.host) {
                                $('#host').addClass('is-invalid');
                                $('#error_host').text(data.responseJSON.errors.host[0]);
                            }
                            if (data.responseJSON.errors.username) {
                                $('#username').addClass('is-invalid');
                                $('#error_username').text(data.responseJSON.errors.username[0]);
                            }
                            if (data.responseJSON.errors.password) {
                                $('#password').addClass('is-invalid');
                                $('#error_password').text(data.responseJSON.errors.password[0]);
                            }
                            if (data.responseJSON.errors.port) {
                                $('#port').addClass('is-invalid');
                                $('#error_port').text(data.responseJSON.errors.port[0]);
                            }
                        }

                        badge.html(
                            '<span class="badge bg-red text-white font-size-12 align-middle">{{ __('server.label.disconnected') }}</span>'
                        );
                        $('#is_connected').val('0');

                        new Notify({
                            status: 'error',
                            title: '{{ __('server.label.disconnected') }}',
                            text: data.responseJSON && data.responseJSON.message ? data.responseJSON
                                .message : 'Terjadi kesalahan saat menguji API',
                            effect: 'slide',
                            speed: 500,
                            showCloseButton: true,
                            autotimeout: 5000,
                            autoclose: true
                        });
                    }
                });
            });

            // 2. Handler Tombol Test RADIUS & CoA Connection
            $('#btn-test-radius').on('click', function(e) {
                e.preventDefault();

                const btn = $(this);
                const spinner = $('#radius-test-spinner');
                const resultDiv = $('#radius-test-result');

                const host = $('#host').val();
                const radiusSecret = $('#radius_secret').val();
                const coaPort = $('#coa_port').val();

                if (!host) {
                    Swal.fire({
                        icon: 'warning',
                        title: '{{ __('server.import.error_title') }}',
                        text: '{{ __('validation.required', ['attribute' => __('server.label.host')]) }}'
                    });
                    return;
                }

                btn.prop('disabled', true);
                spinner.removeClass('d-none');
                resultDiv.addClass('d-none').removeClass('alert alert-success alert-danger');

                $.ajax({
                    url: "{{ route('servers.test-radius', $server->slug) }}",
                    method: "POST",
                    data: {
                        'host': host,
                        'radius_secret': radiusSecret,
                        'coa_port': coaPort
                    },
                    success: function(response) {
                        btn.prop('disabled', false);
                        spinner.addClass('d-none');

                        if (response.success) {
                            resultDiv.removeClass('d-none')
                                .addClass('alert alert-success')
                                .html('<i class="fas fa-check-circle me-1"></i> ' + response.message);

                            Swal.fire({
                                icon: 'success',
                                title: '{{ __('server.import.success_title') }}',
                                text: response.message
                            });
                        } else {
                            resultDiv.removeClass('d-none')
                                .addClass('alert alert-danger')
                                .html('<i class="fas fa-times-circle me-1"></i> ' + response.message);

                            Swal.fire({
                                icon: 'error',
                                title: '{{ __('server.import.error_title') }}',
                                text: response.message
                            });
                        }
                    },
                    error: function(xhr) {
                        btn.prop('disabled', false);
                        spinner.addClass('d-none');

                        const errMsg = xhr.responseJSON && xhr.responseJSON.message ?
                            xhr.responseJSON.message :
                            '{{ __('server.import.error_text') }}';

                        resultDiv.removeClass('d-none')
                            .addClass('alert alert-danger')
                            .html('<i class="fas fa-times-circle me-1"></i> ' + errMsg);

                        Swal.fire({
                            icon: 'error',
                            title: '{{ __('server.import.error_title') }}',
                            text: errMsg
                        });
                    }
                });
            });
        </script>
    @endsection
</x-layouts.cpanel.settings.app-layout>
