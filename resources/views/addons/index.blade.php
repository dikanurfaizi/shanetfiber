<x-layouts.app-layout title="Addons">

    <div class="card-header py-3">
        <h5 class="m-0 fw-bold text-primary mb-2">Addon Management</h5>

        <span>
            @if (isset($message))
                <div class="alert alert-danger">
                    <a href="#" class="close" data-bs-dismiss="alert" aria-label="close">&times;</a>
                    {{ $message }}
                </div>
            @else
                <div class="alert alert-success">
                    <a href="#" class="close" data-bs-dismiss="alert" aria-label="close">&times;</a>
                    {{ $addons->total() }} addons found
                </div>
            @endif
        </span>
    </div>

    <div class="card-body">
        {{ $addons->withQueryString()->links() }}
        <div style="overflow-x: auto;">
            <table id="addonsTable" cellspacing="0" width="100%"
                class="table table-bordered table-striped table-hover">
                <tr class="table-secondary text-center">
                    <th><span class="badge bg-danger-soft text-primary">{{ __('addon.table.no') }}</th>
                    <th><span class="badge bg-danger-soft text-primary">{{ __('addon.table.name') }}</th>
                    <th><span class="badge bg-danger-soft text-primary">{{ __('addon.table.price') }}</th>
                    <th><span class="badge bg-danger-soft text-primary">{{ __('addon.table.is_recurring') }}</th>
                    <th><span class="badge bg-danger-soft text-primary">{{ __('addon.table.status') }}</th>
                    <th width="80px"><span class="badge bg-danger-soft text-primary">{{ __('addon.table.action') }}
                    </th>
                </tr>
                @foreach ($addons as $addon)
                    <tr class="@if ($addon->is_active === 0) text-secondary @else text-primary @endif">
                        <td class="text-center"><span class="badge bg-danger-soft">{{ ++$i }}</span></td>
                        <td>
                            @can('customer-list')
                                <a class="@if ($addon->is_active === 0) text-secondary @else text-primary @endif"
                                    href="#">{{ $addon->name }}</a>
                            @else
                                {{ $addon->name }}
                            @endcan
                        </td>
                        <td class="text-center">
                            <span class="badge bg-danger-soft">
                                {{ number_format($addon->price, 0, ',', '.') }}
                            </span>
                        </td>
                        <td class="text-center">
                            <span class="badge bg-danger-soft">
                                {{ $addon->is_recurring ? __('Yes') : __('No') }}
                            </span>
                        </td>
                        <td class="text-center">
                            <span class="badge {{ $addon->getStatusClass() }} text-white">
                                {{ $addon->getStatusLabel() }}
                            </span>
                        </td>
                        <td class="items-center justify-content-end">
                            @canany(['addon-edit', 'addon-disable', 'addon-enable'])
                                <x-drop-down-action>
                                    @can('addon-edit')
                                        <li>
                                            <a class="dropdown-item" href="{{ route('addons.edit', $addon->slug) }}">
                                                <i class="far fa-edit me-2 text-success"></i>
                                                {{ __('addon.action.edit') }}
                                            </a>
                                        </li>
                                    @endcan
                                    @canany(['addon-disable', 'addon-enable'])
                                        <li>
                                            @if ($addon->is_active)
                                                <a class="dropdown-item" href="#"
                                                    data-url="{{ route('addon.disable', $addon->slug) }}"
                                                    data-action="deactivate" onclick="confirmAddonAction(this)">
                                                    <i class="fa fa-lock me-2 text-danger"></i>
                                                    {{ __('addon.action.disable') }}
                                                </a>
                                            @else
                                                <a class="dropdown-item" href="#"
                                                    data-url="{{ route('addon.enable', $addon->slug) }}" data-action="activate"
                                                    onclick="confirmAddonAction(this)">
                                                    <i class="fa fa-unlock me-2 text-success"></i>
                                                    {{ __('addon.action.enable') }}
                                                </a>
                                            @endif
                                        </li>
                                    @endcanany
                                </x-drop-down-action>
                            @endcanany
                        </td>
                    </tr>
                @endforeach
            </table>
        </div>
        {{ $addons->withQueryString()->links() }}

        @section('custom_scripts')
            <script src="{{ asset('js/addons/index.js') }}"></script>
        @endsection
</x-layouts.app-layout>
