<div class="card-header py-3">
    <h5 class="m-0 fw-bold text-primary">{{ __('customer.header.show-server') }}</h5>
    <span class="text-danger">{{ $info_msg }}</span>
</div>

<div class="card-body">
    <div class="row">

        {{-- SERVER --}}
        <div class="col-12 col-md-6 mb-3">
            <div class="fw-bold text-muted small">
                {{ __('customer.label.server') }}
            </div>
            <div class="mt-1">
                <span class="badge bg-secondary">
                    <i class="fas fa-server"></i>
                    {{ $customer->mikrotik->name ?? '-' }}
                </span>
            </div>
        </div>

        {{-- PROFILE --}}
        <div class="col-12 col-md-6 mb-3">
            <div class="fw-bold text-muted small">
                {{ __('customer.label.profile') }}
            </div>
            <div class="mt-1">
                <span class="text-muted">
                    {{ $customer->active_package->profile ?? 'No Profile' }}
                </span>
            </div>
        </div>

        {{-- USERNAME --}}
        <div class="col-12 col-md-6 mb-3">
            <div class="fw-bold text-muted small">
                {{ __('customer.label.username') }}
            </div>
            <div class="mt-1">
                <span class="text-muted">
                    {{ $customer->username ?? '-' }}
                </span>
            </div>
        </div>

        {{-- PASSWORD PPP --}}
        <div class="col-12 col-md-6 mb-3">
            <div class="fw-bold text-muted small">
                {{ __('customer.label.password-ppp') }}
            </div>
            <div class="mt-1">
                <span class="text-muted">
                    {{ $customer->password_ppp ?? 'No Data' }}
                </span>
            </div>
        </div>

        {{-- SERVICE --}}
        <div class="col-12 col-md-6 mb-3">
            <div class="fw-bold text-muted small">
                {{ __('customer.label.service') }}
            </div>
            <div class="mt-1">
                <span class="text-muted">
                    {{ $customer->pppType->name ?? 'No Service' }}
                </span>
            </div>
        </div>

        {{-- STATE --}}
        <div class="col-12 col-md-6 mb-3">
            <div class="fw-bold text-muted small">
                {{ __('customer.label.state') }}
            </div>
            <div class="mt-1">
                <span class="badge bg-danger">
                    {{ __('customer.info.not-activation') }}
                </span>
            </div>
        </div>

    </div>

    {{-- ACTION (Pojok Kanan Bawah) --}}
    @canany(['customer-edit', 'customer-create'])
        @if (!$customer->activation)
            <div class="d-flex justify-content-end mt-3">
                <form method="POST" action="{{ route('customers.activation', $customer->slug) }}">
                    @csrf
                    @method('PUT')

                    <button type="submit" class="btn btn-success">
                        <i class="fa fa-check me-2"></i>
                        {{ __('customer.action.activation') }}
                    </button>
                </form>
            </div>
        @endif
    @endcanany

</div>
