<x-layouts.cpanel.settings.app-layout title="{{ __('autoisolir.title.edit-nat-proxy') }}">
    <div class="card-header py-3">
        <h5 class="m-0 fw-bold text-primary"> {{ __('autoisolir.header.edit-nat-proxy') }}</h5>
    </div>

    <div class="row">
        <div class="col-lg-12">
            {!! Form::model($autoisolir, [
                'method' => 'PATCH',
                'route' => ['autoisolir.updateNat.setting', $autoisolir->id],
            ]) !!}

            <div class="card-body col-lg-8">

                <div class="card-header py-3">
                    <h6 class="m-0 fw-bold text-primary"> {{ __('autoisolir.header.edit-nat') }}</h6>
                </div>
                <div class="row align-items-center">
                    <div class="col-md-12 mb-3">
                        <label for="server"><strong>{{ __('autoisolir.label.server') }}</strong></label>
                        <div></div><span class="badge bg-success text-white">
                            <i class="fas fa-server"></i> {{ $autoisolir->mikrotik->name }}
                        </span>
                    </div>
                </div>
                <div class="row align-items-center">
                    <div class="col-md-12 mb-3">
                        <label for="server"><strong>{{ __('autoisolir.label.name') }}</strong></label>
                        <div></div><span class="badge bg-success text-white">
                            <i class="fas fa-server"></i> {{ $autoisolir->name }}
                        </span>
                    </div>
                </div>
                <div class="row align-items-center">
                    <div class="col-md-12 mb-3">
                        <label for="server"><strong>{{ __('autoisolir.label.nat_src_address_list') }}</strong></label>
                        <div></div><span class="badge bg-success text-white">
                            <i class="fas fa-server"></i> {{ $autoisolir->nat_src_address_list }}
                        </span>
                    </div>
                </div>
                <div class="row align-items-center">
                    <div class="col-md-12 mb-3">
                        <label for="nat_dst_address"><strong>{{ __('autoisolir.label.nat_dst_address') }} [ !
                                ]</strong></label>
                        <div class="input-group">
                            {!! Form::text('nat_dst_address', null, [
                                'placeholder' => __('autoisolir.placeholder.nat_dst_address'),
                                'class' => 'form-control',
                            ]) !!}
                            <div class="input-group-append">
                                <div class="input-group-text">
                                    <span class="far fa-address-book"></span>
                                </div>
                            </div>
                        </div>
                        @if ($errors->has('nat_dst_address'))
                            <span class="text-danger">{{ $errors->first('nat_dst_address') }}</span>
                        @endif
                    </div>
                </div>


                <div class="row align-items-center">
                    <div class="col-md-12 mb-3">
                        <label
                            for="nat_dst_address_list"><strong>{{ __('autoisolir.label.nat_dst_address_list') }}</strong></label>
                        <div class="input-group">
                            {!! Form::text('nat_dst_address_list', null, [
                                'placeholder' => __('Insert address list'),
                                'class' => 'form-control',
                            ]) !!}
                        </div>
                        @if ($errors->has('nat_dst_address_list'))
                            <span class="text-danger">{{ $errors->first('nat_dst_address_list') }}</span>
                        @endif
                    </div>
                </div>

                <div class="card-header py-3">
                    <h6 class="m-0 fw-bold text-primary"> {{ __('autoisolir.header.edit-proxy') }}</h6>
                </div>
                <div class="row align-items-center">
                    <div class="col-md-12 mb-3">
                        <label
                            for="proxy_access_src_address"><strong>{{ __('autoisolir.label.proxy_access_src_address') }}</strong></label>
                        <div class="input-group">
                            {!! Form::text('proxy_access_src_address', null, [
                                'placeholder' => __('autoisolir.placeholder.proxy_access_src_address'),
                                'class' => 'form-control',
                            ]) !!}
                            <div class="input-group-append">
                                <div class="input-group-text">
                                    <span class="far fa-address-book"></span>
                                </div>
                            </div>
                        </div>
                        @if ($errors->has('proxy_access_src_address'))
                            <span class="text-danger">{{ $errors->first('proxy_access_src_address') }}</span>
                        @endif
                    </div>
                </div>
                <div class="row align-items-center">
                    <div class="col-md-12 mb-3">
                        <label
                            for="nat_dst_address_list"><strong>{{ __('autoisolir.label.proxy-access-action') }}</strong></label>
                        <div class="input-group">
                            <select id="auto-isolir-option" name="access_action" class="form-control">
                                <option value="">{{ __('autoisolir.placeholder.select-proxy-access-action') }}
                                </option>
                                @if ($autoisolir->mikrotik->ros_version_id === '1')
                                    <option value="deny" @selected($autoisolir->proxy_access_action == 'deny')>{{ __('Deny and redirect') }}
                                    </option>
                                @elseif ($autoisolir->mikrotik->ros_version_id === '2')
                                    <option value="deny" @selected($autoisolir->proxy_access_action == 'deny')>{{ __('Deny') }}</option>
                                    <option value="redirect" @selected($autoisolir->proxy_access_action == 'redirect')>{{ __('Redirect') }}</option>
                                @endif

                            </select>
                        </div>
                        @if ($errors->has('nat_dst_address_list'))
                            <span class="text-danger">{{ $errors->first('nat_dst_address_list') }}</span>
                        @endif
                    </div>
                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-success"><i class="fa fa-save"></i> &nbsp
                        {{ __('autoisolir.button.save') }}</button>
                    <a class="btn btn-secondary" href="{{ route('autoisolir.index') }}"><i
                            class="fa fa-arrow-left"></i> &nbsp {{ __('autoisolir.button.back') }}</a>
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
                        url: "{{ url('api/fetch-profiles') }}",
                        type: "POST",
                        data: {
                            mikrotik_id: idServer,
                            _token: '{{ csrf_token() }}'
                        },
                        dataType: 'json',
                        success: function(result_server) {
                            $('#profile-isolir-dropdown').html(
                                '<option value="">Select Profile</option>');
                            $.each(result_server, function(key, value) {
                                $("#profile-isolir-dropdown").append('<option value="' +
                                    value.name + '">' + value.name + '</option>');
                            });
                        }
                    });
                });

                //  $('#auto-isolir-option').on('change', function() {
                //      var autoIsolirOption = this.value;
                //      if (paymentMethode == "transfer") {
                //           $('#account-bank-dropdown').removeClass('d-none');
                //       } else {
                //           $('#account-bank-dropdown').addClass('d-none');
                //      }

                //   });
            });
        </script>
    @endsection
</x-layouts.cpanel.settings.app-layout>
