<x-layouts.app-layout title="Crate PPP Profile">
    <div class="card-header py-3">
        <h5 class="m-0 font-weight-bold text-primary"> {{ __('paket.profile.header.create') }}</h5>
    </div>

    <div class="row">
        <div class="col-lg-12">

            {!! Form::open(array('route' => 'ppp-profiles.store','method'=>'POST')) !!}

            <div class="card-body col-lg-8">
                <div class="row mb-1 col-md-12">
                    <label class="text-primary"><strong>{{ __('General') }}</strong></label>
                </div>
                <div class="row align-items-center">
                    <div class="col-md-8 mb-3">
                        <label for="server"><strong>{{ __('paket.profile.label.server') }}</strong></label>
                        {!! Form::select('mikrotik_id', $mikrotiks, null, array('placeholder' => 'Select Server','class' => 'form-control', 'id' => 'server-dropdown')) !!}
                        @if ($errors->has('mikrotik_id'))
                        <span class="text-danger">{{ $errors->first('mikrotik_id') }}</span>
                        @endif
                    </div>
                </div>
                <div class="row align-items-center">
                    <div class="col-md-8 mb-3">
                        <label for="profile_name"><strong>{{ __('paket.profile.label.profile_name') }}</strong></label>
                        <div class="input-group">
                            {!! Form::text('profile_name', null, array('placeholder' => __('paket.profile.placeholder.profile_name'),'class' => 'form-control')) !!}
                        </div>
                        @if ($errors->has('profile_name'))
                        <span class="text-danger">{{ $errors->first('profile_name') }}</span>
                        @endif
                    </div>
                </div>

                <div class="form-group row mb-0">
                    <div class="col-sm-4 mb-0 mb-sm-0">
                        <label for="local_address"><strong>{{ __('paket.profile.label.local_address') }}</strong></label>
                    </div>
                    <div class="col-sm-4">
                        <label for="remote_address"><strong>{{ __('paket.profile.label.remote_address') }}</strong></label>
                    </div>
                </div>

                <div class="form-group row mb-0">
                    <div class="col-sm-4 mb-0 mb-sm-0">
                        {!! Form::text('local_address', null, array('placeholder' => __('paket.profile.placeholder.local_address'),'class' => 'form-control')) !!}
                    </div>
                    <div class="col-sm-4">
                        {!! Form::text('remote_address', null, array('placeholder' => __('paket.profile.placeholder.remote_address'),'class' => 'form-control')) !!}
                    </div>
                </div>
                <div class="form-group row mb-2">
                    @error('local_address')
                    <div class="col-sm-4 mb-3 mb-sm-0">
                        <label class="text-danger">{{ $message }}</label>
                    </div>
                    @enderror
                    @error('remote_address')
                    <div class="col-sm-4">
                        <label class="text-danger">{{ $message }}</label>
                    </div>
                    @enderror
                </div>

                <div class="row align-items-center">
                    <div class="col-md-8 mb-3">
                        <label for="dns_server"><strong>{{ __('paket.profile.label.dns_server') }}</strong></label>
                        <div class="input-group">
                            {!! Form::text('dns_server', null, array('placeholder' => __('paket.profile.placeholder.dns_server'),'class' => 'form-control')) !!}
                        </div>
                        @if ($errors->has('dns_server'))
                        <span class="text-danger">{{ $errors->first('dns_server') }}</span>
                        @endif
                    </div>
                </div>


                <div class="row mb-1 col-md-12">
                    <label class="text-primary"><strong>{{ __('Limits') }}</strong></label>
                </div>
                <div class="form-group row mb-0">
                    <div class="col-sm-4 mb-0 mb-sm-0">
                        <label for="max_limit"><strong>{{ __('paket.profile.label.max_limit') }}</strong></label>
                    </div>
                    <div class="col-sm-2">
                        <label for="cir"><strong>{{ __('paket.profile.label.cir') }}</strong></label>
                    </div>
                    <div class="col-sm-2">
                        <label for="priority"><strong>{{ __('paket.profile.label.priority') }}</strong></label>
                    </div>
                </div>
                <div class="form-group mb-0 row">
                    <div class="col-sm-2 mb-1 mb-sm-0">
                        <input type="text" id="max-limit-upload" name="max_upload" class="form-control" placeholder="{{ __('paket.profile.placeholder.max_upload') }}"></input>
                    </div>
                    <div class="col-sm-2">
                        <input type="text" name="max_download" class="form-control" placeholder="{{ __('paket.profile.placeholder.max_download') }}"></input>
                    </div>
                    <div class="col-sm-2">
                        <select name="rasio_cir" class="form-control">
                            <option value="1">1:1</option>
                            <option value="2">1:2</option>
                            <option value="3">1:3</option>
                            <option value="4">1:4</option>
                            <option value="5">1:5</option>
                            <option value="6">1:6</option>
                            <option value="7">1:7</option>
                            <option value="8">1:8</option>
                        </select>
                    </div>

                    <div class="col-sm-2">
                        <select name="priority" class="form-control">
                            <option value="8">8</option>
                            <option value="7">7</option>
                            <option value="6">6</option>
                            <option value="5">5</option>
                            <option value="4">4</option>
                            <option value="3">3</option>
                            <option value="2">2</option>
                            <option value="1">1</option>
                        </select>
                    </div>
                </div>
                <div class="form-group row">
                    <div class="col-sm-8 mb-3 mb-sm-0">
                        <span class="text-secondary">{{ __('paket.profile.label.format_max_limit') }}</span>
                    </div>
                    <div class="col-sm-8 mb-3 mb-sm-0">
                        @error('max_upload')
                        <span class="text-danger">{{ $message }}</span>
                        @enderror
                        @error('max_download')
                        <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <div class="row mb-1 col-md-12">
                    <label class="text-primary"><strong>{{ __('Queue') }}</strong></label>
                </div>

                <div class="form-group row mb-0">
                    <div class="col-sm-3 mb-0 mb-sm-0">
                        <label for="parent_queue"><strong>{{ __('paket.profile.label.parent_queue') }}</strong></label>
                    </div>

                    <div class="col-sm-2">
                        <label for="insert_before"><strong>{{ __('paket.profile.label.insert_before') }}</strong></label>
                    </div>
                    <div class="col-sm-3">
                        <label for="mqueue_type"><strong>{{ __('paket.profile.label.queue_type') }}</strong></label>
                    </div>
                </div>
                <div class="form-group row">
                    <div class="col-sm-3">
                        <select id="parent-queue-dropdown" name="parent_queue" class="form-control">
                            <option value="none">None</option>
                        </select>
                    </div>
                    <div class="col-sm-2">
                        <select id="insert-before-dropdown" name="insert_before" class="form-control">
                            <option value="default">Default</option>
                            <option value="bottom">Bottom</option>
                            <option value="first">First</option>
                        </select>
                    </div>
                    <div class="col-sm-3">
                        <select id="queue-type-dropdown" name="queue_type" class="form-control">
                            <option value="none">None</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="card-footer">
                <button type="submit" class="btn btn-sm btn-success"><i class="fa fa-save"></i> &nbsp {{ __('paket.profile.button.save') }}</button>
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
                    url: "{{url('api/fetch-parent-queues')}}",
                    type: "POST",
                    data: {
                        mikrotik_id: idServer,
                        _token: '{{csrf_token()}}'
                    },
                    dataType: 'json',
                    success: function(result_server) {
                        $('#parent-queue-dropdown').html('<option value="none">None</option>');
                        $.each(result_server, function(key, value) {
                            $("#parent-queue-dropdown").append('<option value="' + value.name + '">' + value.name + '</option>');
                        });
                        $('#insert-before-dropdown').html('<option value="default">Default</option><option value="bottom">Bottom</option><option value="first">First</option>');
                        $.each(result_server, function(key, value) {
                            $("#insert-before-dropdown").append('<option value="' + value.name + '">' + value.name + '</option>');
                        });
                    }
                });


                setTimeout(() => {
                    $.ajax({
                        url: "{{url('api/fetch-queue-types')}}",
                        type: "POST",
                        data: {
                            mikrotik_id: idServer,
                            _token: '{{csrf_token()}}'
                        },
                        dataType: 'json',
                        success: function(result_server) {
                            $('#queue-type-dropdown').html('<option value="none">None</option>');
                            $.each(result_server, function(key, value) {
                                $("#queue-type-dropdown").append('<option value="' + value.name + '">' + value.name + '</option>');
                            });
                        }
                    });
                }, 250)
            });
        });
    </script>
    @endsection
</x-layouts.app-layout>