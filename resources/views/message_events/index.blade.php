<x-layouts.cpanel.settings.app-layout title="WhatsApp Cloud Templates">

    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Message Events Log</h5>
        </div>

        <div class="card-body border-bottom">
            <form method="GET" action="{{ route('messageevents.index') }}">

                {{-- ROW 1: INPUT FILTER --}}
                <div class="form-row">
                    <div class="col-12 col-md-3 mb-2">
                        <input type="text" name="q" class="form-control" placeholder="Nama / Nomor"
                            value="{{ request('q') }}">
                    </div>

                    <div class="col-6 col-md-2 mb-2">
                        <input type="date" name="date" class="form-control" value="{{ request('date') }}">
                    </div>

                    <div class="col-6 col-md-2 mb-2">
                        <input type="time" name="time_from" class="form-control" value="{{ request('time_from') }}">
                    </div>

                    <div class="col-6 col-md-2 mb-2">
                        <input type="time" name="time_to" class="form-control" value="{{ request('time_to') }}">
                    </div>
                </div>

                {{-- ROW 2: BUTTON --}}
                <div class="form-row">
                    <div class="col-12 text-left">

                        {{-- Desktop & Tablet --}}
                        <div class="d-none d-md-inline-block">
                            <button class="btn btn-primary mr-2">
                                Filter
                            </button>
                            <a href="{{ route('messageevents.index') }}" class="btn btn-outline-secondary">
                                Reset
                            </a>
                        </div>

                        {{-- Mobile --}}
                        <div class="d-block d-md-none">
                            <button class="btn btn-primary btn-block mb-2">
                                Filter
                            </button>
                            <a href="{{ route('messageevents.index') }}" class="btn btn-outline-secondary btn-block">
                                Reset
                            </a>
                        </div>

                    </div>
                </div>

            </form>
        </div>

        {{-- DESKTOP TABLE --}}
        <div class="d-none d-md-block">
            <div class="table-responsive">
                <table class="table table-hover table-bordered mb-0">
                    <thead class="thead-light">
                        <tr>
                            <th>#</th>
                            <th>Channel</th>
                            <th>Customer</th>
                            <th>Phone</th>
                            <th>Message Type</th>
                            <th>Status</th>
                            <th>Sent At</th>
                            <th>Action</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($events as $i => $event)
                            <tr>
                                <td>{{ $events->firstItem() + $i }}</td>
                                <td><span class="badge badge-info">{{ strtoupper($event->channel) }}</span></td>
                                <td>{{ $event->customer->name ?? '-' }}</td>
                                <td>{{ $event->customer->contact ?? '-' }}</td>
                                <td>{{ $event->message_type }}</td>
                                <td>
                                    @php
                                        $map = ['success' => 'success', 'failed' => 'danger', 'pending' => 'warning'];
                                    @endphp
                                    <span class="badge badge-{{ $map[$event->status] ?? 'secondary' }}">
                                        {{ strtoupper($event->status) }}
                                    </span>
                                </td>
                                <td>{{ optional($event->sent_at)->format('d M Y H:i:s') }}</td>
                                <td>
                                    <button class="btn btn-sm btn-outline-primary" data-toggle="collapse"
                                        data-target="#detail-{{ $event->id }}">
                                        Detail
                                    </button>
                                </td>
                            </tr>

                            <tr class="collapse bg-light" id="detail-{{ $event->id }}">
                                <td colspan="8" class="p-3">
                                    <strong>Response:</strong>
                                    <code>{{ $event->response_summary }}</code>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted">
                                    No message events found
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
                                $map = ['success' => 'success', 'failed' => 'danger', 'pending' => 'warning'];
                            @endphp

                            <span class="badge badge-{{ $map[$event->status] ?? 'secondary' }}">
                                {{ strtoupper($event->status) }}
                            </span>
                        </div>

                        <div class="small text-muted mb-1">
                            {{ optional($event->sent_at)->format('d M Y H:i') }}
                        </div>

                        <strong>{{ $event->customer->name ?? 'Unknown Customer' }}</strong><br>
                        <span class="text-muted">{{ $event->customer->contact ?? '-' }}</span>

                        <div class="mt-1">
                            <span class="badge badge-light">
                                {{ $event->message_type }}
                            </span>
                        </div>

                        <button class="btn btn-sm btn-outline-primary mt-2 w-100" data-toggle="collapse"
                            data-target="#detail-mobile-{{ $event->id }}">
                            Lihat Detail
                        </button>

                        <div class="collapse mt-2" id="detail-mobile-{{ $event->id }}">
                            <div class="bg-light p-2 rounded small">
                                <strong>Response:</strong><br>
                                <code>{{ $event->response_summary }}</code>
                            </div>
                        </div>

                    </div>
                </div>
            @empty
                <div class="text-center text-muted">
                    No message events found
                </div>
            @endforelse
        </div>

        <div class="card-footer">
            {{ $events->links() }}
        </div>
    </div>

</x-layouts.cpanel.settings.app-layout>
