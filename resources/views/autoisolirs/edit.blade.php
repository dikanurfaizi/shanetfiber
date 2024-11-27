<x-layouts.cpanel.settings.app-layout title="{{ __('autoisolir.title.edit') }}">
    <div class="card-header py-3">
        <h5 class="m-0 font-weight-bold text-primary"> {{ __('autoisolir.header.edit') }}</h5>
    </div>

    <div class="row">
        <div class="col-lg-12">

            {!! Form::model($autoisolir, ['method' => 'PATCH','route' => ['autoisolir.update', $autoisolir->id]]) !!}

            <div class="card-body col-lg-8">

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
                        <label for="profile_isolir"><strong>{{ __('autoisolir.label.profile') }}</strong></label>
                        <div class="input-group">
                            {!! Form::select('profile_id', $collectionProfiles, null, array('placeholder' => __('autoisolir.placeholder.select-profile'),'class' => 'form-control')) !!}
                        </div>
                        @if ($errors->has('profile_id'))
                        <span class="text-danger">{{ $errors->first('profile_id') }}</span>
                        @endif
                    </div>
                </div>

                <div class="row align-items-center">
                    <div class="col-md-12 mb-3">
                        <label for="address_list_isolir"><strong>{{ __('autoisolir.label.address_list_isolir') }}</strong></label>
                        <div class="input-group">
                            {!! Form::text('address_list_isolir', null, array('placeholder' => __('autoisolir.placeholder.address_list_isolir'),'class' => 'form-control')) !!}
                            <div class="input-group-append">
                                <div class="input-group-text">
                                    <span class="fas fa-plus"></span>
                                </div>
                            </div>
                        </div>
                        @if ($errors->has('address_list_isolir'))
                        <span class="text-danger">{{ $errors->first('address_list_isolir') }}</span>
                        @endif
                    </div>
                </div>



                <div class="row align-items-center">
                    <div class="col-md-12 mb-3">
                        <label for="auto_isolir_option"><strong>{{ __('autoisolir.label.auto-isolir-option') }}</strong></label>
                        <div class="input-group">
                            <select id="auto-isolir-option" name="activation_date" class="form-control">
                                <option value="false" @selected($autoisolir->activation_date == 'false')>{{__('autoisolir.placeholder.select-auto-isolir-due-date')}}</option>
                                <option value="true" @selected($autoisolir->activation_date == 'true')>{{__('autoisolir.placeholder.select-auto-isolir-activation-date')}}</option>
                            </select>
                        </div>
                        @if ($errors->has('activation_date'))
                        <span class="text-danger">{{ $errors->first('activation_date') }}</span>
                        @endif
                    </div>
                </div>
                <div class="row align-items-center">
                    <div class="col-md-12 mb-3">
                        <label for="due_date"><strong>{{ __('autoisolir.label.due-date') }}</strong></label>
                        <div class="input-group mb-3">
                            {!! Form::text('due_date', null, array('placeholder' => __('autoisolir.placeholder.due-date'),'class' => 'form-control', 'id' => 'due_date', 'readonly')) !!}
                            <div class="input-group-append">
                                <div class="input-group-text">
                                    <span class="far fa-calendar-alt"></span>
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
                                    <span class="far fa-comment-alt"></span>
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
                                    <span class="fas fa-comment-alt"></span>
                                </div>
                            </div>
                        </div>
                        @if ($errors->has('comment_unpayment'))
                        <span class="text-danger">{{ $errors->first('comment_unpayment') }}</span>
                        @endif
                    </div>
                </div>
                
                <div class="card-footer">
                    <button type="submit" class="btn btn-success"><i class="fa fa-save"></i> &nbsp {{ __('autoisolir.button.save') }}</button>
                    <a class="btn btn-primary" href="{{ route('autoisolir.nat.setting',$autoisolir->id) }}"><i class="fa fa-edit"></i> &nbsp {{ __('autoisolir.button.edit-nat') }}</a>
                    <a class="btn btn-secondary" href="{{ route('autoisolir.index') }}"><i class="fa fa-arrow-left"></i> &nbsp {{ __('autoisolir.button.back') }}</a>
                </div>
            </div>
            {!! Form::close() !!}
        </div>
    </div>
    @section('custom_scripts')
    <script type="text/javascript">
        $(document).ready(function() {
            $('#auto-isolir-option').each(function() {
                var autoIsolirOption = this.value;
                if (autoIsolirOption == "false") {
                    // $('#due-date').removeClass('d-none');
                    $('#due_date').attr('readonly', false);
                } else {
                    $('#due_date').attr('readonly', true);
                    // $('#due-date').addClass('d-none');
                }
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