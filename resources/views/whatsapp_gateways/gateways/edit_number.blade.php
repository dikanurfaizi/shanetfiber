<x-layouts.cpanel.settings.app-layout title="{{ __('whatsapp.gateway.header-edit') }}">
    <div class="card-header py-3">
        <h5 class="m-0 font-weight-bold text-primary">
            {{ __('whatsapp.gateway.header-edit') }}</h5>
    </div>

    <div class="row">
        <div class="col-lg-12">
            {!! Form::model($data, ['method' => 'PATCH', 'route' => ['whatsappgateway.update_number', $data->slug]]) !!}
            <div class="card-body col-lg-8">

                <div class="form-group has-feedback row mb-2" id="switch">
                    <div class="col-md-4">
                        <label for="gateway">{!! trans('whatsapp.gateway.page-title-index') !!}</label>
                    </div>
                    <div class="col-md-5">
                        <div class="form-switch">
                            <input class="form-check-input" value="false" type="checkbox" id="switch-gateway">
                            <input type="hidden" id="gateway" name="gateway" value="false">
                            <label class="form-check-label status-gateway" for="toggle-switch">
                                {{ trans('whatsapp.status.disable') }}
                            </label>
                        </div>
                    </div>
                </div>

                <div class="form-group has-feedback row mb-2">
                    <div class="col-md-4">
                        <label for="country_code">{!! trans('whatsapp.gateway.label.country-code') !!}</label>
                    </div>
                    <div class="col-md-5">
                        {!! Form::text('country_code', null, [
                            'placeholder' => __('whatsapp.gateway.ph.country_code'),
                            'class' => 'form-control',
                        ]) !!}
                        @if ($errors->has('country_code'))
                            <span class="text-danger">{{ $errors->first('country_code') }}</span>
                        @endif
                    </div>
                </div>
                <div class="form-group has-feedback row mb-2">
                    <div class="col-md-4">
                        <label for="remaining_day">{!! trans('whatsapp.gateway.label.remaining-day') !!}</label>
                    </div>
                    <div class="col-md-5">
                        <div class="input-group">
                            <input class="form-control remaining_day" id="remaining_day" name="remaining_day"
                                type="number" min="0" max="10"
                                placeholder="{{ trans('whatsapp.gateway.ph.remaining_day') }}"
                                value="{{ $data->remaining_day }}">
                            <span class="input-group-text">
                                {!! trans('whatsapp.gateway.day') !!}
                            </span>
                        </div>
                        @if ($errors->has('remaining_day'))
                            <span class="text-danger">{{ $errors->first('remaining_day') }}</span>
                        @endif
                    </div>
                </div>
                <div class="form-group has-feedback row mb-2">
                    <div class="col-md-4">
                        <label for="schedule_time">{!! trans('whatsapp.gateway.label.schedule-time') !!}</label>
                    </div>
                    <div class="col-md-5">
                        <div class="input-group">
                            <span class="input-group-text">
                                {!! trans('whatsapp.gateway.schedule-every') !!}
                            </span>
                            <input class="form-control schedule_time" id="schedule_time" name="schedule_time"
                                type="number" min="0" max="23"
                                placeholder="{{ trans('whatsapp.gateway.ph.schedule_time') }}"
                                value="{{ $data->schedule_time }}">
                            <span class="input-group-text">
                                {!! trans('whatsapp.gateway.schedule-time') !!}
                            </span>
                        </div>
                        @if ($errors->has('schedule-time'))
                            <span class="text-danger">{{ $errors->first('schedule-time') }}</span>
                        @endif
                    </div>
                </div>
                <div class="form-group has-feedback row mb-2">
                    <div class="col-md-4">
                        <label for="default_number">{!! trans('whatsapp.gateway.label.default-number') !!}</label>
                    </div>
                    <div class="col-md-5">

                        <select id="default_number" name="default_number" class="form-control"
                            @if (!count($numbers)) disabled @endif>
                            @foreach ($numbers as $number)
                                <option value="{{ $number['id'] ?? null }}" @selected($number['id'] == $data->whatsap_number_id)>
                                    {{ $number['namenumber'] ?? trans('whatsapp.gateway.ph.no-number') }}
                                </option>
                            @endforeach
                        </select>
                        @if ($errors->has('default_number'))
                            <span class="text-danger">{{ $errors->first('default_number') }}</span>
                        @endif
                    </div>
                </div>
                <div class="form-group has-feedback row mb-2">
                    <div class="col-md-4">
                        <label for="description">{!! trans('whatsapp.gateway.label.description') !!}</label>
                    </div>
                    <div class="col-md-5">
                        {!! Form::text('description', null, [
                            'placeholder' => __('whatsapp.gateway.ph.description'),
                            'class' => 'form-control',
                        ]) !!}
                        @if ($errors->has('description'))
                            <span class="text-danger">{{ $errors->first('description') }}</span>
                        @endif
                    </div>
                </div>



            </div>
            <div class="card-footer">

                <a class="btn btn-sm btn-outline-danger" href="{{ route('whatsappgateway.index') }}"><i
                        class="fa fa-arrow-left"></i>&nbsp{{ __('server.button.cancel') }}</a>
                <button type="submit" class="btn btn-sm btn-outline-success pull-right btn-save" id="confirm">
                    <span class="button-text"><i class="fa fa-save"></i>
                        &nbsp{{ __('whatsapp.gateway.button.update') }}</span>
                </button>
            </div>
            {!! Form::close() !!}
        </div>
    </div>

    @section('custom_scripts')
        @if ($data->disabled)
            <script>
                $('#switch #switch-gateway').prop("checked", false);
                $('#switch #gateway').val(true);
                $('#switch .form-check-label').text(
                    "{{ trans('whatsapp.status.disable') }}");
            </script>
        @else
            <script>
                $('#switch #switch-gateway').prop("checked", true);
                $('#switch #gateway').val(false);
                $('#switch .form-check-label').text(
                    "{{ trans('whatsapp.status.enable') }}");
            </script>
        @endif
        <script>
            $('#switch #switch-gateway').on('click', function(e) {
                if (e.currentTarget.checked) {
                    $('#switch #gateway').val(false);
                    $('#switch .form-check-label').text(
                        "{{ trans('whatsapp.status.enable') }}");
                } else {

                    $('#switch #gateway').val(true);
                    $('#switch .form-check-label').text(
                        "{{ trans('whatsapp.status.disable') }}");
                }
            });
        </script>
    @endsection
</x-layouts.cpanel.settings.app-layout>
