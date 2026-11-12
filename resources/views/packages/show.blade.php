<x-layouts.app-layout title="Packet Show">

    <div class="card-header py-3">
        <h5 class="m-0 fw-bold text-primary">{{ __('Detail Package') }}</h5>
    </div>

    <div class="card-body">
        <div class="row">
            <!-- Left Column -->
            <div class="col-md-6">
                <div class="mb-3">
                    <strong>{{ __('package.label.name') }}</strong><br>
                    {{ $package->name ?? 'no data' }}
                </div>

                <div class="mb-3">
                    <strong>{{ __('package.label.profile') }}</strong><br>
                    {{ $package->profile ?? 'no data' }}
                </div>

                <div class="mb-3">
                    <strong>{{ __('package.label.speed_up_to') }}</strong><br>
                    {{ $package->speed_up_to ?? '0' }} {{ $package->speed_unit ?? 'Mbps' }}
                </div>

                <div class="mb-3">
                    <strong>{{ __('package.label.recommended_device') }}</strong><br>
                    {{ $package->recommended_device_min ?? '0' }} - {{ $package->recommended_device_max ?? '0' }}
                </div>

                <div class="mb-3">
                    <strong>{{ __('package.label.quota') }}</strong><br>
                    {{ $package->quota ?? 'no data' }}
                </div>

                <div class="mb-3">
                    <strong>{{ __('package.label.incl_modem') }}</strong><br>
                    {{ $package->include_modem ? 'Yes' : 'No' }}
                </div>

                <div class="mb-3">
                    <strong>{{ __('package.label.created_at') }}</strong><br>
                    {{ \Carbon\Carbon::parse($package->created_at)->translatedFormat('d F Y H:i') }} WIB
                </div>
            </div>

            <!-- Right Column -->
            <div class="col-md-6">
                <div class="mb-3">
                    <strong>{{ __('package.label.installation_free') }}</strong><br>
                    {{ $package->is_installation_free ? 'Yes' : 'No' }}
                </div>

                <div class="mb-3">
                    <strong>{{ __('package.label.installation_fee') }}</strong><br>
                    Rp {{ number_format($package->installation_fee ?? 0, 0, ',', '.') }}
                </div>

                <div class="mb-3">
                    <strong>{{ __('package.label.price') }}</strong><br>
                    Rp {{ number_format($package->price ?? 0, 0, ',', '.') }}
                </div>

                <div class="mb-3">
                    <strong>{{ __('package.label.package_category') }}</strong><br>
                    {{ $package->category->name ?? 'no data' }}
                </div>

                <div class="mb-3">
                    <strong>{{ __('package.label.description') }}</strong><br>
                    {{ $package->description ?? 'no data' }}
                </div>

                <div class="mb-3">
                    <strong>{{ __('package.label.updated_at') }}</strong><br>
                    {{ \Carbon\Carbon::parse($package->updated_at)->translatedFormat('d F Y H:i') }} WIB
                </div>
            </div>
        </div>
    </div>

    <div class="card-footer">
        @can('package-edit')
            <a class="btn btn-sm btn-success" href="{{ route('packages.edit', $package->slug) }}">
                <i class="fa fa-edit"></i> Edit
            </a>
        @endcan
        <a class="btn btn-sm btn-primary" href="{{ route('packages.index') }}">
            <i class="fa fa-arrow-left"></i> Back
        </a>
    </div>

</x-layouts.app-layout>
