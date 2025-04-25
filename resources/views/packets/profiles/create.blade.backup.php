<x-layouts.app-layout title="Crate PPP Profile">
    <div class="card-header py-3">
        <h5 class="m-0 font-weight-bold text-primary"> {{ __('paket.profile.header.create') }}</h5>
    </div>

    <div class="row">
        <div class="col-lg-12">

            {!! Form::open(['route' => 'ppp-profiles.store', 'method' => 'POST']) !!}

            <div class="card-body col-lg-8">
                <div class="row mb-1">
                    <div class="col-12">
                        <label class="text-primary"><strong>{{ __('General') }}</strong></label>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12 mb-3">
                        <label for="profile_name"><strong>{{ __('paket.profile.label.profile_name') }}</strong></label>
                        {!! Form::text('profile_name', null, [
                            'placeholder' => __('paket.profile.placeholder.profile_name'),
                            'class' => 'form-control',
                        ]) !!}
                        @error('profile_name')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label
                            for="local_address"><strong>{{ __('paket.profile.label.local_address') }}</strong></label>
                        {!! Form::text('local_address', null, [
                            'placeholder' => __('paket.profile.placeholder.local_address'),
                            'class' => 'form-control',
                        ]) !!}
                        @error('local_address')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label
                            for="remote_address"><strong>{{ __('paket.profile.label.remote_address') }}</strong></label>
                        {!! Form::text('remote_address', null, [
                            'placeholder' => __('paket.profile.placeholder.remote_address'),
                            'class' => 'form-control',
                        ]) !!}
                        @error('remote_address')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12 mb-3">
                        <label for="dns_server"><strong>{{ __('paket.profile.label.dns_server') }}</strong></label>
                        {!! Form::text('dns_server', null, [
                            'placeholder' => __('paket.profile.placeholder.dns_server'),
                            'class' => 'form-control',
                        ]) !!}
                        @error('dns_server')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <div class="row mb-1">
                    <div class="col-12">
                        <label class="text-primary"><strong>{{ __('Limits') }}</strong></label>
                    </div>
                </div>

                <div class="row">
                    <div class="col-sm-6 col-md-3 mb-3">
                        <label for="max_limit"><strong>{{ __('paket.profile.label.max_limit') }}</strong></label>
                        <input type="text" name="max_upload" class="form-control"
                            placeholder="{{ __('paket.profile.placeholder.max_upload') }}">
                        @error('max_upload')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="col-sm-6 col-md-3 mb-3">
                        <label><strong>&nbsp;</strong></label>
                        <input type="text" name="max_download" class="form-control"
                            placeholder="{{ __('paket.profile.placeholder.max_download') }}">
                        @error('max_download')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="col-sm-6 col-md-3 mb-3">
                        <label for="rasio_cir"><strong>{{ __('paket.profile.label.cir') }}</strong></label>
                        <select name="rasio_cir" class="form-control">
                            @for ($i = 1; $i <= 8; $i++)
                                <option value="{{ $i }}">1:{{ $i }}</option>
                            @endfor
                        </select>
                    </div>
                    <div class="col-sm-6 col-md-3 mb-3">
                        <label for="priority"><strong>{{ __('paket.profile.label.priority') }}</strong></label>
                        <select name="priority" class="form-control">
                            @for ($i = 8; $i >= 1; $i--)
                                <option value="{{ $i }}">{{ $i }}</option>
                            @endfor
                        </select>
                    </div>
                </div>

                <div class="row mb-2">
                    <div class="col-12">
                        <span class="text-secondary">{{ __('paket.profile.label.format_max_limit') }}</span>
                    </div>
                </div>

                <div class="row mb-1">
                    <div class="col-12">
                        <label class="text-primary"><strong>{{ __('Queue') }}</strong></label>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="parent_queue"><strong>{{ __('paket.profile.label.parent_queue') }}</strong></label>
                        <select id="parent-queue-dropdown" name="parent_queue" class="form-control">
                            <option value="none">None</option>
                        </select>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label
                            for="insert_before"><strong>{{ __('paket.profile.label.insert_before') }}</strong></label>
                        <select id="insert-before-dropdown" name="insert_before" class="form-control">
                            <option value="default">Default</option>
                            <option value="bottom">Bottom</option>
                            <option value="first">First</option>
                        </select>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="queue_type"><strong>{{ __('paket.profile.label.queue_type') }}</strong></label>
                        <select id="queue-type-dropdown" name="queue_type" class="form-control">
                            <option value="none">None</option>
                        </select>
                    </div>
                </div>
                <div class="row mb-1 col-md-12">
                    <label class="text-primary"><strong>{{ __('Advanced Options') }}</strong></label>
                </div>

                <div class="form-group row">
                    <div class="col-sm-3">
                        <label for="bridge_learning"><strong>Bridge Learning</strong></label>
                        {!! Form::select('bridge_learning', ['default' => 'default', 'yes' => 'yes', 'no' => 'no'], 'default', [
                            'class' => 'form-control',
                        ]) !!}
                    </div>
                    <div class="col-sm-3">
                        <label for="use_ipv6"><strong>Use IPv6</strong></label>
                        {!! Form::select('use_ipv6', ['default' => 'default', 'yes' => 'yes', 'no' => 'no'], 'yes', [
                            'class' => 'form-control',
                        ]) !!}
                    </div>
                    <div class="col-sm-3">
                        <label for="use_mpls"><strong>Use MPLS</strong></label>
                        {!! Form::select('use_mpls', ['default' => 'default', 'yes' => 'yes', 'no' => 'no'], 'default', [
                            'class' => 'form-control',
                        ]) !!}
                    </div>
                    <div class="col-sm-3">
                        <label for="use_compression"><strong>Use Compression</strong></label>
                        {!! Form::select('use_compression', ['default' => 'default', 'yes' => 'yes', 'no' => 'no'], 'default', [
                            'class' => 'form-control',
                        ]) !!}
                    </div>
                </div>

                <div class="form-group row">
                    <div class="col-sm-3">
                        <label for="use_encryption"><strong>Use Encryption</strong></label>
                        {!! Form::select('use_encryption', ['default' => 'default', 'yes' => 'yes', 'no' => 'no'], 'yes', [
                            'class' => 'form-control',
                        ]) !!}
                    </div>
                    <div class="col-sm-3">
                        <label for="only_one"><strong>Only One</strong></label>
                        {!! Form::select('only_one', ['default' => 'default', 'yes' => 'yes', 'no' => 'no'], 'yes', [
                            'class' => 'form-control',
                        ]) !!}
                    </div>
                    <div class="col-sm-3">
                        <label for="change_tcp_mss"><strong>Change TCP MSS</strong></label>
                        {!! Form::select('change_tcp_mss', ['default' => 'default', 'yes' => 'yes', 'no' => 'no'], 'default', [
                            'class' => 'form-control',
                        ]) !!}
                    </div>
                    <div class="col-sm-3">
                        <label for="use_upnp"><strong>Use UPNP</strong></label>
                        {!! Form::select('use_upnp', ['default' => 'default', 'yes' => 'yes', 'no' => 'no'], 'default', [
                            'class' => 'form-control',
                        ]) !!}
                    </div>
                </div>

                <div class="form-group row">
                    <div class="col-sm-6">
                        <label for="on_up"><strong>Script On Up</strong></label>
                        {!! Form::text('on_up', ':log warning "PPPOE $user login kembali.";', ['class' => 'form-control']) !!}
                    </div>
                    <div class="col-sm-6">
                        <label for="on_down"><strong>Script On Down</strong></label>
                        {!! Form::text('on_down', ':local profile [/ppp secret get [find name=$user] profile];', [
                            'class' => 'form-control',
                        ]) !!}
                    </div>
                </div>

                <div class="form-group row">
                    <div class="col-sm-6">
                        <label for="comment"><strong>Comment</strong></label>
                        {!! Form::text('comment', null, ['class' => 'form-control']) !!}
                    </div>
                    <div class="col-sm-6">
                        <label for="rate_limit"><strong>Rate Limit</strong></label>
                        {!! Form::text('rate_limit', null, ['class' => 'form-control', 'readonly' => true, 'id' => 'rate-limit']) !!}
                    </div>
                </div>
            </div>
            <div class="card-footer">
                <button type="submit" class="btn btn-sm btn-success"><i class="fa fa-save"></i> &nbsp
                    {{ __('paket.profile.button.save') }}</button>
            </div>
            {!! Form::close() !!}
        </div>
    </div>

    @section('custom_scripts')
        <script type="text/javascript">
            $(document).ready(function() {
                /*==================================================
                    Server Dropdown Change Event
                ====================================================*/
                $('#server-dropdown').on('change', function() {
                    var idServer = this.value;
                    $("#parent-queue-dropdown").html('');
                    $("#queue-type-dropdown").html('');
                    $("#insert-before-dropdown").html('');


                    $.ajax({
                        url: "{{ url('api/fetch-parent-queues') }}",
                        type: "POST",
                        data: {
                            mikrotik_id: idServer,
                            _token: '{{ csrf_token() }}'
                        },
                        dataType: 'json',
                        success: function(result_server) {
                            $('#parent-queue-dropdown').html('<option value="none">None</option>');
                            $.each(result_server, function(key, value) {
                                $("#parent-queue-dropdown").append('<option value="' + value
                                    .name + '">' + value.name + '</option>');
                            });
                            $('#insert-before-dropdown').html(
                                '<option value="default">Default</option><option value="bottom">Bottom</option><option value="first">First</option>'
                            );
                            $.each(result_server, function(key, value) {
                                $("#insert-before-dropdown").append('<option value="' +
                                    value.name + '">' + value.name + '</option>');
                            });
                        }
                    });


                    setTimeout(() => {
                        $.ajax({
                            url: "{{ url('api/fetch-queue-types') }}",
                            type: "POST",
                            data: {
                                mikrotik_id: idServer,
                                _token: '{{ csrf_token() }}'
                            },
                            dataType: 'json',
                            success: function(result_server) {
                                $('#queue-type-dropdown').html(
                                    '<option value="none">None</option>');
                                $.each(result_server, function(key, value) {
                                    $("#queue-type-dropdown").append(
                                        '<option value="' + value.name + '">' +
                                        value.name + '</option>');
                                });
                            }
                        });
                    }, 250)
                });
            });

            function calculateRateLimit() {
                let uploadInput = $('input[name="max_upload"]').val().trim();
                let downloadInput = $('input[name="max_download"]').val().trim();
                let priority = $('select[name="priority"]').val();
                let rasioCir = parseFloat($('input[name="rasio_cir"]').val()) || 1;

                // Ekstrak angka dan unit
                let uploadValue = parseInt(uploadInput.replace(/[^0-9]/g, '')) || 0;
                let uploadUnit = uploadInput.replace(/[^a-zA-Z]/g, '').toLowerCase();

                let downloadValue = parseInt(downloadInput.replace(/[^0-9]/g, '')) || 0;
                let downloadUnit = downloadInput.replace(/[^a-zA-Z]/g, '').toLowerCase();

                // Normalisasi satuan ke kbps
                if (uploadUnit === 'm') {
                    uploadValue *= 1000;
                } else if (!uploadUnit || uploadUnit === '') {
                    uploadValue = uploadValue;
                } // assume already in k otherwise

                if (downloadUnit === 'm') {
                    downloadValue *= 1000;
                } else if (!downloadUnit || downloadUnit === '') {
                    downloadValue = downloadValue;
                }

                // Konversi ke 'xxxk'
                let maxUpload = `${uploadValue}k`;
                let maxDownload = `${downloadValue}k`;

                // Hitung limit-at
                let limitAtUpload = `${Math.floor(uploadValue / rasioCir)}k`;
                let limitAtDownload = `${Math.floor(downloadValue / rasioCir)}k`;

                if (uploadValue && downloadValue && priority) {
                    let rateLimit = `${maxUpload}/${maxDownload} 0/0 0/0 0/0 ${priority} ${limitAtUpload}/${limitAtDownload}`;
                    $('#rate-limit').val(rateLimit);
                }
            }

            $(document).on('input', 'input[name="max_upload"], input[name="max_download"], input[name="rasio_cir"]',
                calculateRateLimit);
            $(document).on('change', 'select[name="priority"]', calculateRateLimit);
        </script>
    @endsection
</x-layouts.app-layout>
