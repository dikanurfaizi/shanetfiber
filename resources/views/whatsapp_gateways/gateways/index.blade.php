<x-layouts.cpanel.settings.app-layout title="WhatsApp Gateway Providers">

    <div class="card-header py-3">
        <h5 class="m-0 font-weight-bold text-primary">WhatsApp Gateway Providers</h5>
    </div>

    <div class="row mt-3">

        @foreach ($gateways as $gw)
            <div class="col-md-6 mb-4">
                <div class="card shadow-sm border-left-primary h-100">
                    <div class="card-body">

                        <h5 class="font-weight-bold">{{ $gw->name }}</h5>
                        <p class="text-muted mb-1">Type: {{ $gw->type }}</p>

                        <p class="mb-2">
                            {{-- Menggunakan accounts_count dari withCount() agar tidak N+1 --}}
                            <strong>Accounts:</strong> {{ $gw->accounts_count ?? $gw->accounts->count() }}
                        </p>

                        {{-- Tag Badge yang disederhanakan --}}
                        <span class="badge {{ $gw->is_active ? 'bg-success' : 'bg-secondary' }} text-white">
                            {{ $gw->is_active ? 'Active' : 'Inactive' }}
                        </span>

                        <div class="mt-3">
                            {{-- Parameter disesuaikan menggunakan array ber-key eksplisit --}}
                            <a href="{{ route('whatsappgateway.show', ['whatsappgateway' => $gw->slug]) }}"
                                class="btn btn-sm btn-primary">
                                <i class="fa fa-cog"></i> Manage
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach

    </div>

</x-layouts.cpanel.settings.app-layout>
