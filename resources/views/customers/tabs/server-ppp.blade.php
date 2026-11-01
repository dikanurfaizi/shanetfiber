<div class="card-header py-3">
    <h5 class="m-0 font-weight-bold text-primary">{{ __('customer.header.show-server') }}</h5>

    <span class="text-success">{{ $info_msg }}</span>

    {{-- Username mismatch warning --}}
    @php
        $serverName = $pppsecret['name'] ?? null;

        $remoteAddress = $secretActive['address'] ?? ($customer->ip_address ?? null);
        $disabledState = $pppsecret['disabled'] ?? null;
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
    <div class="row">

        {{-- SERVER --}}
        <div class="col-12 col-md-6 mb-3">
            <div class="fw-bold text-muted small">{{ __('customer.label.server') }}</div>
            <div class="mt-1">
                <span class="badge bg-success text-white">
                    <i class="fas fa-server"></i> {{ $customer->mikrotik->name }}
                </span>
            </div>
        </div>

        {{-- PROFILE --}}
        <div class="col-12 col-md-6 mb-3">
            <div class="fw-bold text-muted small">{{ __('customer.label.profile') }}</div>
            <div class="mt-1">
                <span class="text-primary">{{ $pppsecret['profile'] ?? $package_profile }}</span>
            </div>
        </div>

        {{-- USERNAME --}}
        <div class="col-12 col-md-6 mb-3">
            <div class="fw-bold text-muted small">{{ __('customer.label.username') }}</div>
            <div class="mt-1">
                <span
                    class="{{ $serverName && $serverName !== $customer->username
                        ? 'alert alert-danger py-1 px-2 d-inline-block'
                        : 'text-primary' }}">
                    {{ $serverName ?? $customer->username }}
                </span>
            </div>
        </div>

        {{-- PASSWORD PPP --}}
        <div class="col-12 col-md-6 mb-3">
            <div class="fw-bold text-muted small">{{ __('customer.label.password-ppp') }}</div>
            <div class="mt-1">
                <span class="text-primary">{{ $customer->password_ppp ?? 'No Data' }}</span>
            </div>
        </div>

        {{-- REMOTE ADDRESS --}}
        <div class="col-12 col-md-6 mb-3">
            <div class="font-weight-bold text-muted small">{{ __('customer.label.remote-address') }}</div>
            <div class="mt-1">
                <span class="text-primary">{{ $remoteAddress ?? 'Offline' }}</span>

                @if ($remoteAddress)
                    <a href="http://{{ $remoteAddress }}:{{ $ont->port ?? 80 }}"
                        class="badge badge-primary ml-2" target="_blank">
                        {{ __('customer.remote') }}
                    </a>
                @endif
            </div>
        </div>

        {{-- SERVICE --}}
        <div class="col-12 col-md-6 mb-3">
            <div class="font-weight-bold text-muted small">{{ __('customer.label.service') }}</div>
            <div class="mt-1">
                <span class="text-primary">{{ $customer->pppType->name ?? 'No Service' }}</span>
            </div>
        </div>

        {{-- STATE --}}
        <div class="col-12 col-md-6 mb-3">
            <div class="fw-bold text-muted small">{{ __('customer.label.state') }}</div>
            <div class="mt-1">
                @include('customers.partials.ppp-state-badge', [
                    'customer' => $customer,
                    'disabledState' => $disabledState,
                ])
            </div>
        </div>

        {{-- UPTIME --}}
        <div class="col-12 col-md-6 mb-3">
            <div class="fw-bold text-muted small">{{ __('customer.label.uptime') }}</div>
            <div class="mt-1">
                <span class="text-primary">{{ $customer->uptime_human ?? 'No Data' }}</span>
            </div>
        </div>

        {{-- CALLER ID --}}
        <div class="col-12 col-md-6 mb-3">
            <div class="fw-bold text-muted small">{{ __('customer.label.caller-id') }}</div>
            <div class="mt-1">
                <span class="text-primary">{{ $customer->caller_id ?? 'No Caller ID' }}</span>
            </div>
        </div>

        {{-- LAST CALLER ID --}}
        <div class="col-12 col-md-6 mb-3">
            <div class="fw-bold text-muted small">{{ __('customer.label.last-caller-id') }}</div>
            <div class="mt-1">
                <span class="text-primary">{{ $pppsecret['last-caller-id'] ?? 'No Caller ID' }}</span>
            </div>
        </div>

        {{-- COMMENT --}}
        <div class="col-12 col-md-6 mb-3">
            <div class="fw-bold text-muted small">{{ __('customer.label.comment') }}</div>
            <div class="mt-1">
                <span class="text-primary">{{ $pppsecret['comment'] ?? 'No Comment' }}</span>
            </div>
        </div>

    </div>

    {{-- ACTION BUTTONS --}}
    <div class="row mt-3">
        <div class="col-12">

            <div class="d-flex flex-column flex-md-row">

                @can('secret-edit')
                    <div class="d-block d-md-inline-block mb-2 mr-md-2">
                        <a href="{{ route('customers.edit.ppp', [$customer->slug, 'caller_id' => $pppsecret['last-caller-id'] ?? null]) }}"
                            class="btn-soft btn-blue btn-block btn-md-inline text-center">
                            <i class="fa fa-edit"></i> {{ __('customer.button.edit-ppp') }}
                        </a>
                    </div>
                @endcan

                @canany('customer-edit', 'customer-create')
                    @if (!$customer->activation)
                        <div class="d-block d-md-inline-block mb-2 mr-md-2">
                            <form method="POST" action="{{ route('customers.activation', $customer->slug) }}">
                                @csrf @method('put')
                                <button class="btn-soft btn-green btn-block btn-md-inline">
                                    <i class="fa fa-check mr-2"></i> {{ __('customer.action.activation') }}
                                </button>
                            </form>
                        </div>
                    @endif
                @endcanany

                @can('secret-disable')
                    @if ($customer->activation)
                        @if ($disabledState === \App\Enums\MikrotikStatus::PPPOE_DISABLE->value)
                            <div class="d-block d-md-inline-block mb-2 mr-md-2">
                                <form method="POST" action="{{ route('customers.enable', $customer->slug) }}">
                                    @csrf @method('put')
                                    <button class="btn-soft btn-green btn-block btn-md-inline">
                                        <i class="fa fa-check mr-2"></i> {{ __('pppoe.secret.enable') }}
                                    </button>
                                </form>
                            </div>
                        @elseif ($disabledState === \App\Enums\MikrotikStatus::PPPOE_ENABLE->value)
                            <div class="d-block d-md-inline-block mb-2 mr-md-2">
                                <form method="POST" action="{{ route('customers.disable', $customer->slug) }}">
                                    @csrf @method('put')
                                    <button class="btn-soft btn-red btn-block btn-md-inline">
                                        <i class="fa fa-times mr-2"></i> {{ __('pppoe.secret.disable') }}
                                    </button>
                                </form>
                            </div>
                        @else
                            <div class="d-block d-md-inline-block mb-2 mr-md-2">
                                <button class="btn-soft btn-gray btn-block btn-md-inline" disabled>
                                    <i class="fa fa-ban mr-2"></i> {{ __('pppoe.secret.un-available') }}
                                </button>
                            </div>
                        @endif
                    @endif
                @endcan

            </div>

        </div>
    </div>

</div>
