<div class="card-header py-3">
    <h5 class="m-0 font-weight-bold text-primary">{{ __('customer.header.show-server') }}</h5>

    <span class="text-success">{{ $info_msg }}</span>

    {{-- Username mismatch warning --}}
    @php
        $serverName = $pppsecret['name'] ?? null;
    @endphp

    @if ($serverName !== null && $serverName !== $customer->username)
        <div class="alert alert-danger mt-2">
            {{ __('Warning username on server different with database!') }}<br>
            <strong>{{ __('pppoe.secret.db-username') }}:</strong> {{ $customer->username }}<br>
            <strong>{{ __('pppoe.secret.server-username') }}:</strong> {{ $serverName }}
        </div>
    @endif
</div>

<div class="card-body col-lg-12">
    <div style="overflow-x: auto;">
        <table class="table no-footer" id="serverTable">

            {{-- SERVER + PROFILE --}}
            <tr class="table-borderless">
                <td class="py-1"><strong>{{ __('customer.label.server') }}</strong></td>
                <td class="py-1">:
                    <span class="badge bg-success text-white">
                        <i class="fas fa-server"></i> {{ $customer->mikrotik->name }}
                    </span>
                </td>

                <td class="py-1"><strong>{{ __('customer.label.profile') }}</strong></td>
                <td class="py-1">:
                    <span class="text-primary">{{ $pppsecret['profile'] ?? $package_profile }}</span>
                </td>
            </tr>

            {{-- USERNAME + PASSWORD --}}
            @php
                $usernameIsMismatch = $serverName !== null && $serverName !== $customer->username;
                $usernameClass = $usernameIsMismatch ? 'alert alert-danger py-1 px-2 d-inline-block' : 'text-primary';
            @endphp

            <tr class="table-borderless">
                <td class="py-1"><strong>{{ __('customer.label.username') }}</strong></td>
                <td class="py-1">:
                    <span class="{{ $usernameClass }}">
                        {{ $serverName ?? $customer->username }}
                    </span>
                </td>

                <td class="py-1"><strong>{{ __('customer.label.password-ppp') }}</strong></td>
                <td class="py-1">:
                    <span class="text-primary">{{ $customer->password_ppp ?? 'No Data' }}</span>
                </td>
            </tr>

            {{-- REMOTE ADDRESS + SERVICE --}}
            @php
                $remoteAddress = $secretActive['address'] ?? ($customer->ip_address ?? null);
            @endphp

            <tr class="table-borderless">
                <td class="py-1"><strong>{{ __('customer.label.remote-address') }}</strong></td>
                <td class="py-1">:
                    <span class="text-primary">{{ $remoteAddress ?? 'Offline' }}</span>

                    @if ($remoteAddress)
                        <a href="http://{{ $remoteAddress }}:{{ $ont->port ?? 80 }}"
                            class="badge bg-primary text-white ms-2" target="_blank">
                            {{ __('customer.remote') }}
                        </a>
                    @endif
                </td>

                <td class="py-1"><strong>{{ __('customer.label.service') }}</strong></td>
                <td class="py-1">:
                    <span class="text-primary">{{ $customer->pppType->name ?? 'No Service' }}</span>
                </td>
            </tr>

            {{-- STATE + UPTIME --}}
            @php
                $disabledState = $pppsecret['disabled'] ?? null;
            @endphp

            <tr class="table-borderless">
                <td class="py-1"><strong>{{ __('customer.label.state') }}</strong></td>
                <td class="py-1">:

                    @if ($customer->activation == 'false')
                        <span class="badge bg-danger text-white">
                            <i class="fa fa-times text-warning me-2"></i>
                            {{ __('customer.info.not-activation') }}
                        </span>
                    @else
                        @if ($disabledState === \App\Enums\MikrotikStatus::PPPOE_ENABLE->value)
                            <span class="badge bg-success text-white">
                                <i class="fas fa-lock-open"></i> {{ __('pppoe.secret.enabled') }}
                            </span>
                        @elseif ($disabledState === \App\Enums\MikrotikStatus::PPPOE_DISABLE->value)
                            <span class="badge bg-danger text-white">
                                <i class="fas fa-lock"></i> {{ __('pppoe.secret.disabled') }}
                            </span>
                        @else
                            <span class="badge bg-secondary text-white">
                                {{ __('pppoe.secret.unknown-state') }}
                            </span>
                        @endif
                    @endif
                </td>

                <td class="py-1"><strong>{{ __('customer.label.uptime') }}</strong></td>
                <td class="py-1">:
                    <span class="text-primary">{{ $secretActive['uptime'] ?? 'No Data' }}</span>
                </td>
            </tr>

            {{-- CALLER ID --}}
            <tr class="table-borderless">
                <td class="py-1"><strong>{{ __('customer.label.caller-id') }}</strong></td>
                <td class="py-1">:
                    <span class="text-primary">{{ $customer->caller_id ?? 'No Caller ID' }}</span>
                </td>

                <td class="py-1"><strong>{{ __('customer.label.last-caller-id') }}</strong></td>
                <td class="py-1">:
                    <span class="text-primary">{{ $pppsecret['last-caller-id'] ?? 'No Caller ID' }}</span>
                </td>
            </tr>

            {{-- COMMENT --}}
            <tr class="table-borderless">
                <td class="py-1"><strong>{{ __('customer.label.comment') }}</strong></td>
                <td class="py-1">:
                    <span class="text-primary">{{ $pppsecret['comment'] ?? 'No Comment' }}</span>
                </td>
            </tr>

        </table>
    </div>

    {{-- ACTION BUTTONS --}}
    <div class="row mt-3">
        <div class="col-md-12">

            @can('secret-edit')
                <a class="btn-soft btn-blue"
                    href="{{ route('customers.edit.ppp', [$customer->slug, 'caller_id' => $pppsecret['last-caller-id'] ?? null]) }}">
                    <i class="fa fa-edit"></i> {{ __('customer.button.edit-ppp') }}
                </a>
            @endcan

            @canany('customer-edit', 'customer-create')
                @if ($customer->activation == 'false')
                    <form method="POST" action="{{ route('customers.activation', $customer->slug) }}" class="d-inline">
                        @csrf @method('put')
                        <button class="btn-soft btn-green">
                            <i class="fa fa-check me-2"></i> {{ __('customer.action.activation') }}
                        </button>
                    </form>
                @endif
            @endcanany

            @can('secret-disable')
                @if ($customer->activation == 'true')
                    {{-- CASE 1: PPP State = DISABLED → Show ENABLE button --}}
                    @if ($disabledState === \App\Enums\MikrotikStatus::PPPOE_DISABLE->value)
                        <form method="POST" action="{{ route('customers.enable', $customer->slug) }}" class="d-inline">
                            @csrf @method('put')
                            <button class="btn-soft btn-green">
                                <i class="fa fa-check me-2"></i> {{ __('pppoe.secret.enable') }}
                            </button>
                        </form>

                        {{-- CASE 2: PPP State = ENABLED → Show DISABLE button --}}
                    @elseif ($disabledState === \App\Enums\MikrotikStatus::PPPOE_ENABLE->value)
                        <form method="POST" action="{{ route('customers.disable', $customer->slug) }}" class="d-inline">
                            @csrf @method('put')
                            <button class="btn-soft btn-red">
                                <i class="fa fa-times me-2"></i> {{ __('pppoe.secret.disable') }}
                            </button>
                        </form>

                        {{-- CASE 3: STATE UNKNOWN → SHOW DISABLED INFO BUTTON --}}
                    @else
                        <button class="btn-soft btn-gray disabled" disabled>
                            <i class="fa fa-ban me-2"></i> {{ __('pppoe.secret.un-available') }}
                        </button>
                    @endif
                @endif
            @endcan

        </div>
    </div>
</div>
