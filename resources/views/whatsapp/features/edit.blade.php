<x-layouts.cpanel.settings.app-layout
    title="{{ __('whatsapp.feature.title.edit-feature', ['name' => $whatsappfeature->name]) }}">

    {{-- HEADER --}}
    <div class="card-header py-3">
        <h5 class="m-0 font-weight-bold text-primary">
            {{ __('whatsapp.feature.title.edit-feature', ['name' => $whatsappfeature->name]) }}
        </h5>

        <div class="mt-1 text-muted" style="font-size: .95rem;">
            {{ __('whatsapp.feature.description.edit-information') }}
        </div>
    </div>

    {!! Form::model($whatsappfeature, [
        'method' => 'PATCH',
        'route' => ['whatsappfeature.update', $whatsappfeature->slug],
    ]) !!}

    <div class="card-body col-lg-8">

        {{-- NAME --}}
        <div class="form-group row">
            <label class="col-md-3 col-form-label">
                {{ __('whatsapp.feature.label.name') }}
            </label>
            <div class="col-md-8">
                {!! Form::text('name', null, ['class' => 'form-control']) !!}
                @error('name')
                    <span class="text-danger">{{ $message }}</span>
                @enderror
            </div>
        </div>

        {{-- ALIAS (READONLY) --}}
        <div class="form-group row">
            <label class="col-md-3 col-form-label">
                {{ __('whatsapp.feature.label.alias') }}
            </label>
            <div class="col-md-8">
                <input type="text" class="form-control" value="{{ $whatsappfeature->alias }}" readonly>
                <small class="text-muted">
                    {{ __('whatsapp.feature.help.alias-readonly') }}
                </small>
            </div>
        </div>

        {{-- DESCRIPTION --}}
        <div class="form-group row">
            <label class="col-md-3 col-form-label">
                {{ __('whatsapp.feature.label.description') }}
            </label>
            <div class="col-md-8">
                {!! Form::textarea('description', null, ['class' => 'form-control', 'rows' => 2]) !!}
            </div>
        </div>

        <hr>

        {{-- TYPE SWITCH --}}
        <div class="form-group row">
            <label class="col-md-3 col-form-label">
                {{ __('whatsapp.feature.label.type') }}
            </label>

            <div class="col-md-8">

                <div class="custom-control custom-radio mb-2">
                    <input type="radio" id="type_onprem" name="type" value="onprem" class="custom-control-input"
                        {{ $whatsappfeature->type === 'onprem' ? 'checked' : '' }}>
                    <label class="custom-control-label" for="type_onprem">
                        {{ __('whatsapp.feature.type.onprem') }}
                    </label>
                    <div class="small text-muted ml-4">
                        {{ __('whatsapp.feature.help.onprem') }}
                    </div>
                </div>

                <div class="custom-control custom-radio">
                    <input type="radio" id="type_cloud" name="type" value="cloud" class="custom-control-input"
                        {{ $whatsappfeature->type === 'cloud' ? 'checked' : '' }}>
                    <label class="custom-control-label" for="type_cloud">
                        {{ __('whatsapp.feature.type.cloud') }}
                    </label>
                    <div class="small text-muted ml-4">
                        {{ __('whatsapp.feature.help.cloud') }}
                    </div>
                </div>

                <small class="text-warning d-block mt-2">
                    <i class="fa fa-exclamation-triangle mr-1"></i>
                    {{ __('whatsapp.feature.help.type-change-warning') }}
                </small>

                @error('type')
                    <span class="text-danger">{{ $message }}</span>
                @enderror

            </div>
        </div>

        <hr>

        {{-- STATUS TOGGLE --}}
        <div class="form-group row align-items-center">
            <label class="col-md-3 col-form-label">
                {{ __('whatsapp.feature.label.status') }}
            </label>
            <div class="col-md-8">
                <div class="custom-control custom-switch">
                    {!! Form::hidden('is_active', 0) !!}
                    {!! Form::checkbox('is_active', 1, $whatsappfeature->is_active, [
                        'class' => 'custom-control-input',
                        'id' => 'is_active',
                    ]) !!}
                    <label class="custom-control-label" for="is_active">
                        {{ __('whatsapp.feature.status.active') }}
                    </label>
                </div>
            </div>
        </div>

        {{-- SCHEDULE TOGGLE --}}
        <div class="form-group row align-items-center">
            <label class="col-md-3 col-form-label">
                {{ __('whatsapp.feature.label.schedule-status') }}
            </label>
            <div class="col-md-8">
                <div class="custom-control custom-switch">
                    {!! Form::hidden('is_scheduled', 0) !!}
                    {!! Form::checkbox('is_scheduled', 1, $whatsappfeature->is_scheduled, [
                        'class' => 'custom-control-input',
                        'id' => 'is_scheduled',
                    ]) !!}
                    <label class="custom-control-label" for="is_scheduled">
                        {{ __('whatsapp.feature.schedule-status.scheduled') }}
                    </label>
                </div>

                <small id="schedule-cloud-note" class="text-warning d-block mt-1"
                    style="{{ $whatsappfeature->type === 'cloud' ? 'display:none;' : '' }}">
                    <i class="fa fa-info-circle mr-1"></i>
                    {{ __('whatsapp.feature.help.schedule-only-cloud') }}
                </small>
            </div>
        </div>

        {{-- SCHEDULE CONFIGURATION --}}
        <div id="schedule-config" class="border rounded p-3 mt-3"
            style="{{ $whatsappfeature->is_scheduled ? '' : 'display:none;' }}">

            <h6 class="font-weight-bold mb-3">
                {{ __('whatsapp.feature.title.schedule-configuration') }}
            </h6>

            {{-- TIMEZONE --}}
            <div class="form-group row">
                <label class="col-md-3 col-form-label">
                    {{ __('whatsapp.feature.label.timezone') }}
                </label>
                <div class="col-md-8">
                    <select name="schedule[timezone]" class="form-control">
                        <option value="Asia/Jakarta" selected>Asia/Jakarta</option>
                    </select>
                    <small class="text-muted">
                        {{ __('whatsapp.feature.help.timezone') }}
                    </small>
                </div>
            </div>

            {{-- DAYS --}}
            <div class="form-group row">
                <label class="col-md-3 col-form-label">
                    {{ __('whatsapp.feature.label.schedule-days') }}
                </label>
                <div class="col-md-8">
                    <div class="d-flex flex-wrap">
                        @for ($i = 1; $i <= 31; $i++)
                            <div class="custom-control custom-checkbox mr-3 mb-2">
                                <input type="checkbox" class="custom-control-input" id="day_{{ $i }}"
                                    name="schedule[days][]" value="{{ $i }}"
                                    {{ in_array($i, data_get($whatsappfeature->schedule, 'days', [])) ? 'checked' : '' }}>
                                <label class="custom-control-label" for="day_{{ $i }}">
                                    {{ $i }}
                                </label>
                            </div>
                        @endfor
                    </div>
                    <small class="text-muted">
                        {{ __('whatsapp.feature.help.schedule-days') }}
                    </small>
                    <div class="alert alert-info py-2 mt-2 mb-0" role="alert" style="font-size: 0.85rem;">
                        <i class="fa fa-info-circle mr-1"></i>
                        {{ __('whatsapp.feature.help.schedule-days-fallback') }}
                    </div>
                </div>
            </div>

            {{-- TIME WINDOW --}}
            <div class="form-group row">
                <label class="col-md-3 col-form-label">
                    {{ __('whatsapp.feature.label.schedule-time-window') }}
                </label>
                <div class="col-md-8">
                    <div class="d-flex">
                        <input type="time" name="schedule[time_window][start]" class="form-control mr-2"
                            value="{{ data_get($whatsappfeature->schedule, 'time_window.start', '08:00') }}">
                        <input type="time" name="schedule[time_window][end]" class="form-control"
                            value="{{ data_get($whatsappfeature->schedule, 'time_window.end', '21:00') }}">
                    </div>
                    <small class="text-muted">
                        {{ __('whatsapp.feature.help.schedule-time-window') }}
                    </small>
                </div>
            </div>

        </div>

    </div>

    {{-- FOOTER --}}
    <div class="card-footer text-right">
        <a href="{{ route('whatsappfeature.show', $whatsappfeature->slug) }}" class="btn btn-secondary btn-sm mr-2">
            {{ __('whatsapp.feature.button.back') }}
        </a>

        <button type="submit" class="btn btn-primary btn-sm">
            <i class="fa fa-save mr-1"></i>
            {{ __('whatsapp.feature.button.update') }}
        </button>
    </div>

    {!! Form::close() !!}

    @section('custom_scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {

                const scheduleToggle = document.getElementById('is_scheduled');
                const scheduleConfig = document.getElementById('schedule-config');
                const typeRadios = document.querySelectorAll('input[name="type"]');

                if (!scheduleToggle || !scheduleConfig) {
                    return;
                }

                function getSelectedType() {
                    const checked = document.querySelector('input[name="type"]:checked');
                    return checked ? checked.value : null;
                }

                function applyScheduleRules() {
                    const type = getSelectedType();

                    if (type === 'cloud') {
                        // Cloud → schedule allowed
                        scheduleToggle.disabled = false;

                        if (scheduleToggle.checked) {
                            scheduleConfig.style.display = 'block';
                        } else {
                            scheduleConfig.style.display = 'none';
                        }
                    } else {
                        // Onprem → schedule forbidden
                        scheduleToggle.checked = false;
                        scheduleToggle.disabled = true;
                        scheduleConfig.style.display = 'none';
                    }
                }

                // When schedule toggled
                scheduleToggle.addEventListener('change', applyScheduleRules);

                // When type changed
                typeRadios.forEach(function(radio) {
                    radio.addEventListener('change', applyScheduleRules);
                });

                // Initial state on page load
                applyScheduleRules();

            });
        </script>
    @endsection

</x-layouts.cpanel.settings.app-layout>
