<x-layouts.cpanel.settings.app-layout title="Brand">

    <div class="card-header py-3">
        <h5 class="m-0 font-weight-bold text-primary">{{ __('router.header.index-brand') }}</h5>
        <span><a class="btn btn-sm btn-success mt-2" href="{{ route('brand.create') }}"><i class="fa fa-plus"></i> {{ __('router.button.create-brand') }}</a></span>
    </div>

    <div class="card-body">
        <table id="ontsTable" cellspacing="0" width="100%" class="table datatable-loading no-footer sortable searchable">
            <thead>
                <tr>
                    <th>#</th>
                    <th>{{ __('router.table.merk') }}</th>
                    <th class="text-center">{{ __('router.table.action') }}</th>
                </tr>
            </thead>
            <tbody>

                @foreach($brands as $brand)
                <tr>
                    <td><span class="badge rounded-pill text-secondary">{{$loop->iteration}}</span></td>
                    <td><span class="badge text-primary">{{ $brand->name}}</span></td>

                    <td class="d-flex items-center justify-content-center">

                        @can ('server-edit')
                        <a href="{{route('brand.edit',$brand->id)}}" data-bs-toggle="tooltip" data-bs-placement="top" title="Edit Merk Router" class="btn btn-datatable btn-icon btn-transparent-dark me-2 router-edit">
                            <i class="fas fa-edit text-primary"></i>
                        </a>
                        @endcan
                        @can ('server-delete')
                        <a href="{{route('brand.delete',$brand->id)}}" class="btn btn-datatable btn-icon btn-transparent-dark me-2" data-bs-placement="top" title="Hapus Merk Router" id="btn-hapus-router" type="button">
                            <i class="far fa-trash-alt text-danger"></i>
                        </a>
                        @endcan

                    </td>
                </tr>
                @endforeach

            </tbody>
        </table>
        <a class="btn btn-sm btn-secondary" href="{{ route('routers.index') }}"><i class="fa fa-arrow-left"></i>&nbsp{{ __('router.button.back') }}</a>
    </div>
</x-layouts.cpanel.settings.app-layout>