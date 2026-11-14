<x-layouts.cpanel.settings.app-layout title="{{ __('whatsapp.feature.title.delete-feature') }}">

    {{-- HEADER --}}
    <div class="card-header py-3">
        <h5 class="m-0 fw-bold text-danger">
            {{ __('whatsapp.feature.title.delete-feature') }}
        </h5>

        <div class="mt-1 text-muted" style="font-size:.95rem;">
            {{ __('whatsapp.feature.description.delete-warning') }}
        </div>
    </div>

    <form action="{{ route('whatsappfeature.destroy', $whatsappfeature->slug) }}" method="POST">
        @csrf
        @method('DELETE')

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
                            <span class="badge bg-secondary">
                                {{ $whatsappfeature->alias }}
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <th>{{ __('whatsapp.feature.label.gateway-type') }}</th>
                        <td>
                            <span class="badge bg-info">
                                {{ $whatsappfeature->gateway_label }}
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <th>{{ __('whatsapp.feature.label.schedule-status') }}</th>
                        <td>
                            @if ($whatsappfeature->is_scheduled)
                                <span class="badge bg-warning text-dark">
                                    {{ __('whatsapp.feature.schedule-status.scheduled') }}
                                </span>
                            @else
                                <span class="badge bg-secondary">
                                    {{ __('whatsapp.feature.schedule-status.not-scheduled') }}
                                </span>
                            @endif
                        </td>
                    </tr>
                </table>

                @if ($whatsappfeature->is_scheduled && !empty($whatsappfeature->schedule))
                    <div class="alert alert-warning mt-3 mb-0" style="font-size: .9rem;">
                        <div class="fw-bold mb-1">
                            <i class="fa fa-clock me-1"></i>
                            {{ __('whatsapp.feature.message.active-schedule-detected') }}
                        </div>

                        <ul class="mb-0 ps-3">
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
                            <i class="fa fa-info-circle me-1"></i>
                            {{ __('whatsapp.feature.help.delete-with-active-schedule') }}
                        </div>
                    </div>
                @endif

                <div class="mt-3">
                    <small class="text-muted">
                        <i class="fa fa-exclamation-triangle me-1"></i>
                        {{ __('whatsapp.feature.help.delete-irreversible') }}
                    </small>
                </div>

            </div>

        </div>

        {{-- FOOTER --}}
        <div class="card-footer text-end">
            <a href="{{ route('whatsappfeature.show', $whatsappfeature->slug) }}"
                class="btn btn-secondary btn-sm me-2">
                <i class="fa fa-arrow-left me-1"></i>
                {{ __('whatsapp.feature.button.cancel') }}
            </a>

            <button type="submit" class="btn btn-danger btn-sm">
                <i class="fa fa-trash me-1"></i>
                {{ __('whatsapp.feature.button.delete') }}
            </button>
        </div>

    </form>

</x-layouts.cpanel.settings.app-layout>
