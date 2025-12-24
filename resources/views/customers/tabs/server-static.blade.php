<div class="card-header py-3">
    <h5 class="m-0 font-weight-bold text-primary">
        {{ __('customer.header.show-server') }}
    </h5>

    <div class="text-success">{{ $info_msg }}</div>
</div>

<div class="card-body col-lg-12">
    <div class="row">

        {{-- IP ADDRESS --}}
        <div class="col-12 col-md-6 mb-3">
            <div class="fw-bold text-muted small">
                {{ __('customer.label.ip-address') }}
            </div>
            <div class="mt-1">
                <span class="text-primary">
                    {{ $customer->ip_static ?? 'No IP' }}
                </span>
            </div>
        </div>

        {{-- MAC ADDRESS --}}
        <div class="col-12 col-md-6 mb-3">
            <div class="fw-bold text-muted small">
                {{ __('customer.label.mac-address') }}
            </div>
            <div class="mt-1">
                <span class="text-primary">
                    {{ $customer->mac_address ?? 'No MAC' }}
                </span>
            </div>
        </div>

        {{-- INTERFACE --}}
        <div class="col-12 col-md-6 mb-3">
            <div class="fw-bold text-muted small">
                {{ __('customer.label.interface') }}
            </div>
            <div class="mt-1">
                <span class="text-primary">
                    {{ $customer->interface ?? 'No Interface' }}
                </span>
            </div>
        </div>

        {{-- LIMITATION NAME --}}
        <div class="col-12 col-md-6 mb-3">
            <div class="fw-bold text-muted small">
                {{ __('customer.label.limitation-name') }}
            </div>
            <div class="mt-1">
                <span class="text-primary">
                    {{ $customer->username ?? 'No Name' }}
                </span>
            </div>
        </div>

    </div>
</div>
