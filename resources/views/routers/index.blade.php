<x-layouts.cpanel.settings.app-layout title="Routers">

    <div class="card-header py-3">
        <h5 class="m-0 font-weight-bold text-primary">{{ __('router.header.index') }}</h5>
        <span><a class="btn btn-sm btn-success mt-2" href="{{ route('routers.create') }}"><i class="fa fa-plus"></i> {{ __('router.button.create') }}</a></span>
    </div>

    <div class="card-body">
        <table id="ontsTable" cellspacing="0" width="100%" class="table datatable-loading no-footer sortable searchable">
            <thead>
                <tr>
                    <th>#</th>
                    <th>{{ __('router.table.merk') }}</th>
                    <th>{{ __('router.table.type') }}</th>
                    <th>{{ __('router.table.server-type') }}</th>
                    <th class="text-center">{{ __('router.table.action') }}</th>
                </tr>
            </thead>
            <tbody>

                @foreach($routers as $router)
                <tr>
                    <td><span class="badge rounded-pill text-secondary">{{$loop->iteration}}</span></td>
                    <td><span class="badge text-primary">{{ $router->merk_router->name}}</span></td>
                    <td><span class="badge text-primary">{{ $router->name}}</span></td>
                    <td>
                        @if ($router->server_type === 'true')
                        <span class="badge text-white bg-success">
                            <i class="fas fa-server"></i> {{ __('router.server-type') }}
                        </span>
                        @endif
                        @if ($router->server_type === 'false')
                        <span class="badge text-white bg-primary">
                            <i class="fas fa-wifi"></i> {{ __('router.modem-type') }}
                        </span>
                        @endif
                    </td>
                    <td class="d-flex items-center justify-content-center">
                        @can ('server-edit')
                        <a href="{{route('routers.edit',$router->id)}}" data-bs-toggle="tooltip" data-bs-placement="top" title="Edit Router" class="btn btn-datatable btn-icon btn-transparent-dark me-2 router-edit">
                            <i class="fas fa-edit text-primary"></i>
                        </a>
                        @endcan
                        @can ('server-delete')
                        <a href="{{route('router.delete',$router->id)}}" class="btn btn-datatable btn-icon btn-transparent-dark me-2" data-bs-placement="top" title="Hapus Router" id="btn-hapus-router" type="button">
                            <i class="far fa-trash-alt text-danger"></i>
                        </a>
                        @endcan

                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        <a class="btn btn-sm btn-primary float-right" href="{{ route('brand.index') }}"><i class="fa fa-server"></i>&nbsp{{ __('router.button.show-brand-router') }}</a>

    </div>
</x-layouts.cpanel.settings.app-layout>