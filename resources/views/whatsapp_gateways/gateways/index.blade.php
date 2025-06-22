<x-layouts.cpanel.settings.app-layout title="{{ __('whatsapp.gateway.page-title-index') }}">

    <div class="card-header py-3">
        <h5 class="m-0 font-weight-bold text-primary">{{ __('whatsapp.gateway.page-title-index') }}</h5>
    </div>

    <div class="row mx-0">
        <div class="col-12 col-lg-8 px-0">
            <!-- Gateway Information Section -->
            <div class="mb-4 bg-white rounded p-4">
                <div class="row align-items-center mb-3">
                    <div class="col-5 col-md-3 font-weight-bold text-muted">
                        {!! trans('whatsapp.gateway.label.status') !!}
                    </div>
                    <div class="col-7 col-md-9">
                        <span class="status-gateway">
                            @if ($gateway->disabled)
                                <span class="badge bg-danger text-white">{{ trans('whatsapp.status.disable') }}</span>
                            @else
                                <span class="badge bg-success text-white">{{ trans('whatsapp.status.enable') }}</span>
                            @endif
                        </span>
                    </div>
                </div>

                <div class="row align-items-center mb-3">
                    <div class="col-5 col-md-3 font-weight-bold text-muted">
                        {!! trans('whatsapp.gateway.label.country-code') !!}
                    </div>
                    <div class="col-7 col-md-9">
                        <strong>+ <span class="country-code">
                                @if ($gateway->country_code)
                                    {{ $gateway->country_code }}
                                @else
                                    <span class="text-muted">{{ trans('whatsapp.status.not-set') }}</span>
                                @endif
                            </span></strong>
                    </div>
                </div>

                <div class="row align-items-center mb-3">
                    <div class="col-5 col-md-3 font-weight-bold text-muted">
                        {!! trans('whatsapp.gateway.label.remaining-day') !!}
                    </div>
                    <div class="col-7 col-md-9">
                        <span class="remaining-day">
                            {{ trans_choice('whatsapp.gateway.caption-remaining-day', $gateway->remaining_day, ['day' => $gateway->remaining_day]) }}
                        </span>
                        <span class="schedule-time text-muted">
                            {{ trans('whatsapp.gateway.at_hour', ['hour' => $gateway->schedule_time]) }}
                        </span>
                    </div>
                </div>

                <div class="row align-items-center mb-3">
                    <div class="col-5 col-md-3 font-weight-bold text-muted">
                        {!! trans('whatsapp.gateway.label.default-number') !!}
                    </div>
                    <div class="col-7 col-md-9">
                        <span class="default-number">
                            @if ($gateway->whatsapp_number_id)
                                {{ $gateway->whatsapp_number->name }} - {{ $gateway->whatsapp_number->number }}
                            @else
                                <span class="text-muted">{{ trans('whatsapp.status.not-set') }}</span>
                            @endif
                        </span>
                    </div>
                </div>

                <div class="row align-items-center mb-3">
                    <div class="col-5 col-md-3 font-weight-bold text-muted">
                        {!! trans('whatsapp.gateway.label.description') !!}
                    </div>
                    <div class="col-7 col-md-9">
                        <span class="description">
                            @if ($gateway->description)
                                {{ $gateway->description }}
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </span>
                    </div>
                </div>

                <div class="d-flex flex-wrap gap-2 mt-4">
                    <a class="btn btn-sm btn-success mr-1"
                        href="{{ route('whatsappgateway.edit_number', $gateway->slug) }}"
                        title="{{ trans('whatsapp.gateway.tooltips.edit') }}" data-bs-toggle="tooltip">
                        <i class="fa fa-edit"></i>
                        {!! trans('whatsapp.button.edit') !!}
                    </a>

                    <a class="btn btn-sm btn-outline-primary ms-auto" href="{{ route('trigger-job') }}"
                        title="{{ trans('whatsapp.gateway.label.manual-sending') }}" data-bs-toggle="tooltip">
                        <i class="fa fa-bullhorn"></i>
                        {!! trans('whatsapp.gateway.label.manual-sending') !!}
                    </a>
                </div>
            </div>

            <!-- Gateway Settings Section -->
            <div class="mb-4 bg-white rounded p-4">
                <h6 class="font-weight-bold mb-3 text-muted">
                    {!! trans('whatsapp.gateway.label.setting-gateway') !!}
                </h6>

                <div class="row align-items-center mb-3">
                    <div class="col-5 col-md-3 font-weight-bold text-muted">
                        {!! trans('whatsapp.gateway.label.url-callback') !!}
                    </div>
                    <div class="col-7 col-md-9">
                        <span class="url-callback text-break d-inline-block" style="max-width: 100%;">
                            @if ($gateway->url_callback)
                                <code>{{ $gateway->url_callback }}</code>
                            @else
                                <span class="text-muted">{{ trans('whatsapp.status.not-set') }}</span>
                            @endif
                        </span>
                    </div>
                </div>

                <div class="row align-items-center mb-3">
                    <div class="col-5 col-md-3 font-weight-bold text-muted">
                        {!! trans('whatsapp.gateway.label.api-key') !!}
                    </div>
                    <div class="col-7 col-md-9">
                        <span class="api-key text-break d-inline-block" style="max-width: 100%;">
                            @if ($gateway->api_key)
                                <code>{{ $gateway->api_key }}</code>
                            @else
                                <span class="text-muted">{{ trans('whatsapp.status.not-set') }}</span>
                            @endif
                        </span>
                    </div>
                </div>

                <div class="row align-items-center mb-3">
                    <div class="col-5 col-md-3 font-weight-bold text-muted">
                        {!! trans('whatsapp.gateway.label.cert-file') !!}
                    </div>
                    <div class="col-7 col-md-9">
                        <span class="cert-file">
                            @if ($gateway->cert_file)
                                <code>{{ $gateway->cert_file }}</code>
                            @else
                                <span class="text-muted">{{ trans('whatsapp.status.not-set') }}</span>
                            @endif
                        </span>
                    </div>
                </div>

                <div class="row align-items-center mb-3">
                    <div class="col-5 col-md-3 font-weight-bold text-muted">
                        {!! trans('whatsapp.gateway.label.auth-username') !!}
                    </div>
                    <div class="col-7 col-md-9">
                        <span class="auth-username">
                            @if ($gateway->basic_auth_username)
                                {{ $gateway->basic_auth_username }}
                            @else
                                <span class="text-muted">{{ trans('whatsapp.status.not-set') }}</span>
                            @endif
                        </span>
                    </div>
                </div>

                <div class="row align-items-center mb-3">
                    <div class="col-5 col-md-3 font-weight-bold text-muted">
                        {!! trans('whatsapp.gateway.label.auth-password') !!}
                    </div>
                    <div class="col-7 col-md-9">
                        <span class="auth-password">
                            @if ($gateway->basic_auth_password)
                                *****
                            @else
                                <span class="text-muted">{{ trans('whatsapp.status.not-set') }}</span>
                            @endif
                        </span>
                    </div>
                </div>

                <div class="d-flex flex-wrap gap-2 mt-4">
                    <a class="btn btn-sm btn-success"
                        href="{{ route('whatsappgateway.edit_gateway', $gateway->slug) }}"
                        title="{{ trans('whatsapp.gateway.tooltips.edit') }}" data-bs-toggle="tooltip">
                        <i class="fa fa-edit"></i>
                        {!! trans('whatsapp.button.edit') !!}
                    </a>
                </div>
            </div>

            <!-- Test Gateway Section -->
            <div class="mb-4 bg-white rounded p-4">
                <h6 class="font-weight-bold mb-3 text-muted">
                    {!! trans('whatsapp.gateway.label.test-gateway') !!}
                </h6>

                {!! Form::model($gateway, ['method' => 'PATCH', 'route' => ['whatsappgateway.test_gateway', $gateway->slug]]) !!}
                <div class="row align-items-center mb-3">
                    <label for="test-number" class="col-5 col-md-3 col-form-label font-weight-bold text-muted">
                        {!! trans('whatsapp.gateway.label.test-number') !!}
                    </label>
                    <div class="col-7 col-md-5">
                        <input class="form-control form-control-sm" id="test-number" name="test_number" type="text"
                            placeholder="{{ trans('whatsapp.gateway.ph.test-number') }}"
                            value="{{ old('test_number') }}">
                        @if ($errors->has('test_number'))
                            <div class="text-danger small mt-1">{{ $errors->first('test_number') }}</div>
                        @endif
                    </div>

                </div>

                {{-- <div class="d-flex justify-content-end mt-3">
                    <button type="submit" class="btn btn-sm btn-outline-success"
                        title="{{ trans('whatsapp.gateway.tooltips.send') }}" data-bs-toggle="tooltip">
                        <i class="fa fa-save"></i>
                        {!! trans('whatsapp.gateway.button.test') !!}
                    </button>
                </div> --}}
                <div class="d-flex flex-wrap gap-2 mt-4">
                    <button type="submit" class="btn btn-sm btn-outline-success"
                        title="{{ trans('whatsapp.gateway.tooltips.send') }}" data-bs-toggle="tooltip">
                        <i class="fab fa-whatsapp me-1"></i>
                        {!! trans('whatsapp.gateway.button.test') !!}
                    </button>
                </div>
                {!! Form::close() !!}
            </div>
        </div>
    </div>

</x-layouts.cpanel.settings.app-layout>
