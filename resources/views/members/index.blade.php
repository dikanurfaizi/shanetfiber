<x-layouts.app-layout title="Memberships">
    <div class="card-header py-3">
        <h5 class="m-0 fw-bold text-primary mb-2">
            {{ __('member.header.index') }}
        </h5>

        <span>
            @if (isset($message))
                <div class="alert alert-danger">
                    <a href="#" class="close" data-bs-dismiss="alert" aria-label="close">&times;</a>
                    {{ $message }}
                </div>
            @else
                <div class="alert alert-success">
                    <a href="#" class="close" data-bs-dismiss="alert" aria-label="close">&times;</a>
                    {{ $members->total() }} membership found
                </div>
            @endif
        </span>
    </div>

    <div class="card-body">
        {{ $members->withQueryString()->links() }}
        <div style="overflow-x: auto;">
            <table id="memberssTable" cellspacing="0" width="100%"
                class="table table-bordered table-striped table-hover">
                <tr class="table-secondary text-center">
                    <th><span class="badge bg-danger-soft text-primary">{{ __('member.table.no') }}</span></th>
                    <th><span class="badge bg-danger-soft text-primary">{{ __('member.table.name') }}</span></th>
                    <th><span class="badge bg-danger-soft text-primary">{{ __('member.table.discount_type') }}</span>
                    </th>
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
                                    href="#">{{ $member->name }}</a>
                            @else
                                {{ $member->name }}
                            @endcan
                        </td>
                        <td class="text-center">
                            <span class="badge bg-danger-soft">
                                {{ $member->discount_type_label }}
                            </span>
                        </td>
                        <td class="text-center">
                            <span class="badge bg-danger-soft">
                                {{ $member->formatted_discount }}
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
                                            @if ($member->is_active)
                                                <a class="dropdown-item" href="javascript:void(0)"
                                                    onclick="confirmAction('{{ route('members.deactivate', $member->slug) }}', 'deactivate')">
                                                    <i class="fa fa-lock me-2 text-danger }}"></i>
                                                    &nbsp;{{ __('member.action.deactivate') }}
                                                </a>
                                            @else
                                                <a class="dropdown-item" href="javascript:void(0)"
                                                    onclick="confirmAction('{{ route('members.activate', $member->slug) }}', 'activate')">
                                                    <i class="fa fa-unlock me-2 text-success }}"></i>
                                                    &nbsp;{{ __('member.action.activate') }}
                                                </a>
                                            @endif
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

    @section('custom_scripts')
        <script>
            function confirmAction(url, action) {
                Swal.fire({
                    title: `Are you sure you want to ${action} this member?`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: action === 'deactivate' ? '#d33' : '#3085d6',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: `Yes, ${action}!`
                }).then(result => {
                    if (result.isConfirmed) {
                        fetch(url, {
                                method: 'PATCH',
                                headers: {
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                    'Content-Type': 'application/json'
                                },
                                body: '{}' // body kosong
                            })
                            .then(response => response.json())
                            .then(data => {
                                if (data.success) {
                                    Swal.fire('Success!', data.message, 'success')
                                        .then(() => location.reload());
                                } else {
                                    Swal.fire('Error!', data.message, 'error');
                                }
                            })
                            .catch(() => {
                                Swal.fire('Error!', 'Something went wrong, please try again.', 'error');
                            });
                    }
                });
            }
        </script>
    @endsection

</x-layouts.app-layout>
