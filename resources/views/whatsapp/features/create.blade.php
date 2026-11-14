<x-layouts.cpanel.settings.app-layout title="{{ __('whatsapp.feature.title.create-feature') }}">
    {{-- HEADER --}}
    <div class="card-header py-3">
        <h5 class="m-0 fw-bold text-primary">
            {{ __('whatsapp.feature.title.create-feature') }}
        </h5>

        <div class="mt-1 text-muted" style="font-size:.95rem;">
            {{ __('whatsapp.feature.description.create-information') }}
        </div>
    </div>

    <div class="row">
        <div class="col-lg-12">
            <form action="{{ route('whatsappfeature.store') }}" method="POST">
                @csrf

                <div class="card-body col-lg-8">
                    {{-- NAME --}}
                    <div class="mb-3 row">
                        <label for="feature-name" class="col-md-3 col-form-label">
                            {{ __('whatsapp.feature.label.name') }}
                        </label>
                        <div class="col-md-8">
                            <input type="text" name="name" id="feature-name" value="{{ old('name') }}"
                                class="form-control @error('name') is-invalid @enderror">
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    {{-- ALIAS INFO (AUTO) --}}
                    <div class="mb-3 row">
                        <label for="feature-alias" class="col-md-3 col-form-label">
                            {{ __('whatsapp.feature.label.alias') }}
                        </label>
                        <div class="col-md-8">
                            <input type="text" class="form-control" id="feature-alias"
                                value="{{ old('name') ? \Illuminate\Support\Str::slug(old('name'), '_') : '' }}"
                                readonly>
                            <small class="text-muted">
                                {{ __('whatsapp.feature.help.alias-generated') }}
                            </small>
                        </div>
                    </div>

                    {{-- DESCRIPTION --}}
                    <div class="mb-3 row">
                        <label for="description" class="col-md-3 col-form-label">
                            {{ __('whatsapp.feature.label.description') }}
                        </label>
                        <div class="col-md-8">
                            <textarea name="description" id="description" rows="2"
                                class="form-control @error('description') is-invalid @enderror">{{ old('description') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <hr>

                    {{-- TYPE --}}
                    <div class="mb-3 row">
                        <label class="col-md-3 col-form-label">
                            {{ __('whatsapp.feature.label.type') }}
                        </label>

                        <div class="col-md-8">
                            <div class="form-check">
                                <input type="radio" id="type_onprem" name="type" value="onprem"
                                    class="form-check-input" @checked(old('type') === 'onprem')>
                                <label class="form-check-label" for="type_onprem">
                                    {{ __('whatsapp.feature.type.onprem') }}
                                </label>
                                <div class="small text-muted ms-4">
                                    {{ __('whatsapp.feature.help.onprem') }}
                                </div>
                            </div>

                            <div class="form-check mb-2">
                                <input type="radio" id="type_cloud" name="type" value="cloud"
                                    class="form-check-input" @checked(old('type', 'cloud') === 'cloud')>
                                <label class="form-check-label" for="type_cloud">
                                    {{ __('whatsapp.feature.type.cloud') }}
                                </label>
                                <div class="small text-muted ms-4">
                                    {{ __('whatsapp.feature.help.cloud') }}
                                </div>
                            </div>

                            <small class="text-warning d-block mt-2">
                                <i class="fa fa-info-circle me-1"></i>
                                {{ __('whatsapp.feature.help.type-create-note') }}
                            </small>

                            @error('type')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <hr>

                    {{-- STATUS --}}
                    <div class="mb-3 row align-items-center">
                        <label class="col-md-3 col-form-label">
                            {{ __('whatsapp.feature.label.status') }}
                        </label>
                        <div class="col-md-8">
                            <div class="form-check form-switch">
                                <input type="hidden" name="is_active" value="0">
                                <input type="checkbox" name="is_active" value="1" id="is_active"
                                    class="form-check-input" @checked(old('is_active', true))>
                                <label class="form-check-label" for="is_active">
                                    {{ __('whatsapp.feature.status.active') }}
                                </label>
                            </div>
                        </div>
                    </div>

                    {{-- SCHEDULE --}}
                    <div class="mb-3 row align-items-center">
                        <label class="col-md-3 col-form-label">
                            {{ __('whatsapp.feature.label.schedule-status') }}
                        </label>
                        <div class="col-md-8">
                            <div class="form-check form-switch">
                                <input type="hidden" name="is_scheduled" value="0">
                                <input type="checkbox" name="is_scheduled" value="1" id="is_scheduled"
                                    class="form-check-input" @checked(old('is_scheduled', false))>
                                <label class="form-check-label" for="is_scheduled">
                                    {{ __('whatsapp.feature.schedule-status.scheduled') }}
                                </label>
                            </div>

                            <small class="text-muted d-block mt-1">
                                {{ __('whatsapp.feature.help.schedule-only-cloud') }}
                            </small>
                        </div>
                    </div>

                    {{-- SCHEDULE CONFIG --}}
                    <div id="schedule-config" class="border rounded p-3 mt-3" style="display:none;">
                        <h6 class="fw-bold mb-3">
                            {{ __('whatsapp.feature.title.schedule-configuration') }}
                        </h6>

                        {{-- TIMEZONE --}}
                        <div class="mb-3 row">
                            <label class="col-md-3 col-form-label">
                                {{ __('whatsapp.feature.label.timezone') }}
                            </label>
                            <div class="col-md-8">
                                <select name="schedule[timezone]" class="form-select">
                                    <option value="Asia/Jakarta">Asia/Jakarta</option>
                                </select>
                                <small class="text-muted">
                                    {{ __('whatsapp.feature.help.timezone') }}
                                </small>
                            </div>
                        </div>

                        {{-- DAYS (DATE OF MONTH) --}}
                        <div class="mb-3 row">
                            <label class="col-md-3 col-form-label">
                                {{ __('whatsapp.feature.label.schedule-days') }}
                            </label>

                            <div class="col-md-8">
                                <div class="d-flex flex-wrap">
                                    @for ($i = 1; $i <= 31; $i++)
                                        <div class="form-check me-3 mb-2">
                                            <input type="checkbox" class="form-check-input"
                                                id="schedule_day_{{ $i }}" name="schedule[days][]"
                                                value="{{ $i }}" @checked(is_array(old('schedule.days')) && in_array($i, old('schedule.days')))>
                                            <label class="form-check-label" for="schedule_day_{{ $i }}">
                                                {{ $i }}
                                            </label>
                                        </div>
                                    @endfor
                                </div>

                                <small class="text-muted d-block mt-1">
                                    {{ __('whatsapp.feature.help.schedule-days') }}
                                </small>

                                {{-- INFO FALLBACK --}}
                                <div class="alert alert-info py-2 mt-2 mb-0" role="alert"
                                    style="font-size: 0.85rem;">
                                    <i class="fa fa-info-circle me-1"></i>
                                    {{ __('whatsapp.feature.help.schedule-days-fallback') }}
                                </div>
                            </div>
                        </div>

                        {{-- TIME WINDOW --}}
                        <div class="mb-3 row">
                            <label class="col-md-3 col-form-label">
                                {{ __('whatsapp.feature.label.schedule-time-window') }}
                            </label>
                            <div class="col-md-8">
                                <div class="d-flex">
                                    <input type="time" name="schedule[time_window][start]"
                                        class="form-control me-2"
                                        value="{{ old('schedule.time_window.start', '08:00') }}">
                                    <input type="time" name="schedule[time_window][end]" class="form-control"
                                        value="{{ old('schedule.time_window.end', '21:00') }}">
                                </div>
                                <small class="text-muted mt-1 d-block">
                                    {{ __('whatsapp.feature.help.schedule-time-window') }}
                                </small>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- FOOTER --}}
                <div class="card-footer text-end">
                    <a href="{{ route('whatsappfeature.index') }}" class="btn btn-secondary btn-sm me-2">
                        {{ __('whatsapp.feature.button.back') }}
                    </a>

                    <button type="submit" class="btn btn-primary btn-sm">
                        <i class="fa fa-save me-1"></i>
                        {{ __('whatsapp.feature.button.create') }}
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- SCRIPT --}}
    @section('custom_scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // === Alias generator ===
                const nameInput = document.getElementById('feature-name');
                const aliasInput = document.getElementById('feature-alias');

                function slugify(text) {
                    return text
                        .toString()
                        .toLowerCase()
                        .trim()
                        .replace(/[\s\W-]+/g, '_')
                        .replace(/^_+|_+$/g, '');
                }

                if (nameInput && aliasInput) {
                    nameInput.addEventListener('input', function() {
                        aliasInput.value = slugify(this.value);
                    });
                }

                // === Schedule rules ===
                const scheduleToggle = document.getElementById('is_scheduled');
                const scheduleConfig = document.getElementById('schedule-config');
                const typeRadios = document.querySelectorAll('input[name="type"]');

                function selectedType() {
                    const r = document.querySelector('input[name="type"]:checked');
                    return r ? r.value : null;
                }

                function applyRules() {
                    const type = selectedType();

                    if (type === 'cloud') {
                        scheduleToggle.disabled = false;
                        scheduleConfig.style.display = scheduleToggle.checked ? 'block' : 'none';
                    } else {
                        scheduleToggle.checked = false;
                        scheduleToggle.disabled = true;
                        scheduleConfig.style.display = 'none';
                    }
                }

                if (scheduleToggle) {
                    scheduleToggle.addEventListener('change', applyRules);
                }

                typeRadios.forEach(r => r.addEventListener('change', applyRules));

                applyRules();
            });
        </script>
    @endsection
</x-layouts.cpanel.settings.app-layout>
