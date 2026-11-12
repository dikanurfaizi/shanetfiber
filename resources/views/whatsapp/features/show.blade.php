<x-layouts.cpanel.settings.app-layout title="Feature Detail">

    {{-- HEADER --}}
    <div class="card-header py-3">
        <h5 class="m-0 fw-bold text-primary">
            {{ __('whatsapp.feature.title.show-feature') }}
        </h5>

        <div class="mt-1 text-muted" style="font-size: .95rem;">
            {{ __('whatsapp.feature.description.detail-information') }}
        </div>
    </div>

    {{-- BODY --}}
    <div class="card-body">
        <div class="row">

            {{-- LEFT : FEATURE INFORMATION --}}
            <div class="col-md-6 mb-4">

                <div class="card shadow-sm h-100">
                    <div class="card-header">
                        {{ __('whatsapp.feature.label.feature-information') }}
                    </div>

                    <div class="card-body">

                        {{-- DESKTOP --}}
                        <div class="d-none d-md-block">
                            <table class="table table-borderless table-sm mb-0">
                                <tr>
                                    <th width="160">{{ __('whatsapp.feature.label.name') }}</th>
                                    <td>{{ $whatsappfeature->name }}</td>
                                </tr>
                                <tr>
                                    <th>{{ __('whatsapp.feature.label.alias') }}</th>
                                    <td>
                                        <span class="badge bg-secondary">
                                            {{ $whatsappfeature->alias }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <th>{{ __('whatsapp.feature.label.gateway') }}</th>
                                    <td>
                                        <span class="badge bg-info">
                                            {{ $whatsappfeature->gateway_label }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <th>{{ __('whatsapp.feature.label.status') }}</th>
                                    <td>
                                        <span
                                            class="badge badge-{{ $whatsappfeature->is_active ? 'success' : 'secondary' }}">
                                            {{ $whatsappfeature->is_active ? __('whatsapp.feature.status.active') : __('whatsapp.feature.status.inactive') }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <th>{{ __('whatsapp.feature.label.created-by') }}</th>
                                    <td>{{ $whatsappfeature->created_by }}</td>
                                </tr>
                                <tr>
                                    <th>{{ __('whatsapp.feature.label.updated-by') }}</th>
                                    <td>{{ $whatsappfeature->updated_by }}</td>
                                </tr>
                                <tr>
                                    <th>{{ __('whatsapp.feature.label.created-at') }}</th>
                                    <td>{{ $whatsappfeature->created_at->format('d M Y H:i') }}</td>
                                </tr>
                                <tr>
                                    <th>{{ __('whatsapp.feature.label.updated-at') }}</th>
                                    <td>{{ $whatsappfeature->updated_at->format('d M Y H:i') }}</td>
                                </tr>
                                <tr>
                                    <th>{{ __('whatsapp.feature.label.description') }}</th>
                                    <td>{{ $whatsappfeature->description ?? '-' }}</td>
                                </tr>
                            </table>
                        </div>

                        {{-- MOBILE --}}
                        <div class="d-md-none">

                            {{-- NAME --}}
                            <div class="mb-3">
                                <small class="text-muted">{{ __('whatsapp.feature.label.name') }}</small>
                                <div class="fw-bold">
                                    {{ $whatsappfeature->name }}
                                </div>
                            </div>

                            {{-- ALIAS --}}
                            <div class="mb-3">
                                <small class="text-muted">{{ __('whatsapp.feature.label.alias') }}</small><br>
                                <span class="badge bg-secondary">
                                    {{ $whatsappfeature->alias }}
                                </span>
                            </div>

                            {{-- GATEWAY --}}
                            <div class="mb-3">
                                <small class="text-muted">{{ __('whatsapp.feature.label.gateway') }}</small><br>
                                <span class="badge bg-info">
                                    {{ $whatsappfeature->gateway_label }}
                                </span>
                            </div>

                            {{-- STATUS --}}
                            <div class="mb-3">
                                <small class="text-muted">{{ __('whatsapp.feature.label.status') }}</small><br>
                                <span class="badge badge-{{ $whatsappfeature->is_active ? 'success' : 'secondary' }}">
                                    {{ $whatsappfeature->is_active ? __('whatsapp.feature.status.active') : __('whatsapp.feature.status.inactive') }}
                                </span>
                            </div>

                            <hr class="my-3">

                            {{-- CREATED / UPDATED --}}
                            <div class="mb-2">
                                <small class="text-muted">{{ __('whatsapp.feature.label.created-by') }}</small>
                                <div>{{ $whatsappfeature->created_by ?? '-' }}</div>
                            </div>

                            <div class="mb-2">
                                <small class="text-muted">{{ __('whatsapp.feature.label.updated-by') }}</small>
                                <div>{{ $whatsappfeature->updated_by ?? '-' }}</div>
                            </div>

                            <div class="mb-2">
                                <small class="text-muted">{{ __('whatsapp.feature.label.created-at') }}</small>
                                <div>{{ $whatsappfeature->created_at?->format('d M Y H:i') }}</div>
                            </div>

                            <div class="mb-3">
                                <small class="text-muted">{{ __('whatsapp.feature.label.updated-at') }}</small>
                                <div>{{ $whatsappfeature->updated_at?->format('d M Y H:i') }}</div>
                            </div>

                            <hr class="my-3">

                            {{-- DESCRIPTION --}}
                            <div class="mb-0">
                                <small class="text-muted">{{ __('whatsapp.feature.label.description') }}</small>
                                <div>
                                    {{ $whatsappfeature->description ?? __('whatsapp.feature.message.no-description') }}
                                </div>
                            </div>

                        </div>

                    </div>
                </div>

            </div>

            {{-- RIGHT : SCHEDULE DETAIL --}}
            <div class="col-md-6 mb-4">

                <div class="card shadow-sm h-100">
                    <div class="card-header">
                        {{ __('whatsapp.feature.title.schedule-detail') }}
                    </div>

                    <div class="card-body">

                        @if ($whatsappfeature->is_scheduled && !empty($whatsappfeature->schedule))
                            <div class="alert alert-info py-2 small">
                                <i class="fa fa-clock me-1"></i>
                                {{ __('whatsapp.feature.message.schedule-active') }}
                            </div>

                            <div class="mb-3">
                                <small class="text-muted">{{ __('whatsapp.feature.label.timezone') }}</small>
                                <div class="fw-bold">
                                    {{ data_get($whatsappfeature->schedule, 'timezone', '-') }}
                                </div>
                            </div>

                            <div class="mb-3">
                                <small class="text-muted">{{ __('whatsapp.feature.label.schedule-days') }}</small>
                                <div>
                                    <span class="badge bg-light text-dark">
                                        {{ implode(', ', data_get($whatsappfeature->schedule, 'days', [])) }}
                                    </span>
                                </div>
                            </div>

                            <div class="mb-0">
                                <small
                                    class="text-muted">{{ __('whatsapp.feature.label.schedule-time-window') }}</small>
                                <div class="fw-bold">
                                    {{ data_get($whatsappfeature->schedule, 'time_window.start') }}
                                    –
                                    {{ data_get($whatsappfeature->schedule, 'time_window.end') }}
                                </div>
                            </div>
                        @else
                            <div class="alert alert-secondary small mb-0">
                                <i class="fa fa-info-circle me-1"></i>
                                {{ __('whatsapp.feature.message.schedule-inactive') }}
                            </div>
                        @endif

                    </div>
                </div>

            </div>

        </div>
    </div>

    {{-- FOOTER --}}
    <div class="card-footer d-flex flex-column flex-md-row justify-content-end">

        <a href="{{ route('whatsappfeature.index') }}" class="btn btn-secondary btn-sm mb-2 mb-md-0 me-md-2">
            <i class="fa fa-arrow-left me-1"></i>
            {{ __('whatsapp.feature.button.back') }}
        </a>

        <a href="{{ route('whatsappfeature.edit', $whatsappfeature->slug) }}" class="btn btn-primary btn-sm">
            <i class="fa fa-edit me-1"></i>
            {{ __('whatsapp.feature.button.edit') }}
        </a>

    </div>

</x-layouts.cpanel.settings.app-layout>
