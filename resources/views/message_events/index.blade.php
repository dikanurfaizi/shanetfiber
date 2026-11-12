<x-layouts.cpanel.settings.app-layout :title="__('message_event.app_title')">

    @section('custom_styles')
        {{-- STYLE TAMBAHAN: Untuk menyulap checkbox menjadi tombol pill yang ciamik --}}
        <style>
            .message-type-badge-group .form-check {
                padding-left: 0;
            }

            /* Efek saat checkbox terpilih (Checked) */
            .message-type-badge-group .form-check-input:checked~.form-check-label {
                background-color: #007bff !important;
                color: #fff !important;
                border-color: #007bff !important;
            }

            /* Style default tombol pill */
            .message-type-badge-group .form-check-label {
                cursor: pointer;
                padding: 0.375rem 0.75rem;
                border: 1px solid #ced4da;
                border-radius: 50px;
                background-color: #fff;
                color: #495057;
                transition: all 0.15s ease-in-out;
                user-select: none;
                display: inline-block;
            }

            /* Efek hover ketika mouse berada di atas tombol */
            .message-type-badge-group .form-check-label:hover {
                background-color: #f8f9fa;
                border-color: #b5bbc1;
            }

            .message-type-badge-group .form-check-input {
                display: none;
            }
        </style>
    @endsection

    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">@lang('message_event.title')</h5>
        </div>

        <div class="card-body border-bottom">
            <form method="GET" action="{{ route('messageevents.index') }}">

                {{-- BASIC FILTER --}}
                <div class="row g-2">

                    <div class="col-12 col-md-6 mb-2">
                        <label
                            class="small fw-bold text-muted mb-1">{{ __('message_event.label.find_customer') }}</label>
                        <input type="text" name="q" class="form-control"
                            placeholder="{{ __('message_event.placeholder.placeholder_search') }}"
                            value="{{ request('q') }}">
                    </div>

                    <div class="col-12 col-md-6 mb-2">
                        <label class="small fw-bold text-muted mb-1">Channel</label>
                        <select name="channel" class="form-control">
                            <option value="">@lang('message_event.label.all_channel')</option>
                            @foreach ($channels as $channel)
                                <option value="{{ $channel }}" @selected(request('channel') === $channel)>
                                    {{ strtoupper($channel) }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                </div>

                {{-- DESIGN BARU: Tipe Pesan dalam bentuk Badge/Button Selectable --}}
                <div class="mb-3 mb-2">
                    <label
                        class="small fw-bold text-muted mb-1 d-block">{{ __('message_event.label.message_types') }}</label>
                    <div class="d-flex flex-wrap align-items-center message-type-badge-group" style="gap: 8px;">
                        @foreach ($messageTypes as $index => $type)
                            @php
                                $isChecked =
                                    is_array(request('message_type')) && in_array($type, request('message_type'));
                            @endphp
                            <div class="form-check">
                                <input type="checkbox" name="message_type[]" value="{{ $type }}"
                                    class="form-check-input" id="msg_type_{{ $index }}"
                                    @checked($isChecked)>
                                <label class="form-check-label small shadow-sm" for="msg_type_{{ $index }}">
                                    {{ $type }}
                                </label>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="mb-2">
                    <a class="text-decoration-none" data-bs-toggle="collapse" href="#advancedFilter" role="button"
                        aria-expanded="{{ request()->filled('date') || request()->filled('time_from') || request()->filled('time_to') ? 'true' : 'false' }}"
                        aria-controls="advancedFilter">

                        <small>
                            <i class="fas fa-sliders-h me-1"></i>
                            @lang('message_event.button.advanced_filter')
                        </small>
                    </a>
                </div>

                <div class="collapse {{ request()->filled('date') || request()->filled('time_from') || request()->filled('time_to') ? 'show' : '' }}"
                    id="advancedFilter">

                    <div class="row g-2">

                        <div class="col-12 col-md-4 mb-2">
                            <input type="date" name="date" class="form-control" value="{{ request('date') }}">
                        </div>

                        <div class="col-6 col-md-4 mb-2">
                            <input type="time" name="time_from" class="form-control"
                                value="{{ request('time_from') }}">
                        </div>

                        <div class="col-6 col-md-4 mb-2">
                            <input type="time" name="time_to" class="form-control" value="{{ request('time_to') }}">
                        </div>

                    </div>
                </div>

                {{-- ROW 3: BUTTON --}}
                <div class="row g-2 mt-2">
                    <div class="col-12 text-start">

                        {{-- Desktop & Tablet --}}
                        <div class="d-none d-md-inline-block">
                            <button class="btn btn-primary me-2">
                                @lang('message_event.button.filter')
                            </button>
                            <a href="{{ route('messageevents.index') }}" class="btn btn-outline-secondary">
                                @lang('message_event.button.reset')
                            </a>
                        </div>

                        {{-- Mobile --}}
                        <div class="d-block d-md-none">
                            <button class="btn btn-primary w-100 mb-2">
                                @lang('message_event.button.filter')
                            </button>
                            <a href="{{ route('messageevents.index') }}" class="btn btn-outline-secondary w-100">
                                @lang('message_event.button.reset')
                            </a>
                        </div>

                    </div>
                </div>

            </form>
        </div>

        <div class="card-body border-bottom">
            {{-- QUICK FILTER --}}
            <div class="mb-3">

                <span class="text-muted small me-2">@lang('message_event.label.quick_filter')</span>

                <a href="{{ route('messageevents.index', array_merge(request()->except('status'), ['status' => 'failed'])) }}"
                    class="btn btn-sm btn-outline-danger me-1 {{ request('status') === 'failed' ? 'active' : '' }}">
                    ❌ @lang('message_event.state.status_failed')
                </a>

                <a href="{{ route('messageevents.index', array_merge(request()->except('status'), ['status' => 'pending'])) }}"
                    class="btn btn-sm btn-outline-warning me-1 {{ request('status') === 'pending' ? 'active' : '' }}">
                    ⏳ @lang('message_event.state.status_pending')
                </a>

                <a href="{{ route('messageevents.index', array_merge(request()->except('status'), ['status' => 'success'])) }}"
                    class="btn btn-sm btn-outline-success me-1 {{ request('status') === 'success' ? 'active' : '' }}">
                    ✅ @lang('message_event.state.status_success')
                </a>

                <a href="{{ route('messageevents.index', request()->except('status')) }}"
                    class="btn btn-sm btn-outline-secondary">
                    🔄 @lang('message_event.state.status_all')
                </a>

            </div>
        </div>

        {{-- DESKTOP TABLE --}}
        <div class="d-none d-md-block">
            <div class="table-responsive">
                <table class="table table-hover table-bordered mb-0">
                    <thead class="thead-light">
                        <tr>
                            <th>@lang('message_event.table.hash')</th>
                            <th>@lang('message_event.table.channel')</th>
                            <th>@lang('message_event.table.customer')</th>
                            <th>@lang('message_event.table.phone')</th>
                            <th>@lang('message_event.table.message_type')</th>
                            <th>@lang('message_event.table.status')</th>
                            <th>@lang('message_event.table.sent_at')</th>
                            <th>@lang('message_event.table.scheduled_at')</th>
                            <th>@lang('message_event.table.action')</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($events as $i => $event)
                            <tr>
                                <td>{{ $events->firstItem() + $i }}</td>
                                <td><span class="badge bg-info">{{ strtoupper($event->channel) }}</span></td>
                                <td>{{ $event->customer->name ?? '-' }}</td>
                                <td>
                                    @if ($event->customer && $event->customer->contact)
                                        {{ $event->customer->contact }}
                                    @else
                                        @php
                                            $metaData = is_string($event->meta)
                                                ? json_decode($event->meta, true)
                                                : $event->meta;
                                            $phoneFallback = isset($metaData['phone'])
                                                ? str_replace('@s.whatsapp.net', '', $metaData['phone'])
                                                : '-';
                                        @endphp
                                        {{ $phoneFallback }}
                                    @endif
                                </td>
                                <td>{{ $event->message_type }}</td>
                                <td>
                                    @php
                                        $colorMap = [
                                            'success' => 'success',
                                            'failed' => 'danger',
                                            'pending' => 'warning',
                                        ];
                                    @endphp
                                    <span class="badge badge-{{ $colorMap[$event->status] ?? 'secondary' }}">
                                        {{ __('message_event.db_status.' . $event->status) ?? strtoupper($event->status) }}
                                    </span>
                                </td>
                                <td>{{ $event->sent_at ? $event->sent_at->translatedFormat('d M Y H:i:s') : '-' }}</td>

                                <td>
                                    @if ($event->scheduled_at)
                                        {{ $event->scheduled_at->translatedFormat('d M Y H:i:s') }}
                                    @else
                                        <span class="badge bg-secondary shadow-sm">⚡ @lang('message_event.label.realtime')</span>
                                    @endif
                                </td>
                                <td>
                                    {{-- <button class="btn btn-sm btn-outline-primary" data-bs-toggle="collapse"
                                        data-bs-target="#detail-{{ $event->id }}">
                                        <i class="fas fa-eye"></i> @lang('message_event.button.btn_detail')
                                    </button> --}}

                                    <a href="{{ route('messageevents.show', $event->id) }}"
                                        class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-eye"></i> @lang('message_event.button.btn_detail')
                                    </a>

                                    @if ($event->status === 'failed')
                                        <form action="{{ route('messageevents.resend', $event->id) }}" method="POST"
                                            style="display:inline;">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-outline-danger">
                                                <i class="fas fa-redo-alt"></i> @lang('message_event.button.btn_retry')
                                            </button>
                                        </form>
                                    @endif
                                </td>
                            </tr>

                            <tr class="collapse bg-light" id="detail-{{ $event->id }}">
                                <td colspan="9" class="p-3">
                                    <div class="mb-2">
                                        <strong>@lang('message_event.label.label_response')</strong><br>
                                        <code>{{ $event->response_summary }}</code>
                                    </div>

                                    <div class="small text-muted">
                                        <div>
                                            <strong>Trace ID:</strong>
                                            <code>{{ $event->trace_id ?? '-' }}</code>
                                        </div>
                                        <div>
                                            <strong>Message ID:</strong>
                                            <code>{{ $event->message_id ?? '-' }}</code>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center text-muted">
                                    @lang('message_event.state.no_events')
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- MOBILE CARDS --}}
        <div class="d-block d-md-none p-3">
            @forelse ($events as $event)
                <div class="card mb-2 shadow-sm">
                    <div class="card-body p-2">

                        <div class="d-flex justify-content-between mb-1">
                            <span class="badge bg-info">
                                {{ strtoupper($event->channel) }}
                            </span>

                            @php
                                $colorMap = ['success' => 'success', 'failed' => 'danger', 'pending' => 'warning'];
                            @endphp

                            <span class="badge badge-{{ $colorMap[$event->status] ?? 'secondary' }}">
                                {{ __('message_event.db_status.' . $event->status) ?? strtoupper($event->status) }}
                            </span>
                        </div>

                        <div class="small text-muted mb-1">
                            {{ $event->sent_at ? $event->sent_at->translatedFormat('d M Y H:i') : '-' }}
                        </div>

                        <strong
                            class="text-break">{{ $event->customer->name ?? __('message_event.label.unknown_customer') }}</strong><br>

                        <span class="text-muted text-break">
                            @if ($event->customer && $event->customer->contact)
                                {{ $event->customer->contact }}
                            @else
                                @php
                                    $metaData = is_string($event->meta)
                                        ? json_decode($event->meta, true)
                                        : $event->meta;
                                    $phoneFallback = isset($metaData['phone'])
                                        ? str_replace('@s.whatsapp.net', '', $metaData['phone'])
                                        : '-';
                                @endphp
                                {{ $phoneFallback }}
                            @endif
                        </span>

                        <div class="mt-2 d-flex flex-wrap align-items-center" style="gap: 6px;">
                            <span class="badge bg-light text-dark text-wrap text-break border text-start"
                                style="max-width: 100%;">
                                {{ $event->message_type }}
                            </span>

                            @if ($event->scheduled_at)
                                <span class="badge bg-light text-dark text-muted border">
                                    📅 {{ $event->scheduled_at->translatedFormat('d M Y H:i') }}
                                </span>
                            @else
                                <span class="badge bg-secondary">
                                    ⚡ @lang('message_event.label.realtime')
                                </span>
                            @endif
                        </div>

                        <div class="mt-2 d-flex" style="gap: 6px;">
                            {{-- <button class="btn btn-sm btn-outline-primary flex-grow-1" data-bs-toggle="collapse"
                                data-bs-target="#detail-mobile-{{ $event->id }}">
                                @lang('message_event.button.btn_view_detail')
                            </button> --}}

                            <a href="{{ route('messageevents.show', $event->id) }}"
                                class="btn btn-sm btn-outline-primary flex-grow-1">
                                @lang('message_event.button.btn_view_detail')
                            </a>

                            @if ($event->status === 'failed')
                                <form action="{{ route('messageevents.resend', $event->id) }}" method="POST"
                                    class="flex-grow-1">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-outline-danger w-100">
                                        <i class="fas fa-redo-alt"></i> @lang('message_event.button.btn_retry')
                                    </button>
                                </form>
                            @endif
                        </div>

                        <div class="collapse mt-2" id="detail-mobile-{{ $event->id }}">
                            <div class="bg-light p-2 rounded small">

                                <div class="mb-2">
                                    <strong>@lang('message_event.label.label_response')</strong><br>
                                    <code class="text-break">{{ $event->response_summary }}</code>
                                </div>

                                <div class="text-muted">
                                    <div class="mb-1">
                                        <strong>{{ __('message_event.label.trace_id') }}:</strong><br>
                                        <code class="text-break">{{ $event->trace_id ?? '-' }}</code>
                                    </div>

                                    <div>
                                        <strong>{{ __('message_event.label.message_id') }}:</strong><br>
                                        <code class="text-break">{{ $event->message_id ?? '-' }}</code>
                                    </div>
                                </div>

                            </div>
                        </div>

                    </div>
                </div>
            @empty
                <div class="text-center text-muted">
                    @lang('message_event.state.no_events')
                </div>
            @endforelse
        </div>

        <div class="card-footer">
            {{ $events->links() }}
        </div>
    </div>

</x-layouts.cpanel.settings.app-layout>
