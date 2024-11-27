<x-layouts.cpanel.settings.app-layout title="{{ __('whatsapp.gateway.page-title-index') }}">

    <div class="card-header py-3">
        <h5 class="m-0 font-weight-bold text-primary">{{ __('whatsapp.gateway.page-title-index') }} </h5>

    </div>

    <div class="row">
        <div class="col-lg-12">

            <div class="card-body col-lg-8">
                <div class="form-group row mb-2">
                    <div class="col-md-3">
                        <label for="country_code">{!! trans('whatsapp.gateway.label.status') !!}</label>
                    </div>
                    <div class="col-md-8">
                        : <span class="status-gateway">
                            @if ($gateway->disabled)
                                {{ trans('whatsapp.status.disable') }}
                            @else
                                {{ trans('whatsapp.status.enable') }}
                            @endif
                        </span>
                    </div>
                </div>
                <div class="form-group row mb-2">
                    <div class="col-md-3">
                        <label for="country_code">{!! trans('whatsapp.gateway.label.country-code') !!}</label>
                    </div>
                    <div class="col-md-8">
                        : <strong>+ <span class="country-code">
                                @if ($gateway->country_code)
                                    {{ $gateway->country_code }}
                                @else
                                    {{ trans('whatsapp.status.not-set') }}
                                @endif
                            </span></strong>
                    </div>
                </div>
                <div class="form-group row mb-2">
                    <div class="col-md-3">
                        <label for="remaining_day">{!! trans('whatsapp.gateway.label.remaining-day') !!}</label>
                    </div>
                    <div class="col-md-8">
                        :
                        <span
                            class="remaining-day">{{ trans_choice('whatsapp.gateway.caption-remaining-day', $gateway->remaining_day, ['day' => $gateway->remaining_day]) }}</span>
                        <span class="schedule-time">
                            {{ trans('whatsapp.gateway.at_hour', ['hour' => $gateway->schedule_time]) }}</span>
                    </div>
                </div>
                <div class="form-group row mb-2">
                    <div class="col-md-3">
                        <label for="default_number">{!! trans('whatsapp.gateway.label.default-number') !!}</label>
                    </div>
                    <div class="col-md-8">
                        : <span class="default-number">
                            @if ($gateway->whatsapp_number_id)
                                {{ $gateway->whatsapp_number->name }} - {{ $gateway->whatsapp_number->number }}
                            @else
                                {{ trans('whatsapp.status.not-set') }}
                            @endif
                        </span>
                    </div>
                </div>
                <div class="form-group row mb-2">
                    <div class="col-md-3">
                        <label for="description">{!! trans('whatsapp.gateway.label.description') !!}</label>
                    </div>
                    <div class="col-md-8">
                        : <span class="description">
                            @if ($gateway->description)
                                {{ $gateway->description }}
                            @endif
                        </span>
                    </div>
                </div>
                <span title="{{ trans('whatsapp.gateway.tooltips.edit') }}" data-bs-toggle="tooltip"
                    data-bs-placement="top">

                    <a class="btn btn-sm btn-success"
                        href="{{ route('whatsappgateway.edit_number', $gateway->slug) }}">
                        <i class="fa fa-edit"></i>
                        {!! trans('whatsapp.button.edit') !!}</a>
                </span>
                <span title="{{ trans('whatsapp.gateway.label.manual-sending') }}" data-bs-toggle="tooltip"
                    data-bs-placement="top">

                    <a class="btn btn-sm btn-outline-primary pull-right" href="{{ route('trigger-job') }}">
                        <i class="fa fa-bullhorn"></i>
                        {!! trans('whatsapp.gateway.label.manual-sending') !!}</a>
                </span>
            </div>



            <div class="card-header">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    {!! trans('whatsapp.gateway.label.setting-gateway') !!}
                </div>
            </div>

            <div class="card-body col-lg-8" id="config-whatsaap-server">
                <div class="form-group row mb-2">
                    <div class="col-md-3">
                        <label for="url_callback">{!! trans('whatsapp.gateway.label.url-callback') !!}</label>
                    </div>
                    <div class="col-md-8">
                        : <span class="url-callback">
                            @if ($gateway->url_callback)
                                {{ $gateway->url_callback }}
                            @else
                                {{ trans('whatsapp.status.not-set') }}
                            @endif
                        </span>
                    </div>
                </div>
                <div class="form-group row mb-2">
                    <div class="col-md-3">
                        <label for="api_key">{!! trans('whatsapp.gateway.label.api-key') !!}</label>
                    </div>
                    <div class="col-md-8">
                        : <span class="api-key">
                            @if ($gateway->api_key)
                                {{ $gateway->api_key }}
                            @else
                                {{ trans('whatsapp.status.not-set') }}
                            @endif
                        </span>
                    </div>
                </div>
                <div class="form-group row mb-2">
                    <div class="col-md-3">
                        <label for="cert_file">{!! trans('whatsapp.gateway.label.cert-file') !!}</label>
                    </div>
                    <div class="col-md-8">
                        : <span class="cert-file">
                            @if ($gateway->cert_file)
                                {{ $gateway->cert_file }}
                            @else
                                {{ trans('whatsapp.status.not-set') }}
                            @endif
                        </span>
                    </div>
                </div>
                <span title="{{ trans('whatsapp.gateway.tooltips.edit') }}" data-bs-toggle="tooltip"
                    data-bs-placement="top">
                    <a class="btn btn-sm btn-success"
                        href="{{ route('whatsappgateway.edit_gateway', $gateway->slug) }}">
                        <i class="fa fa-edit"></i>
                        {!! trans('whatsapp.button.edit') !!}</a>
                </span>
            </div>


            <div class="card-header">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <strong>{!! trans('whatsapp.gateway.label.test-gateway') !!}</strong>
                </div>
            </div>
            {!! Form::model($gateway, ['method' => 'PATCH', 'route' => ['whatsappgateway.test_gateway', $gateway->slug]]) !!}
            <div class="card-body col-lg-8" id="config-whatsaap-server">
                <div class="form-group has-feedback row mb-2">
                    <div class="col-md-4">
                        <label for="test-number">{!! trans('whatsapp.gateway.label.test-number') !!}</label>
                    </div>
                    <div class="col-md-5">
                        <input class="form-control" id="test-number" name="test_number" type="text"
                            placeholder="{{ trans('whatsapp.gateway.ph.test-number') }}"
                            value="{{ old('test_number') }}">
                        @if ($errors->has('test_number'))
                            <span class="text-danger">{{ $errors->first('test_number') }}</span>
                        @endif
                    </div>
                </div>

                <span title="{{ trans('whatsapp.gateway.tooltips.send') }}" data-bs-toggle="tooltip"
                    data-bs-placement="top">

                    <button type="submit" class="btn btn-sm btn-outline-success pull-right btn-save" id="confirm">
                        <span class="button-text"><i class="fa fa-save"></i>
                            &nbsp{!! trans('whatsapp.gateway.button.test') !!}</span>
                    </button>

                </span>

            </div>

            <div class="card-footer mt-2">

            </div>
        </div>

</x-layouts.cpanel.settings.app-layout>
