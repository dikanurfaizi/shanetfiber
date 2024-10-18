<x-layouts.cpanel.settings.app-layout title="Banks">

    <div class="card-header py-3">
        <h5 class="m-0 font-weight-bold text-primary">{{ __('bank.header.index') }}</h5>
        <span><a class="btn btn-sm btn-success mt-2" href="{{ route('banks.create') }}"><i class="fa fa-plus"></i> {{ __('bank.button.create') }}</a></span>
    </div>

    <div class="card-body">
        <table id="banksTable" cellspacing="0" width="100%" class="table datatable-loading no-footer sortable searchable">
            <thead>
                <tr>
                    <th>#</th>
                    <th>{{ __('bank.table.name') }}</th>
                    <th>{{ __('bank.table.bank') }}</th>
                    <th>{{ __('bank.table.account-number') }}</th>
                    <th>{{ __('bank.table.status') }}</th>
                    <th class="text-end">{{ __('bank.table.action') }}</th>
                </tr>
            </thead>
            <tbody>

                @foreach($banks as $bank)
                <tr>
                    <td><span class="badge rounded-pill text-secondary">{{ ++$i }}</span></td>
                    <td><span class="badge text-primary">{{ $bank->name }}</span></td>
                    <td><span class="badge text-primary">
                            {{$bank->bank}}
                        </span></td>
                    <td>
                        <span class="badge text-primary">
                            {{ $bank->account_number }}
                        </span>
                    </td>
                    <td>
                        @if ($bank->disabled === 'false')
                        <span class="badge text-white bg-success">
                            <i class="fas fa-lock-open"></i> {{ __('bank.info.enabled') }}
                        </span>
                        @endif
                        @if ($bank->disabled === 'true')
                        <span class="badge text-white bg-danger">
                            <i class="fas fa-lock"></i> {{ __('bank.info.disabled') }}
                        </span>
                        @endif

                    </td>

                    <td class="d-flex items-center justify-content-end">
                        <x-drop-down-action>
                            <li>
                                <a class="dropdown-item" href="{{ route('banks.edit',$bank->slug) }}">
                                    <i class="far fa-edit me-2 text-success"></i>
                                    {{ __('bank.action.edit') }}
                                </a>
                            </li>
                            <li>
                                @if ($bank->disabled === 'false')
                                <form method="POST" class="d-inline" action="{{ route('bank.disable', $bank->slug) }}">
                                    @csrf
                                    @method('put')
                                    <a class="dropdown-item" href="#" onclick="event.preventDefault(); this.closest('form').submit();">
                                        <i class="fa fa-times text-warning me-2"></i> {{ __('bank.action.disable') }}
                                    </a>
                                </form>
                                @else
                                <form method="POST" class="d-inline" action="{{ route('bank.enable', $bank->slug) }}">
                                    @csrf
                                    @method('put')
                                    <a class="dropdown-item" href="#" onclick="event.preventDefault(); this.closest('form').submit();">
                                        <i class="fa fa-check me-2 text-success"></i> {{ __('bank.action.enable') }}
                                    </a>
                                </form>
                                @endif
                            </li>
                            <li>
                                {!! Form::open(['method' => 'DELETE','route' => ['banks.destroy', $bank->slug],'style'=>'display:inline']) !!}
                                <a href="#" class="dropdown-item" onclick="event.preventDefault(); this.closest('form').submit();">
                                    <i class="fa fa-trash me-2 text-danger"></i>
                                    {{ __('bank.action.delete') }}
                                </a>
                                {!! Form::close() !!}

                            </li>

                        </x-drop-down-action>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        {{ $banks->links() }}
    </div>
</x-layouts.cpanel.settings.app-layout>