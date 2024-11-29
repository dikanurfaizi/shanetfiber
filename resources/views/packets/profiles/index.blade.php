<x-layouts.app-layout title="PPP Profiles">

    <div class="card-header py-3">
        <h5 class="m-0 font-weight-bold text-primary">{{ __('paket.profile.header.index') }}</h5>
        <span><a class="btn btn-sm btn-success mt-2" href="{{ route('ppp-profiles.create') }}"><i class="fa fa-plus"></i>
                {{ __('paket.profile.button.create') }}</a></span>
    </div>

    <div class="card-body">
        <div style="overflow-x: auto;">
            <table id="paketsTable" cellspacing="0" width="100%"
                class="table datatable-loading no-footer sortable searchable">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>{{ __('paket.profile.table.name') }}</th>
                        <th>{{ __('paket.profile.table.max_upload') }}</th>
                        <th>{{ __('paket.profile.table.max_download') }}</th>
                        <th>{{ __('paket.profile.table.min_upload') }}</th>
                        <th>{{ __('paket.profile.table.min_download') }}</th>
                        <th>{{ __('paket.profile.table.cir') }}</th>
                        <th>{{ __('paket.profile.table.priority') }}</th>
                        <th>{{ __('paket.profile.table.server') }}</th>
                        <th class="text-end">{{ __('paket.table.action') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($profiles as $profile)
                        <tr>
                            <td><span class="badge rounded-pill text-secondary">{{ ++$i }}</span></td>
                            <td><span class="badge text-primary">{{ $profile->profile_name }}</span></td>
                            <td><span class="badge text-primary">{{ $profile->max_upload }}</span></td>
                            <td><span class="badge text-primary">{{ $profile->max_download }}</span></td>
                            <td><span class="badge text-primary">{{ $profile->min_upload }}</span></td>
                            <td><span class="badge text-primary">{{ $profile->min_download }}</span></td>
                            <td>
                                <span class="badge text-primary">
                                    @if ($profile->rasio_cir)
                                        1 : {{ $profile->rasio_cir }}
                                    @endif
                                </span>
                            </td>
                            <td><span class="badge text-primary">{{ $profile->priority }}</span></td>
                            <td><span class="badge text-primary">{{ $profile->mikrotik->name }}</span></td>

                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        {{ $profiles->links() }}
    </div>
</x-layouts.app-layout>
