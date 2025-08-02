<x-layouts.app-layout title="Packet Show">

    <div class="card-header py-3">
        <h5 class="m-0 font-weight-bold text-primary">{{ __('Paket Detail') }}</h5>
    </div>

    <div class="card-body">
        <div class="row">
            <!-- Left Column -->
            <div class="col-md-6">
                <div class="mb-3">
                    <strong>{{ __('paket.label.name') }}</strong><br>
                    {{ $packet->name ?? 'no data' }}
                </div>

                <div class="mb-3">
                    <strong>{{ __('paket.label.profile') }}</strong><br>
                    {{ $packet->profile ?? 'no data' }}
                </div>

                <div class="mb-3">
                    <strong>{{ __('paket.label.speed_up_to') }}</strong><br>
                    {{ $packet->speed_up_to ?? '0' }} {{ $packet->speed_unit ?? 'Mbps' }}
                </div>

                <div class="mb-3">
                    <strong>{{ __('paket.label.recommended_device') }}</strong><br>
                    {{ $packet->recommended_device_min ?? '0' }} - {{ $packet->recommended_device_max ?? '0' }}
                </div>

                <div class="mb-3">
                    <strong>{{ __('paket.label.quota') }}</strong><br>
                    {{ $packet->quota ?? 'no data' }}
                </div>

                <div class="mb-3">
                    <strong>{{ __('paket.label.incl_modem') }}</strong><br>
                    {{ $packet->include_modem ? 'Yes' : 'No' }}
                </div>

                <div class="mb-3">
                    <strong>{{ __('paket.label.created_at') }}</strong><br>
                    {{ \Carbon\Carbon::parse($packet->created_at)->translatedFormat('d F Y H:i') }} WIB
                </div>
            </div>

            <!-- Right Column -->
            <div class="col-md-6">
                <div class="mb-3">
                    <strong>{{ __('paket.label.installation_free') }}</strong><br>
                    {{ $packet->is_installation_free ? 'Yes' : 'No' }}
                </div>

                <div class="mb-3">
                    <strong>{{ __('paket.label.installation_fee') }}</strong><br>
                    Rp {{ number_format($packet->installation_fee ?? 0, 0, ',', '.') }}
                </div>

                <div class="mb-3">
                    <strong>{{ __('paket.label.price') }}</strong><br>
                    Rp {{ number_format($packet->price ?? 0, 0, ',', '.') }}
                </div>

                <div class="mb-3">
                    <strong>{{ __('paket.label.package_category') }}</strong><br>
                    {{ $packet->paketCategory->name ?? 'no data' }}
                </div>

                <div class="mb-3">
                    <strong>{{ __('paket.label.billing_cycle') }}</strong><br>
                    {{ $packet->billingCycle->code ?? 'no data' }}
                </div>

                <div class="mb-3">
                    <strong>{{ __('paket.label.description') }}</strong><br>
                    {{ $packet->description ?? 'no data' }}
                </div>

                <div class="mb-3">
                    <strong>{{ __('paket.label.updated_at') }}</strong><br>
                    {{ \Carbon\Carbon::parse($packet->updated_at)->translatedFormat('d F Y H:i') }} WIB
                </div>
            </div>
        </div>
    </div>

    <div class="card-footer">
        <a class="btn btn-sm btn-success" href="{{ route('packets.edit', $packet->slug) }}">
            <i class="fa fa-edit"></i> Edit
        </a>
        <a class="btn btn-sm btn-primary" href="{{ route('packets.index') }}">
            <i class="fa fa-arrow-left"></i> Back
        </a>
    </div>

</x-layouts.app-layout>
