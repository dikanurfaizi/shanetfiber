<x-layouts.cpanel.settings.app-layout title="WhatsApp Cloud Templates">

    <div class="card-header py-3 d-flex justify-content-between align-items-center">
        <h5 class="m-0 fw-bold text-primary">
            WhatsApp Cloud Templates
        </h5>

        <form action="{{ route('whatsapptemplate.sync') }}" method="POST" class="d-inline">
            @csrf
            <button type="submit" class="btn btn-sm btn-success">
                <i class="fa fa-sync"></i> Sync All Template from Meta
            </button>
        </form>
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
                        <th>Name</th>
                        <th>Language</th>
                        <th>Category</th>
                        <th>Status</th>
                        <th>Components</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>

                    @foreach ($templates as $i => $tpl)
                        <tr>
                            <td><span class="badge rounded-pill">{{ $i + 1 }}</span></td>

                            <td>
                                <span class="badge bg-primary text-white">
                                    {{ $tps->name }}
                                </span>
                            </td>

                            <td>{{ $tps->language }}</td>

                            <td>
                                <span class="badge bg-info text-white">
                                    {{ $tps->category }}
                                </span>
                            </td>

                            <td>
                                @if ($tps->isApproved())
                                    <span class="badge bg-success text-white">APPROVED</span>
                                @else
                                    <span class="badge bg-warning text-white">{{ $tps->status }}</span>
                                @endif
                            </td>

                            <td>{{ $tps->components->count() }}</td>

                            <td class="text-end">
                                <x-drop-down-action>
                                    <li>
                                        <a class="dropdown-item" href="{{ route('whatsapptemplate.show', $tps->id) }}">
                                            <i class="far fa-eye me-2 text-success"></i> Preview
                                        </a>
                                    </li>
                                    <li>
                                        <form method="POST"
                                            action="{{ route('whatsapptemplate.sync.single', $tps->id) }}">
                                            @csrf
                                            <button class="dropdown-item" type="submit">
                                                <i class="fa fa-sync me-2 text-primary"></i> Sync
                                            </button>
                                        </form>
                                    </li>
                                </x-drop-down-action>
                            </td>
                        </tr>
                    @endforeach

                </tbody>
            </table>
        </div>

        <div class="d-block d-md-none">

            @foreach ($templates as $i => $tpl)
                <div class="card mb-3 shadow-sm">

                    <div class="card-body">

                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <strong>{{ $tps->name }}</strong>
                            <span class="badge bg-secondary text-white">{{ $tps->language }}</span>
                        </div>

                        <div class="mb-1">
                            Category:
                            <span class="badge bg-info text-white">{{ $tps->category }}</span>
                        </div>

                        <div class="mb-1">
                            Status:
                            @if ($tps->isApproved())
                                <span class="badge bg-success text-white">APPROVED</span>
                            @else
                                <span class="badge bg-warning text-white">{{ $tps->status }}</span>
                            @endif
                        </div>

                        <div class="mb-2">
                            Components:
                            <strong>{{ $tps->components->count() }}</strong>
                        </div>

                        <div class="d-flex justify-content-end">
                            <a href="{{ route('whatsapptemplate.show', $tps->id) }}"
                                class="btn btn-sm btn-outline-success me-2">
                                <i class="far fa-eye"></i> Preview
                            </a>

                            <form method="POST" action="{{ route('whatsapptemplate.sync.single', $tps->id) }}">
                                @csrf
                                <button class="btn btn-sm btn-outline-primary" type="submit">
                                    <i class="fa fa-sync me-1"></i> Sync
                                </button>
                            </form>
                        </div>

                    </div>

                </div>
            @endforeach

        </div>

        {{ $templates->links() }}
    </div>

</x-layouts.cpanel.settings.app-layout>
