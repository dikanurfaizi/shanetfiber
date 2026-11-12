<x-layouts.mikrotiks.app-layout title="PPP Secrets">

    <div class="card-header py-3">
        <h5 class="m-0 fw-bold text-primary">script.header.index</h5>
    </div>

    <div class="card-body">
        @if ($errors->any())
            <div class="mb-3 mt-4 mb-4">
                <div class="pull-right alert alert-danger">
                    <strong>Whoops!</strong> There were some problems with your input.<br><br>
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif

        <table id="scriptsTable" cellspacing="0" width="100%"
            class="table datatable-loading no-footer sortable searchable">
            <thead>
                <tr>
                    <th width="40">#</th>
                    <th>{{ __('script.table.name') }}</th>
                    <th>{{ __('script.table.source') }}</th>
                </tr>
            </thead>
            <tbody>

                @foreach ($scripts as $script)
                    <tr>
                        <td><span
                                class="badge rounded-pill bg-secondary-soft text-secondary">{{ $loop->iteration }}</span>
                        </td>
                        <td><span class="badge bg-blue-soft text-primary">{{ $script['name'] }}</span></td>
                        <td><span class="badge bg-blue-soft text-primary">{{ $script['source'] }}</span></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</x-layouts.mikrotiks.app-layout>
