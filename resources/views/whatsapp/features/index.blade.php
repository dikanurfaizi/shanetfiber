<x-layouts.cpanel.settings.app-layout title="WhatsApp Cloud Templates">

    <div class="card-header py-3">
        <div class="d-flex justify-content-between align-items-start flex-wrap">

            {{-- Left: Title + Description --}}
            <div class="mb-2 mb-md-0">
                <h5 class="m-0 font-weight-bold text-primary">
                    {{ __('whatsapp.feature.title.index') }}
                </h5>

                <div class="mt-1 text-muted" style="font-size: 0.95rem;">
                    {{ __('whatsapp.feature.description.index-information') }}
                </div>
            </div>

            {{-- Right: Search + Create --}}
            <div class="d-flex align-items-center mt-2 mt-md-0">

                {{-- Search --}}
                <form method="GET" action="{{ route('whatsappfeature.index') }}" class="mr-2">
                    <div class="input-group input-group-sm">
                        <input type="text" name="q" value="{{ request('q') }}" class="form-control"
                            placeholder="{{ __('whatsapp.feature.placeholder.search-feature') }}">
                        <div class="input-group-append">
                            <button class="btn btn-outline-secondary" type="submit">
                                <i class="fa fa-search"></i>
                            </button>
                        </div>
                    </div>
                </form>

                {{-- Create Button --}}
                <a href="{{ route('whatsappfeature.create') }}" class="btn btn-sm btn-primary">
                    <i class="fa fa-plus mr-1"></i>
                    {{ __('whatsapp.feature.button.create') }}
                </a>

            </div>
        </div>
    </div>

    <div class="card-body">
        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        <div class="d-none d-md-block" style="overflow-x:auto;">
            <table class="table datatable-loading sortable searchable">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>{{ __('whatsapp.feature.label.name') }}</th>
                        <th>{{ __('whatsapp.feature.label.gateway') }}</th>
                        <th>{{ __('whatsapp.feature.label.status') }}</th>
                        <th>{{ __('whatsapp.feature.label.schedule-status') }}</th>
                        <th class="text-end">{{ __('whatsapp.feature.label.actions') }}</th>
                    </tr>
                </thead>
                <tbody>

                    @foreach ($features as $i => $feature)
                        <tr>
                            <td><span class="badge rounded-pill">{{ $i + 1 }}</span></td>

                            <td>
                                @if ($feature->is_active)
                                    <span class="badge bg-primary text-white">
                                        {{ $feature->name }}
                                    </span>
                                @else
                                    <span class="badge bg-danger text-white">
                                        {{ $feature->name }}
                                    </span>
                                @endif
                            </td>

                            <td>{{ $feature->gateway_label }}</td>
                            <td>
                                @if ($feature->is_active)
                                    <span class="badge bg-info text-white">
                                        {{ __('whatsapp.feature.status.active') }}
                                    </span>
                                @else
                                    <span class="badge bg-danger text-white">
                                        {{ __('whatsapp.feature.status.inactive') }}
                                    </span>
                                @endif
                            </td>

                            <td>
                                @if ($feature->is_scheduled)
                                    <span class="badge bg-success text-white">
                                        {{ $feature->schedule_summary }}
                                    </span>
                                @else
                                    <span class="badge bg-secondary text-white">
                                        {{ __('whatsapp.feature.schedule-status.not-scheduled') }}
                                    </span>
                                @endif
                            </td>

                            <td class="text-end">
                                <x-drop-down-action>
                                    <li>
                                        <a class="dropdown-item"
                                            href="{{ route('whatsappfeature.show', $feature->slug) }}">
                                            <i class="far fa-eye me-2 text-success"></i>
                                            {{ __('whatsapp.feature.button.detail') }}
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item"
                                            href="{{ route('whatsappfeature.edit', $feature->slug) }}">
                                            <i class="fa fa-edit me-2 text-primary"></i>
                                            {{ __('whatsapp.feature.button.edit') }}
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item text-danger"
                                            href="{{ route('whatsappfeature.delete', $feature->slug) }}">
                                            <i class="fa fa-trash mr-2"></i>
                                            {{ __('whatsapp.feature.button.delete-feature') }}
                                        </a>
                                    </li>
                                </x-drop-down-action>
                            </td>
                        </tr>
                    @endforeach

                </tbody>
            </table>
        </div>

        <div class="d-block d-md-none">

            @foreach ($features as $i => $feature)
                <div class="card mb-3 shadow-sm">

                    <div class="card-body">

                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <strong>{{ $feature->name }}</strong>
                            <span class="badge bg-secondary text-white">{{ $feature->gateway_label }}</span>
                        </div>

                        <div class="mb-1">
                            {{ __('whatsapp.feature.label.status') }}:
                            <span
                                class="badge bg-info text-white">{{ $feature->is_active ? 'Active' : 'Inactive' }}</span>
                        </div>

                        <div class="mb-2">
                            {{ __('whatsapp.feature.label.schedule') }}:
                            <div class="small text-muted">
                                {{ $feature->schedule_summary }}
                            </div>
                        </div>

                        <div class="d-flex justify-content-end">
                            <a href="{{ route('whatsappfeature.show', $feature->slug) }}"
                                class="btn btn-sm btn-outline-success mr-2">
                                <i class="far fa-eye"></i> {{ __('whatsapp.feature.button.detail') }}
                            </a>

                            <a href="{{ route('whatsappfeature.edit', $feature->slug) }}"
                                class="btn btn-sm btn-outline-primary mr-2">
                                <i class="fa fa-edit mr-1"></i> {{ __('whatsapp.feature.button.edit') }}
                            </a>

                            <form method="POST" action="{{ route('whatsappfeature.delete', $feature->slug) }}">
                                @csrf
                                <button class="btn btn-sm btn-outline-danger" type="submit">
                                    <i class="fa fa-trash mr-1"></i> {{ __('whatsapp.feature.button.delete-feature') }}
                                </button>
                            </form>
                        </div>

                    </div>

                </div>
            @endforeach

        </div>

        {{ $features->links() }}
    </div>

</x-layouts.cpanel.settings.app-layout>
