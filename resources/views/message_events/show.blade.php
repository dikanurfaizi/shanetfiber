<x-layouts.cpanel.settings.app-layout :title="__('message_event.app_title')">

    @section('custom_styles')
        <style>
            /* BASE CHAT CONTAINER */
            .chat-bg {
                width: 100% !important;
                max-width: 100% !important;
                padding: 20px;
                border-radius: 8px;
                display: flex;
                flex-direction: column;
                align-items: flex-end;
                /* Posisi pesan terkirim di KANAN */
                min-height: 420px;
                box-sizing: border-box;
            }

            /* WHATSAPP THEME */
            .wa-bg {
                background-color: #efeae2 !important;
            }

            .wa-bubble-outgoing {
                background-color: #d9fdd3 !important;
                border-radius: 8px 0px 8px 8px;
                box-shadow: 0 1px 1px rgba(11, 20, 26, 0.13);
                color: #111b21;
            }

            .wa-time {
                color: #667781;
            }

            .wa-time .fa-check-double {
                color: #53bdeb;
            }

            /* TELEGRAM THEME (PERBAIKAN BACKGROUND GRADASI) */
            .tg-bg {
                /* Menggabungkan pattern bintik dan gradasi hijau agar tidak saling menimpa */
                background:
                    radial-gradient(rgba(0, 0, 0, 0.08) 1px, transparent 0),
                    linear-gradient(180deg, #9bbd96 0%, #7ca177 100%) !important;
                background-size: 16px 16px, 100% 100% !important;
            }

            .tg-bubble-outgoing {
                background-color: #ffffff !important;
                /* Bubble warna PUTIH */
                border-radius: 14px 14px 2px 14px;
                box-shadow: 0 1px 2px rgba(0, 0, 0, 0.15);
                color: #000000;
            }

            .tg-time {
                color: #8393a3;
            }

            .tg-time .fa-check,
            .tg-time .fa-check-double {
                color: #4fae4e;
            }

            /* COMMON BUBBLE STYLE */
            .chat-bubble {
                padding: 10px 14px 22px 14px;
                font-size: 14px;
                line-height: 1.45;
                position: relative;
                word-wrap: break-word;
                max-width: 82%;
            }

            .chat-body {
                white-space: pre-wrap;
                font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            }

            .chat-time {
                font-size: 11px;
                position: absolute;
                bottom: 4px;
                right: 10px;
                display: flex;
                align-items: center;
                gap: 3px;
            }
        </style>
    @endsection

    <div class="card-header py-3 d-flex justify-content-between align-items-center">
        <h5 class="m-0 fw-bold text-primary">@lang('message_event.header.technical_log_detail')</h5>

        <div class="d-flex align-items-center" style="gap: 8px;">
            @if ($event->status === 'failed')
                <form action="{{ route('messageevents.resend', $event->id) }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-danger btn-sm">
                        <i class="fas fa-redo-alt me-1"></i> @lang('message_event.button.btn_retry')
                    </button>
                </form>
            @endif

            <a href="{{ route('messageevents.index') }}" class="btn btn-sm btn-outline-secondary">
                <i class="fa fa-arrow-left me-1"></i> @lang('message_event.button.back')
            </a>
        </div>
    </div>

    <div class="row mt-3">
        {{-- METADATA PANEL --}}
        <div class="col-12 col-lg-4 mb-3">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-white fw-bold">
                    <i class="fas fa-info-circle text-primary me-1"></i> @lang('message_event.sub_title.event_metada')
                </div>
                <div class="card-body p-0">
                    <table class="table table-sm table-striped mb-0">
                        <tbody>
                            <tr>
                                <th class="ps-3 text-muted w-40">@lang('message_event.label.channel')</th>
                                <td><span class="badge bg-info">{{ strtoupper($event->channel) }}</span></td>
                            </tr>
                            <tr>
                                <th class="ps-3 text-muted">@lang('message_event.label.status')</th>
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
                            </tr>
                            <tr>
                                <th class="ps-3 text-muted">@lang('message_event.label.message_type')</th>
                                <td><code>{{ $event->message_type }}</code></td>
                            </tr>
                            <tr>
                                <th class="ps-3 text-muted">@lang('message_event.label.customer')</th>
                                <td><strong>{{ $event->customer->name ?? __('message_event.label.unknown_customer') }}</strong>
                                </td>
                            </tr>
                            <tr>
                                <th class="ps-3 text-muted">@lang('message_event.label.contact')</th>
                                <td>{{ $event->customer->contact ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th class="ps-3 text-muted">@lang('message_event.label.sent_at')</th>
                                <td>{{ $event->sent_at ? $event->sent_at->translatedFormat('d M Y H:i:s') : '-' }}</td>
                            </tr>
                            <tr>
                                <th class="ps-3 text-muted">@lang('message_event.label.trace_id')</th>
                                <td><small><code class="text-break">{{ $event->trace_id ?? '-' }}</code></small></td>
                            </tr>
                            <tr>
                                <th class="ps-3 text-muted">@lang('message_event.label.message_id')</th>
                                <td><small><code class="text-break">{{ $event->message_id ?? '-' }}</code></small></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- CHAT PREVIEW & JSON LOG --}}
        <div class="col-12 col-lg-8 mb-3">
            @php
                $isTelegram = strtolower($event->channel) === 'telegram';
                $bgClass = $isTelegram ? 'tg-bg' : 'wa-bg';
                $bubbleClass = $isTelegram ? 'tg-bubble-outgoing' : 'wa-bubble-outgoing';
                $timeClass = $isTelegram ? 'tg-time' : 'wa-time';
            @endphp

            {{-- CHAT BUBBLE PREVIEW --}}
            <div class="card shadow-sm mb-3">
                <div class="card-header bg-white fw-bold">
                    @if ($isTelegram)
                        <i class="fab fa-telegram text-info me-1"></i> @lang('message_event.sub_title.telegram_preview')
                    @else
                        <i class="fab fa-whatsapp text-success me-1"></i> @lang('message_event.sub_title.whatsapp_preview')
                    @endif
                </div>

                <div class="card-body p-2">
                    <div class="chat-bg {{ $bgClass }}">
                        <div class="chat-bubble {{ $bubbleClass }} shadow-sm">
                            {{-- PESAN HASIL PARSING --}}
                            <div class="chat-body">{!! $event->extracted_message !!}</div>

                            {{-- JAM & INDIKATOR TERKIRIM --}}
                            <div class="chat-time {{ $timeClass }}">
                                <span>{{ $event->sent_at ? $event->sent_at->format('H:i') : now()->format('H:i') }}</span>
                                @if ($event->status === 'success')
                                    <i class="fas fa-check-double"></i>
                                @elseif ($event->status === 'failed')
                                    <i class="fas fa-exclamation-circle text-danger"></i>
                                @else
                                    <i class="far fa-clock"></i>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- TECHNICAL LOG (JSON) --}}
            <div class="card shadow-sm">
                <div class="card-header bg-white fw-bold">
                    <i class="fas fa-code text-dark me-1"></i> @lang('message_event.sub_title.technical_log_json')
                </div>
                <div class="card-body">
                    <div class="mb-3 mb-3">
                        <label class="small fw-bold text-muted">Request Payload:</label>
                        <pre class="bg-dark text-light p-3 rounded" style="max-height: 200px; overflow-y: auto; font-size: 12px;"><code>{{ $event->formatted_request }}</code></pre>
                    </div>

                    <div class="mb-3 mb-0">
                        <label class="small fw-bold text-muted">Response Message:</label>
                        <pre class="bg-dark text-light p-3 rounded" style="max-height: 200px; overflow-y: auto; font-size: 12px;"><code>{{ $event->formatted_response }}</code></pre>
                    </div>
                </div>
            </div>
        </div>
    </div>

</x-layouts.cpanel.settings.app-layout>
