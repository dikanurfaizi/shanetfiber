<x-layouts.cpanel.settings.app-layout title="{{ __('whatsapp.feature.title.delete-feature') }}">

    {{-- HEADER --}}
    <div class="card-header py-3">
        <h5 class="m-0 font-weight-bold text-danger">
            {{ __('whatsapp.feature.title.delete-feature') }}
        </h5>

        <div class="mt-1 text-muted" style="font-size:.95rem;">
            {{ __('whatsapp.feature.description.delete-warning') }}
        </div>
    </div>

    {!! Form::open([
        'method' => 'DELETE',
        'route' => ['whatsappfeature.destroy', $whatsappfeature->slug],
    ]) !!}

    <div class="card-body col-lg-8">

        <div class="alert alert-danger">

            <strong>
                {{ __('whatsapp.feature.message.delete-confirm', [
                    'name' => $whatsappfeature->name,
                ]) }}
            </strong>

            <hr>

            <table class="table table-sm table-borderless mb-0">
                <tr>
                    <th width="160">{{ __('whatsapp.feature.label.name') }}</th>
                    <td>{{ $whatsappfeature->name }}</td>
                </tr>
                <tr>
                    <th>{{ __('whatsapp.feature.label.alias') }}</th>
                    <td>
                        <span class="badge badge-secondary">
                            {{ $whatsappfeature->alias }}
                        </span>
                    </td>
                </tr>
                <tr>
                    <th>{{ __('whatsapp.feature.label.gateway-type') }}</th>
                    <td>
                        <span class="badge badge-info">
                            {{ $whatsappfeature->gateway_label }}
                        </span>
                    </td>
                </tr>
                <tr>
                    <th>{{ __('whatsapp.feature.label.schedule-status') }}</th>
                    <td>
                        @if ($whatsappfeature->is_scheduled)
                            <span class="badge badge-warning">
                                {{ __('whatsapp.feature.schedule-status.scheduled') }}
                            </span>
                        @else
                            <span class="badge badge-secondary">
                                {{ __('whatsapp.feature.schedule-status.not-scheduled') }}
                            </span>
                        @endif
                    </td>
                </tr>
            </table>

            @if ($whatsappfeature->is_scheduled && !empty($whatsappfeature->schedule))
                <div class="alert alert-warning mt-3 mb-0" style="font-size: .9rem;">
                    <div class="font-weight-bold mb-1">
                        <i class="fa fa-clock mr-1"></i>
                        {{ __('whatsapp.feature.message.active-schedule-detected') }}
                    </div>

                    <ul class="mb-0 pl-3">
                        <li>
                            {{ __('whatsapp.feature.label.timezone') }}:
                            <strong>{{ data_get($whatsappfeature->schedule, 'timezone', '-') }}</strong>
                        </li>

                        <li>
                            {{ __('whatsapp.feature.label.schedule-days') }}:
                            <strong>
                                {{ implode(', ', data_get($whatsappfeature->schedule, 'days', [])) }}
                            </strong>
                        </li>

                        <li>
                            {{ __('whatsapp.feature.label.schedule-time-window') }}:
                            <strong>
                                {{ data_get($whatsappfeature->schedule, 'time_window.start') }}
                                –
                                {{ data_get($whatsappfeature->schedule, 'time_window.end') }}
                            </strong>
                        </li>
                    </ul>

                    <div class="mt-2 text-muted">
                        <i class="fa fa-info-circle mr-1"></i>
                        {{ __('whatsapp.feature.help.delete-with-active-schedule') }}
                    </div>
                </div>
            @endif

            <div class="mt-3">
                <small class="text-muted">
                    <i class="fa fa-exclamation-triangle mr-1"></i>
                    {{ __('whatsapp.feature.help.delete-irreversible') }}
                </small>
            </div>

        </div>

    </div>

    {{-- FOOTER --}}
    <div class="card-footer text-right">
        <a href="{{ route('whatsappfeature.show', $whatsappfeature->slug) }}" class="btn btn-secondary btn-sm mr-2">
            <i class="fa fa-arrow-left mr-1"></i>
            {{ __('whatsapp.feature.button.cancel') }}
        </a>

        <button type="submit" class="btn btn-danger btn-sm">
            <i class="fa fa-trash mr-1"></i>
            {{ __('whatsapp.feature.button.delete') }}
        </button>
    </div>

    {!! Form::close() !!}

</x-layouts.cpanel.settings.app-layout>
