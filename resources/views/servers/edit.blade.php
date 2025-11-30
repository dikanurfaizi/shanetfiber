<x-layouts.cpanel.settings.app-layout title="Edit Server">

    <div class="card-header py-3">
        <h5 class="m-0 font-weight-bold text-primary">Edit Server</h5>
    </div>

    <div class="row">
        <div class="col-lg-12">
            {!! Form::model($server, ['method' => 'PATCH', 'route' => ['servers.update', $server->slug]]) !!}
            <div class="card-body col-lg-8">
                <div class="row align-items-center">
                    <div class="col-md-12 mb-3">
                        <label for="name"><strong>{{ __('Name:') }}</strong></label>
                        {!! Form::text('name', null, ['placeholder' => 'Name', 'class' => 'form-control', 'id' => 'name']) !!}
                        <span id="error_name" class="text-danger">
                            @if ($errors->has('name'))
                                {{ $errors->first('name') }}
                            @endif
                        </span>
                    </div>
                </div>
                <div class="row align-items-center">
                    <div class="col-md-12 mb-3">
                        <label for="host"><strong>{{ __('Host/IP Router:') }}</strong></label>
                        {!! Form::text('host', null, ['placeholder' => '192.168.88.1', 'class' => 'form-control', 'id' => 'host']) !!}
                        <span id="error_host" class="text-danger">
                            @if ($errors->has('host'))
                                {{ $errors->first('host') }}
                            @endif
                        </span>
                    </div>
                </div>
                <div class="row align-items-center">
                    <div class="col-md-12 mb-3">
                        <label for="username"><strong>{{ __('Username:') }}</strong></label>
                        {!! Form::text('username', null, ['placeholder' => 'admin', 'class' => 'form-control', 'id' => 'username']) !!}
                        <span id="error_username" class="text-danger">
                            @if ($errors->has('username'))
                                {{ $errors->first('username') }}
                            @endif
                        </span>
                    </div>
                </div>

                <div class="row align-items-center">
                    <div class="col-md-12 mb-3">
                        <label for="password"><strong>{{ __('Password:') }}</strong></label>
                        <input type="password" name="password" class="form-control" id="password"
                            placeholder="{{ __('Password') }}">
                        <span id="error_password" class="text-danger">
                            @if ($errors->has('password'))
                                {{ $errors->first('password') }}
                            @endif
                        </span>
                    </div>
                </div>

                <div class="row align-items-center">
                    <div class="col-md-12 mb-3">
                        <label for="port"><strong>{{ __('Port:') }}</strong></label>
                        {!! Form::text('port', null, ['placeholder' => 'Default (8728)', 'class' => 'form-control', 'id' => 'port']) !!}
                        <span id="error_port" class="text-danger">
                            @if ($errors->has('port'))
                                {{ $errors->first('port') }}
                            @endif
                        </span>
                    </div>
                </div>

                @if ($server->ros_version_id)
                    <div class="row align-items-center">
                        <div class="col-md-12 mb-3">
                            <label for="port"><strong>{{ __('Version:') }}</strong></label>
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

                <!-- End of Form -->
                <div class="row align-items-center">
                    <div class="col-md-12 mb-3">
                        <div class="d-flex justify-content-between">
                            <div class="form-switch">
                                <input class="form-check-input" type="checkbox" id="checkConnection">
                                <input type="hidden" id="is_connected" name="is_connected" value="0">
                                <label class="form-check-label" for="checkConnection">
                                    Test Connection
                                </label>
                            </div>
                            <div id="badge-connected" class="d-none"></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-footer">
                <button type="submit" class="btn btn-success d-none" id="button-save"><i class="fa fa-save"></i>
                    Submit</button>
                <a class="btn btn-primary" href="{{ route('servers.index') }}"><i class="fa fa-arrow-left"></i>
                    Back</a>
            </div>
            {!! Form::close() !!}
        </div>
    </div>

    @section('custom_scripts')
        <script>
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            $('#checkConnection').on('click', function(e) {
                if (e.currentTarget.checked) {
                    const name = $('#name').val();
                    const host = $('#host').val();
                    const username = $('#username').val();
                    const password = $('#password').val();
                    const port = $('#port').val();
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
                            if (response.status == 'success') {
                                $('#badge-connected').removeClass('d-none');
                                $('#button-save').removeClass('d-none');

                                $('#badge-connected').html(
                                    '<span class="badge text-white bg-success font-size-12 align-middle">Connected</span>'
                                );
                                $('#is_connected').val('1');
                            } else {
                                $('#badge-connected').removeClass('d-none');
                                $('#badge-connected').html(
                                    '<span class="badge bg-red text-white font-size-12 align-middle">Disconnected</span>'
                                );
                                $('#is_connected').val('0');
                            }
                            new Notify({
                                status: response.status,
                                title: response.title,
                                text: response.message,
                                effect: 'slide',
                                speed: 500,
                                showCloseButton: true,
                                autotimeout: 5000,
                                autoclose: true
                            });
                        },
                        error: function(data) {

                            if (data.responseJSON && data.responseJSON.errors) {

                                if (data.responseJSON.errors.host) {
                                    $('#host').addClass('is-invalid');
                                    $('#error_host').addClass('is-invalid').text(data.responseJSON.errors
                                        .host[0]);

                                }
                                if (data.responseJSON.errors.username) {
                                    $('#username').addClass('is-invalid');
                                    $('#error_username').addClass('is-invalid').text(data.responseJSON
                                        .errors.username[0]);
                                }
                                if (data.responseJSON.errors.password) {
                                    $('#password').addClass('is-invalid');
                                    $('#error_password').addClass('is-invalid').text(data.responseJSON
                                        .errors.password[0]);
                                }
                                if (data.responseJSON.errors.port) {
                                    $('#port').addClass('is-invalid');
                                    $('#error_port').addClass('is-invalid').text(data.responseJSON.errors
                                        .port[0]);
                                }
                            }

                            new Notify({
                                status: 'error',
                                title: 'Gagal terkoneksi!',
                                text: data.responseJSON && data.responseJSON.message ? data
                                    .responseJSON.message : 'Terjadi kesalahan',
                                effect: 'slide',
                                speed: 500,
                                showCloseButton: true,
                                autotimeout: 5000,
                                autoclose: true
                            });
                        }
                    });
                } else {
                    $('#badge-connected').addClass('d-none');
                    $('#is_connected').val('0');
                }
            });
        </script>
    @endsection
</x-layouts.cpanel.settings.app-layout>
