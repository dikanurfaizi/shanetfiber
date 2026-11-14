<x-layouts.mikrotiks.app-layout title="PPP Secrets">

    <div class="card-header py-3">
        <h5 class="m-0 fw-bold text-primary">PPP Secrets</h5>
        <span><a class="btn btn-sm btn-success mt-2" href="{{ route('ppp.secret.create', [$mikrotik->slug]) }}"><i
                    class="fa fa-plus"></i> Add User Secret</a></span>
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
        <table id="secretsTable" cellspacing="0" width="100%"
            class="table datatable-loading no-footer sortable searchable">
            <thead>
                <tr>
                    <th>#</th>
                    <th>{{ __('pppoe.secret.name') }}</th>
                    <th>{{ __('pppoe.secret.password') }}</th>
                    <th>{{ __('pppoe.secret.profile') }}</th>
                    <th>{{ __('pppoe.secret.local-addr') }}</th>
                    <th>{{ __('pppoe.secret.remote-addr') }}</th>
                    <th>{{ __('pppoe.secret.last-offline') }}</th>
                    <th>{{ __('pppoe.secret.state') }}</th>
                    <th class="text-center text-end">{{ __('pppoe.secret.actions') }}</th>
                </tr>
            </thead>
            <tbody>

                @foreach ($pppsecrets as $pppsecret)
                    <tr>
                        <td><span
                                class="badge rounded-pill bg-secondary-soft text-secondary">{{ $loop->iteration }}</span>
                        </td>
                        <td><span class="badge bg-blue-soft text-primary">{{ $pppsecret['name'] }}</span></td>
                        <td><span class="badge bg-info-soft text-info">{{ $pppsecret['password'] ?? 'no data' }}</span>
                        </td>
                        <td>
                            <span class="badge bg-primary text-white">
                                <i class="fas fa-id-card-alt"></i> {{ $pppsecret['profile'] ?? 'no data' }}
                            </span>
                        </td>
                        <td><span
                                class="badge bg-blue-soft text-primary">{{ $pppsecret['local-address'] ?? 'no data' }}</span>
                        </td>
                        <td><span
                                class="badge bg-blue-soft text-primary">{{ $pppsecret['remote-address'] ?? 'no data' }}</span>
                        </td>
                        <td><span
                                class="badge bg-blue-soft text-primary">{{ $pppsecret['last-logged-out'] ?? 'no data' }}</span>
                        </td>
                        <td>

                            @if ($pppsecret['disabled'] === \App\Enums\MikrotikStatus::PPPOE_ENABLE->value)
                                <span class="badge text-white bg-success">
                                    <i class="fas fa-lock-open"></i> {{ __('pppoe.secret.enabled') }}
                                </span>
                            @endif
                            @if ($pppsecret['disabled'] === \App\Enums\MikrotikStatus::PPPOE_DISABLE->value)
                                <span class="badge text-white bg-danger">
                                    <i class="fas fa-lock"></i> {{ __('pppoe.secret.disabled') }}
                                </span>
                            @endif
                        </td>

                        <td class="d-flex items-center justify-content-end">
                            <x-drop-down-action>
                                <li>
                                    @if ($pppsecret['disabled'] === \App\Enums\MikrotikStatus::PPPOE_DISABLE->value)
                                        <form method="POST" class="d-inline"
                                            action="{{ route('ppp.secret.enable', ['mikrotik' => $mikrotik, 'username' => $pppsecret['name']]) }}">
                                            @csrf
                                            @method('put')
                                            <a class="dropdown-item" href="#"
                                                onclick="event.preventDefault(); this.closest('form').submit();">
                                                <i class="fa fa-check me-2 text-success"></i>
                                                {{ __('pppoe.secret.enable') }}
                                            </a>
                                        </form>
                                    @elseif ($pppsecret['disabled'] === \App\Enums\MikrotikStatus::PPPOE_ENABLE->value)
                                        <form method="POST" class="d-inline"
                                            action="{{ route('ppp.secret.disable', ['mikrotik' => $mikrotik, 'username' => $pppsecret['name']]) }}">
                                            @csrf
                                            @method('put')
                                            <a class="dropdown-item" href="#"
                                                onclick="event.preventDefault(); this.closest('form').submit();">
                                                <i class="fa fa-times text-warning me-2"></i>
                                                {{ __('pppoe.secret.disable') }}
                                            </a>
                                        </form>
                                    @endif
                                </li>
                                <li>
                                    <a class="dropdown-item"
                                        href="{{ route('ppp.secret.edit', ['mikrotik' => $mikrotik, 'id' => $pppsecret['.id']]) }}">
                                        <i class="far fa-edit me-2 text-success"></i>
                                        {{ __('pppoe.secret.edit') }}
                                    </a>
                                </li>
                                <li>
                                    <form method="POST"
                                        action="{{ route('ppp.secret.delete', ['mikrotik' => $mikrotik, 'id' => $pppsecret['.id']]) }}">
                                        @csrf
                                        @method('delete')

                                        <a href="#" class="dropdown-item"
                                            onclick="event.preventDefault(); this.closest('form').submit();">
                                            <i class="fa fa-trash me-2 text-danger"></i>
                                            {{ __('pppoe.secret.delete') }}
                                        </a>
                                    </form>
                                </li>
                            </x-drop-down-action>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <div class="card-footer">
            <form action="{{ route('ppp.secret.clone', ['mikrotik' => $mikrotik]) }}" method="POST">
                @csrf
            <button type="submit" class="btn btn-sm btn-success"><i class="fas fa-clone"></i> &nbsp
                {{ __('pppoe.secret.button.copy') }}</button>
            </form>
        </div>
    </div>


    @section('custom_scripts')
        <script>
            $(document).ready(function() {
                $('#secretsTable').DataTable({

                });

            });
        </script>
    @endsection
</x-layouts.mikrotiks.app-layout>
