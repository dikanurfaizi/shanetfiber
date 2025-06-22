<x-layouts.cpanel.settings.app-layout title="{{ __('telegram.gateway.page-title-index') }}">

    <div class="card-header py-3">
        <h5 class="m-0 font-weight-bold text-primary">{{ __('telegram.gateway.page-title-index') }}</h5>
    </div>

    <div class="row mx-0">
        <div class="col-12 col-lg-8 px-0">
            <!-- Gateway Information Section -->
            <div class="mb-4 bg-white rounded p-4">
                <div class="row align-items-center mb-3">
                    <div class="col-5 col-md-3 font-weight-bold text-muted">
                        {!! trans('telegram.gateway.label.status') !!}
                    </div>
                    <div class="col-7 col-md-9">
                        <span class="status-gateway">
                            @if ($gateway->disabled)
                                <span class="badge bg-danger text-white">{{ trans('telegram.status.disable') }}</span>
                            @else
                                <span class="badge bg-success text-white">{{ trans('telegram.status.enable') }}</span>
                            @endif
                        </span>
                    </div>
                </div>

                <div class="row align-items-center mb-3">
                    <div class="col-5 col-md-3 font-weight-bold text-muted">
                        {!! trans('telegram.gateway.label.telegram-bot') !!}
                    </div>
                    <div class="col-7 col-md-9">
                        <span class="default-number">
                            @if ($gateway->name)
                                {{ $gateway->name }}
                            @else
                                <span class="text-muted">{{ trans('telegram.status.not-set') }}</span>
                            @endif
                        </span>
                    </div>
                </div>

                <div class="row align-items-center mb-3">
                    <div class="col-5 col-md-3 font-weight-bold text-muted">
                        {!! trans('telegram.gateway.label.description') !!}
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
                    <a class="btn btn-sm btn-success"
                        href="{{ route('telegramgateway.edit_description', $gateway->slug) }}"
                        title="{{ trans('telegram.gateway.tooltips.edit') }}" data-bs-toggle="tooltip">
                        <i class="fa fa-edit"></i>
                        {!! trans('telegram.button.edit') !!}
                    </a>
                </div>
            </div>

            <!-- Gateway Settings Section -->
            <div class="mb-4 bg-white rounded p-4">
                <h6 class="font-weight-bold mb-3 text-muted">
                    {!! trans('telegram.gateway.label.setting-gateway') !!}
                </h6>

                <div class="row align-items-center mb-3">
                    <div class="col-5 col-md-3 font-weight-bold text-muted">
                        {!! trans('telegram.gateway.label.token') !!}
                    </div>
                    <div class="col-7 col-md-9">
                        <span class="api-key text-break d-inline-block" style="max-width: 100%;">
                            @if ($gateway->token)
                                <code>{{ $gateway->token }}</code>
                            @else
                                <span class="text-muted">{{ trans('telegram.status.not-set') }}</span>
                            @endif
                        </span>
                    </div>
                </div>

                <div class="row align-items-center mb-3">
                    <div class="col-5 col-md-3 font-weight-bold text-muted">
                        {!! trans('telegram.gateway.label.url-callback') !!}
                    </div>
                    <div class="col-7 col-md-9">
                        <span class="url-callback text-break d-inline-block" style="max-width: 100%;">
                            @if ($gateway->url_callback)
                                <code>{{ $gateway->url_callback }}</code>
                            @else
                                <span class="text-muted">{{ trans('telegram.status.not-set') }}</span>
                            @endif
                        </span>
                    </div>
                </div>

                <div class="row align-items-center mb-3">
                    <div class="col-5 col-md-3 font-weight-bold text-muted">
                        {!! trans('telegram.gateway.label.cert-file') !!}
                    </div>
                    <div class="col-7 col-md-9">
                        <span class="cert-file">
                            @if ($gateway->cert_file)
                                <code>{{ $gateway->cert_file }}</code>
                            @else
                                <span class="text-muted">{{ trans('telegram.status.not-set') }}</span>
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
                        href="{{ route('telegramgateway.edit_gateway', $gateway->slug) }}"
                        title="{{ trans('telegram.gateway.tooltips.edit') }}" data-bs-toggle="tooltip">
                        <i class="fa fa-edit"></i>
                        {!! trans('telegram.button.edit') !!}
                    </a>
                </div>
            </div>

            <!-- Test Gateway Section -->
            <div class="mb-4 bg-white rounded p-4">
                <h6 class="font-weight-bold mb-3 text-muted">
                    {!! trans('telegram.gateway.label.send-bot-to-wa') !!}
                </h6>

                {!! Form::model($gateway, ['method' => 'PATCH', 'route' => ['telegramgateway.test_gateway', $gateway->slug]]) !!}
                <div class="row align-items-center mb-3">
                    <label for="test-number" class="col-5 col-md-3 col-form-label font-weight-bold text-muted">
                        {!! trans('telegram.gateway.label.test-number') !!}
                    </label>
                    <div class="col-7 col-md-6">
                        <input class="form-control form-control-sm" id="test-number" name="test_number" type="text"
                            placeholder="{{ trans('telegram.gateway.ph.test-number') }}"
                            value="{{ old('test_number') }}">
                        @if ($errors->has('test_number'))
                            <div class="text-danger small mt-1">{{ $errors->first('test_number') }}</div>
                        @endif
                    </div>
                </div>

                <div class="row mt-4">
                    <div class="col-12 offset-md-3 col-md-9">
                        <div class="d-flex flex-wrap gap-2">
                            <button type="submit" class="btn btn-sm btn-outline-success mr-1 mt-1" name="action"
                                value="send" title="{{ trans('telegram.gateway.tooltips.send') }}"
                                data-bs-toggle="tooltip">
                                <i class="fab fa-whatsapp me-1"></i>
                                {!! trans('telegram.gateway.button.send-link') !!}
                            </button>
                        </div>
                    </div>
                </div>
                {!! Form::close() !!}
            </div>
        </div>
    </div>

</x-layouts.cpanel.settings.app-layout>
