<x-layouts.cpanel.customers.app-layout title="Members">
    <div class="card-header py-3">
        <h5 class="m-0 font-weight-bold text-primary mb-2">
            {{ __('member.header.index') }}
        </h5>

        <span>
            @if (isset($message))
                <div class="alert alert-danger">
                    <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
                    {{ $message }}
                </div>
            @else
                <div class="alert alert-success">
                    <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
                    {{ $members->total() }} member type found
                </div>
            @endif
        </span>
    </div>

    <div class="card-header py-3">
        @can('customer-create')
            <span><a class="btn btn-sm btn-success mt-2" href="{{ route('members.create') }}"><i class="fa fa-plus"></i>
                    Add New Member Type </a></span>
        @endcan
    </div>

    <div class="card-body">
        {{ $members->withQueryString()->links() }}
        <div style="overflow-x: auto;">
            <table id="memberssTable" cellspacing="0" width="100%"
                class="table table-bordered table-striped table-hover">
                <tr class="table-secondary text-center">
                    <th><span class="badge bg-danger-soft text-primary">{{ __('member.table.no') }}</span></th>
                    <th><span class="badge bg-danger-soft text-primary">{{ __('member.table.name') }}</span></th>
                    <th><span class="badge bg-danger-soft text-primary">{{ __('member.table.discount') }}</span></th>
                    <th><span class="badge bg-danger-soft text-primary">{{ __('member.table.status') }}</span></th>
                    <th width="80px"><span
                            class="badge bg-danger-soft text-primary">{{ __('member.table.action') }}</span></th>
                </tr>
                @foreach ($members as $key => $member)
                    <tr class="@if ($member->status === 0) text-secondary @else text-primary @endif">
                        <td class="text-center"><span class="badge bg-danger-soft">{{ ++$i }}</span></td>
                        <td>
                            @can('customer-list')
                                <a class="@if ($member->status === 0) text-secondary @else text-primary @endif"
                                    href="#">{{ $member->type }}</a>
                            @else
                                {{ $member->type }}
                            @endcan
                        </td>
                        <td class="text-center">
                            <span class="badge bg-danger-soft">
                                {{ number_format($member->discount, 2, ',', '.') }}
                            </span>
                        </td>
                        <td class="text-center">
                            <span class="badge {{ $member->getStatusClass() }} text-white">
                                {{ $member->getStatusLabel() }}
                            </span>
                        </td>
                        <td class="items-center justify-content-end">
                            @canany(['customer-edit', 'secret-edit', 'customer-delete'])
                                <x-drop-down-action>
                                    @can('customer-edit')
                                        <li>
                                            <a class="dropdown-item" href="{{ route('members.edit', $member->slug) }}">
                                                <i class="far fa-edit me-2 text-success"></i>
                                                {{ __('member.action.edit') }}
                                            </a>
                                        </li>
                                    @endcan
                                    @can('customer-delete')
                                        <li>
                                            <a class="dropdown-item" href="javascript:void(0)"
                                                onclick="confirmAction('{{ route('members.update', $member->slug) }}', '{{ $member->status === 1 ? 'deactivate' : 'activate' }}')">
                                                <i
                                                    class="fa {{ $member->status === 1 ? 'fa-lock' : 'fa-unlock' }} me-2 text-{{ $member->status === 1 ? 'danger' : 'success' }}"></i>
                                                &nbsp;{{ $member->status === 1 ? __('member.action.deactivate') : __('member.action.activate') }}
                                            </a>
                                        </li>
                                    @endcan
                                </x-drop-down-action>
                            @endcanany
                        </td>
                    </tr>
                @endforeach
            </table>
        </div>
        {{ $members->withQueryString()->links() }}
    </div>
</x-layouts.cpanel.customers.app-layout>

<script>
    function confirmAction(url, action) {
        Swal.fire({
            title: `Are you sure you want to ${action} this member?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: action === 'deactivate' ? '#d33' : '#3085d6',
            cancelButtonColor: '#6c757d',
            confirmButtonText: `Yes, ${action}!`
        }).then((result) => {
            if (result.isConfirmed) {
                fetch(url, {
                        method: 'PUT',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            update_status: true
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire(
                                'Success!',
                                data.message,
                                'success'
                            ).then(() => {
                                location.reload();
                            });
                        } else {
                            Swal.fire(
                                'Error!',
                                data.message,
                                'error'
                            );
                        }
                    })
                    .catch(() => {
                        Swal.fire(
                            'Error!',
                            'Something went wrong, please try again later.',
                            'error'
                        );
                    });
            }
        });
    }
</script>
