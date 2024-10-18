<x-layouts.cpanel.settings.app-layout title="{{ __('autoisolir.title.create') }}">
    <div class="card-header py-3">
        <h5 class="m-0 font-weight-bold text-primary"> {{ __('autoisolir.header.create') }}</h5>
    </div>

    <div class="row">
        <div class="col-lg-12">

            {!! Form::open(array('route' => 'autoisolir.store','method'=>'POST')) !!}

            <div class="card-body col-lg-8">

                <div class="row align-items-center">
                    <div class="col-md-12 mb-3">
                        <label for="server"><strong>{{ __('autoisolir.label.server') }}</strong></label>

                        {!! Form::select('mikrotik_id', $mikrotiks, null, array('placeholder' => __('autoisolir.placeholder.select-server'),'class' => 'form-control', 'id' => 'server-dropdown')) !!}

                        @if ($errors->has('mikrotik_id'))
                        <span class="text-danger">{{ $errors->first('mikrotik_id') }}</span>
                        @endif
                    </div>
                </div>

                <div class="row align-items-center">
                    <div class="col-md-12 mb-3">
                        <label for="profile_id"><strong>{{ __('autoisolir.label.profile') }}</strong></label>
                        <div class="input-group">
                            <select id="profile-isolir-dropdown" name="profile_id" class="form-control">
                                <option value="">{{__('autoisolir.placeholder.select-profile')}}</option>
                            </select>
                        </div>
                        @if ($errors->has('profile_id'))
                        <span class="text-danger">{{ $errors->first('profile_id') }}</span>
                        @endif
                    </div>
                </div>


                <div class="row align-items-center">
                    <div class="col-md-12 mb-3">
                        <label for="script"><strong>{{ __('autoisolir.label.name') }}</strong></label>
                        <div class="input-group">
                            {!! Form::text('name', null, array('placeholder' => __('autoisolir.placeholder.name'),'class' => 'form-control')) !!}

                        </div>
                        @if ($errors->has('name'))
                        <span class="text-danger">{{ $errors->first('name') }}</span>
                        @endif
                    </div>
                </div>

                <div class="row align-items-center">
                    <div class="col-md-12 mb-3">
                        <label for="auto_isolir_option"><strong>{{ __('autoisolir.label.auto-isolir-option') }}</strong></label>
                        <div class="input-group">
                            <select id="auto-isolir-option" name="activation_date" class="form-control">
                                <option value="">{{__('autoisolir.placeholder.select-auto-isolir-option')}}</option>
                                <option value="false">{{__('autoisolir.placeholder.select-auto-isolir-due-date')}}</option>
                                <option value="true">{{__('autoisolir.placeholder.select-auto-isolir-activation-date')}}</option>
                            </select>
                        </div>
                        @if ($errors->has('activation_date'))
                        <span class="text-danger">{{ $errors->first('activation_date') }}</span>
                        @endif
                    </div>
                </div>
                <div class="row align-items-center" id="due-date">
                    <div class="col-md-12 mb-3">
                        <label for="due_date"><strong>{{ __('autoisolir.label.due-date') }}</strong></label>
                        <div class="input-group mb-3">
                            {!! Form::text('due_date', null, array('placeholder' => __('autoisolir.placeholder.due-date'),'class' => 'form-control', 'id' => 'due_date', 'readonly')) !!}
                            <div class="input-group-append">
                                <div class="input-group-text">
                                    <span class="far fa-address-book"></span>
                                </div>
                            </div>
                        </div>
                        @if ($errors->has('due_date'))
                        <span class="text-danger">{{ $errors->first('due_date') }}</span>
                        @endif
                    </div>
                </div>
                <div class="row align-items-center">
                    <div class="col-md-12 mb-3">
                        <label for="comment_payment"><strong>{{ __('autoisolir.label.comment-payment') }}</strong></label>
                        <div class="input-group mb-3">
                            {!! Form::text('comment_payment', null, array('placeholder' => __('autoisolir.placeholder.comment-payment'),'class' => 'form-control')) !!}
                            <div class="input-group-append">
                                <div class="input-group-text">
                                    <span class="far fa-address-book"></span>
                                </div>
                            </div>
                        </div>
                        @if ($errors->has('comment_payment'))
                        <span class="text-danger">{{ $errors->first('comment_payment') }}</span>
                        @endif
                    </div>
                </div>
                <div class="row align-items-center">
                    <div class="col-md-12 mb-3">
                        <label for="comment_unpayment"><strong>{{ __('autoisolir.label.comment-unpayment') }}</strong></label>
                        <div class="input-group mb-3">
                            {!! Form::text('comment_unpayment', null, array('placeholder' => __('autoisolir.placeholder.comment-unpayment'),'class' => 'form-control')) !!}
                            <div class="input-group-append">
                                <div class="input-group-text">
                                    <span class="far fa-address-book"></span>
                                </div>
                            </div>
                        </div>
                        @if ($errors->has('comment_unpayment'))
                        <span class="text-danger">{{ $errors->first('comment_unpayment') }}</span>
                        @endif
                    </div>
                </div>

                <div class="row align-items-center">
                    <div class="col-md-12 mb-3">
                        <label for="ros_version_id"><strong>{{ __('autoisolir.label.select-version-ros') }}</strong></label>
                        <div class="input-group">
                            <select name="ros_version_id" class="form-control">
                                <option value="">{{__('autoisolir.placeholder.select-version-ros')}}</option>
                                <option value="1">{{__('autoisolir.placeholder.select-version-67')}}</option>
                                <option value="2">{{__('autoisolir.placeholder.select-version-710')}}</option>
                            </select>
                        </div>
                        @if ($errors->has('ros_version_id'))
                        <span class="text-danger">{{ $errors->first('ros_version_id') }}</span>
                        @endif
                    </div>
                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-success"><i class="fa fa-save"></i> &nbsp {{ __('autoisolir.button.save') }}</button>
                    <a class="btn btn-secondary" href="{{ route('autoisolir.index') }}"><i class="fa fa-arrow-left"></i> &nbsp {{ __('autoisolir.button.back') }}</a>
                </div>
            </div>
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
                $("#profile-isolir-dropdown").html('');
                $.ajax({

                    url: "{{url('api/fetch-profiles')}}",
                    type: "POST",
                    data: {
                        mikrotik_id: idServer,
                        _token: '{{csrf_token()}}'
                    },
                    dataType: 'json',
                    success: function(result_server) {
                        $('#profile-isolir-dropdown').html('<option value="">Select Profile</option>');
                        $.each(result_server, function(key, value) {
                            $("#profile-isolir-dropdown").append('<option value="' + value.name + '">' + value.name + '</option>');
                        });
                    }

                });


            });

            $('#auto-isolir-option').on('change', function() {
                var autoIsolirOption = this.value;
                if (autoIsolirOption == "false") {
                    // $('#due-date').removeClass('d-none');
                    $('#due_date').attr('readonly', false);
                } else {
                    $('#due_date').attr('readonly', true);
                    // $('#due-date').addClass('d-none');
                }
            });
        });
    </script>
    @endsection
</x-layouts.cpanel.settings.app-layout>