<x-layouts.cpanel.settings.app-layout :title="__('message_event.app_title')">

    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">@lang('message_event.title')</h5>
        </div>

        <div class="card-body border-bottom">
            <form method="GET" action="{{ route('messageevents.index') }}">

                {{-- BASIC FILTER --}}
                <div class="form-row">

                    <div class="col-12 col-md-4 mb-2">
                        <input type="text" name="q" class="form-control"
                            placeholder="{{ __('message_event.placeholder_search') }}" value="{{ request('q') }}">
                    </div>

                    <div class="col-12 col-md-4 mb-2">
                        <select name="channel" class="form-control">
                            <option value="">@lang('message_event.all_channel')</option>
                            @foreach ($channels as $channel)
                                <option value="{{ $channel }}" @selected(request('channel') === $channel)>
                                    {{ strtoupper($channel) }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-12 col-md-4 mb-2">
                        <select name="message_type" class="form-control">
                            <option value="">@lang('message_event.all_message_type')</option>
                            @foreach ($messageTypes as $type)
                                <option value="{{ $type }}" @selected(request('message_type') === $type)>
                                    {{ $type }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                </div>

                <div class="mb-2">
                    <a class="text-decoration-none" data-toggle="collapse" href="#advancedFilter" role="button"
                        aria-expanded="{{ request()->filled('date') || request()->filled('time_from') || request()->filled('time_to') ? 'true' : 'false' }}"
                        aria-controls="advancedFilter">

                        <small>
                            <i class="fas fa-sliders-h mr-1"></i>
                            @lang('message_event.advanced_filter')
                        </small>
                    </a>
                </div>

                <div class="collapse {{ request()->filled('date') || request()->filled('time_from') || request()->filled('time_to') ? 'show' : '' }}"
                    id="advancedFilter">

                    <div class="form-row">

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
                <div class="form-row">
                    <div class="col-12 text-left">

                        {{-- Desktop & Tablet --}}
                        <div class="d-none d-md-inline-block">
                            <button class="btn btn-primary mr-2">
                                @lang('message_event.filter')
                            </button>
                            <a href="{{ route('messageevents.index') }}" class="btn btn-outline-secondary">
                                @lang('message_event.reset')
                            </a>
                        </div>

                        {{-- Mobile --}}
                        <div class="d-block d-md-none">
                            <button class="btn btn-primary btn-block mb-2">
                                @lang('message_event.filter')
                            </button>
                            <a href="{{ route('messageevents.index') }}" class="btn btn-outline-secondary btn-block">
                                @lang('message_event.reset')
                            </a>
                        </div>

                    </div>
                </div>

            </form>
        </div>

        <div class="card-body border-bottom">
            {{-- QUICK FILTER --}}
            <div class="mb-3">

                <span class="text-muted small mr-2">@lang('message_event.quick_filter')</span>

                <a href="{{ route('messageevents.index', array_merge(request()->except('status'), ['status' => 'failed'])) }}"
                    class="btn btn-sm btn-outline-danger mr-1 {{ request('status') === 'failed' ? 'active' : '' }}">
                    ❌ @lang('message_event.status_failed')
                </a>

                <a href="{{ route('messageevents.index', array_merge(request()->except('status'), ['status' => 'pending'])) }}"
                    class="btn btn-sm btn-outline-warning mr-1 {{ request('status') === 'pending' ? 'active' : '' }}">
                    ⏳ @lang('message_event.status_pending')
                </a>

                <a href="{{ route('messageevents.index', array_merge(request()->except('status'), ['status' => 'success'])) }}"
                    class="btn btn-sm btn-outline-success mr-1 {{ request('status') === 'success' ? 'active' : '' }}">
                    ✅ @lang('message_event.status_success')
                </a>

                <a href="{{ route('messageevents.index', request()->except('status')) }}"
                    class="btn btn-sm btn-outline-secondary">
                    🔄 @lang('message_event.status_all')
                </a>

            </div>
        </div>

        {{-- DESKTOP TABLE --}}
        <div class="d-none d-md-block">
            <div class="table-responsive">
                <table class="table table-hover table-bordered mb-0">
                    <thead class="thead-light">
                        <tr>
                            <th>@lang('message_event.th_hash')</th>
                            <th>@lang('message_event.th_channel')</th>
                            <th>@lang('message_event.th_customer')</th>
                            <th>@lang('message_event.th_phone')</th>
                            <th>@lang('message_event.th_message_type')</th>
                            <th>@lang('message_event.th_status')</th>
                            <th>@lang('message_event.th_sent_at')</th>
                            <th>@lang('message_event.th_scheduled_at')</th>
                            <th>@lang('message_event.th_action')</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($events as $i => $event)
                            <tr>
                                <td>{{ $events->firstItem() + $i }}</td>
                                <td><span class="badge badge-info">{{ strtoupper($event->channel) }}</span></td>
                                <td>{{ $event->customer->name ?? '-' }}</td>
                                <td>
                                    @if ($event->customer && $event->customer->contact)
                                        {{ $event->customer->contact }}
                                    @else
                                        @php
                                            // Decode string JSON meta menjadi array
                                            $metaData = is_string($event->meta)
                                                ? json_decode($event->meta, true)
                                                : $event->meta;
                                            // Bersihkan suffix '@s.whatsapp.net' jika ada agar nomornya rapi
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
                                        // Cukup sisakan mapping untuk class warna Bootstrap saja
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
                                {{-- Kolom Sent At --}}
                                <td>{{ $event->sent_at ? $event->sent_at->translatedFormat('d M Y H:i:s') : '-' }}</td>

                                {{-- Kolom Scheduled At --}}
                                <td>
                                    @if ($event->scheduled_at)
                                        {{ $event->scheduled_at->translatedFormat('d M Y H:i:s') }}
                                    @else
                                        <span class="badge badge-secondary shadow-sm">⚡ @lang('message_event.realtime')</span>
                                    @endif
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-outline-primary" data-toggle="collapse"
                                        data-target="#detail-{{ $event->id }}">
                                        <i class="fas fa-eye"></i> @lang('message_event.btn_detail')
                                    </button>

                                    @if ($event->status === 'failed')
                                        <form action="{{ route('messageevents.resend', $event->id) }}" method="POST"
                                            style="display:inline;">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-outline-danger">
                                                <i class="fas fa-redo-alt"></i> @lang('message_event.btn_retry')
                                            </button>
                                        </form>
                                    @endif
                                </td>
                            </tr>

                            <tr class="collapse bg-light" id="detail-{{ $event->id }}">
                                <td colspan="9" class="p-3">
                                    <div class="mb-2">
                                        <strong>@lang('message_event.label_response')</strong><br>
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
                                    @lang('message_event.no_events')
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
                            <span class="badge badge-info">
                                {{ strtoupper($event->channel) }}
                            </span>

                            @php
                                $colorMap = ['success' => 'success', 'failed' => 'danger', 'pending' => 'warning'];
                            @endphp

                            <span class="badge badge-{{ $colorMap[$event->status] ?? 'secondary' }}">
                                {{ __('message_event.db_status.' . $event->status) ?? strtoupper($event->status) }}
                            </span>
                        </div>

                        {{-- Waktu Sent At di Mobile --}}
                        <div class="small text-muted mb-1">
                            {{ $event->sent_at ? $event->sent_at->translatedFormat('d M Y H:i') : '-' }}
                        </div>

                        <strong
                            class="text-break">{{ $event->customer->name ?? __('message_event.unknown_customer') }}</strong><br>

                        {{-- Fallback Kontak di Mobile --}}
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

                        {{-- Bagian Badge Tipe Pengiriman di Mobile --}}
                        <div class="mt-2 d-flex flex-wrap align-items-center" style="gap: 6px;">
                            <span class="badge badge-light text-wrap text-break border text-left"
                                style="max-width: 100%;">
                                {{ $event->message_type }}
                            </span>

                            @if ($event->scheduled_at)
                                <span class="badge badge-light text-muted border">
                                    📅 {{ $event->scheduled_at->translatedFormat('d M Y H:i') }}
                                </span>
                            @else
                                <span class="badge badge-secondary">
                                    ⚡ @lang('message_event.realtime')
                                </span>
                            @endif
                        </div>

                        <button class="btn btn-sm btn-outline-primary mt-2 w-100" data-toggle="collapse"
                            data-target="#detail-mobile-{{ $event->id }}">
                            @lang('message_event.btn_view_detail')
                        </button>

                        <div class="collapse mt-2" id="detail-mobile-{{ $event->id }}">
                            <div class="bg-light p-2 rounded small">

                                <div class="mb-2">
                                    <strong>@lang('message_event.label_response')</strong><br>
                                    <code class="text-break">{{ $event->response_summary }}</code>
                                </div>

                                <div class="text-muted">
                                    <div class="mb-1">
                                        <strong>Trace ID:</strong><br>
                                        <code class="text-break">{{ $event->trace_id ?? '-' }}</code>
                                    </div>

                                    <div>
                                        <strong>Message ID:</strong><br>
                                        <code class="text-break">{{ $event->message_id ?? '-' }}</code>
                                    </div>
                                </div>

                            </div>
                        </div>

                    </div>
                </div>
            @empty
                <div class="text-center text-muted">
                    @lang('message_event.no_events')
                </div>
            @endforelse
        </div>

        <div class="card-footer">
            {{ $events->links() }}
        </div>
    </div>

</x-layouts.cpanel.settings.app-layout>
