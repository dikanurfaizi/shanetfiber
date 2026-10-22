<x-layouts.cpanel.customers.app-layout title="{{ __('customer.title.suspend') }}">
    <div class="card border-0 shadow-sm rounded-lg my-3">

        {{-- Header --}}
        <div class="card-header bg-white py-3 border-bottom">
            <h5 class="m-0 font-weight-bold text-danger">
                <i class="fas fa-exclamation-triangle mr-2"></i>{{ __('customer.header.suspend') }}
            </h5>
        </div>

        <div class="card-body p-3 p-md-4">
            {!! Form::open(['method' => 'DELETE', 'route' => ['customers.destroy', $customer->slug]]) !!}

            {{-- Ringkasan Informasi Customer --}}
            <div class="bg-light p-3 rounded mb-4 border">
                <h6 class="font-weight-bold text-secondary mb-3">{{ __('customer.label.customer-info') }}</h6>

                {{-- Nama --}}
                <div class="row small mb-2 align-items-center">
                    <div class="col-6 col-md-2 col-lg-2 text-muted font-weight-bold">{{ __('customer.label.name') }}
                    </div>
                    <div class="col-6 col-md-10 col-lg-10 text-right text-md-left font-weight-bold text-dark">
                        {{ $customer->name }}</div>
                </div>

                {{-- Alamat --}}
                <div class="row small mb-2 align-items-center">
                    <div class="col-6 col-md-2 col-lg-2 text-muted font-weight-bold">{{ __('customer.label.address') }}
                    </div>
                    <div class="col-6 col-md-10 col-lg-10 text-right text-md-left text-dark">
                        {{ $customer->address ?? '-' }}</div>
                </div>

                {{-- Server Router --}}
                <div class="row small align-items-center">
                    <div class="col-6 col-md-2 col-lg-2 text-muted font-weight-bold">{{ __('customer.label.server') }}
                    </div>
                    <div class="col-6 col-md-10 col-lg-10 text-right text-md-left text-dark">
                        {{ $customer->mikrotik->name ?? '-' }}</div>
                </div>
            </div>

            {{-- Alert Peringatan Dampak --}}
            <div class="alert alert-warning border-left-danger shadow-sm mb-4 p-3" role="alert">
                <div class="d-flex align-items-start">
                    <div class="mr-3">
                        <i class="fas fa-info-circle fa-2x text-danger"></i>
                    </div>
                    <div>
                        <h6 class="alert-heading font-weight-bold text-danger mb-2">
                            {{ __('customer.info.suspend') }}
                        </h6>
                        <p class="mb-2 text-dark small">
                            {{ __('customer.info.suspend-action') }}
                            <strong>{{ __('customer.info.user-secret') }}</strong>
                            {{ __('customer.info.on-server') }}
                            <strong>{{ $customer->mikrotik->name ?? '-' }}</strong>.
                        </p>
                        <p class="mb-0 text-muted small">
                            <i class="fas fa-ban text-danger mr-1"></i>
                            {{ __('customer.info.after-deleted') }}
                        </p>
                    </div>
                </div>
            </div>

            {{-- Tombol Aksi --}}
            <div class="pt-2 border-top d-flex flex-column flex-sm-row">
                @can('customer-delete')
                    <button type="submit" class="btn btn-sm btn-danger px-3 mb-2 mb-sm-0 mr-sm-2">
                        <i class="fas fa-trash mr-1"></i>{{ __('customer.action.delete') }}
                    </button>
                @endcan

                <a class="btn btn-sm btn-outline-secondary px-3" href="{{ URL::previous() }}">
                    <i class="fas fa-arrow-left mr-1"></i>{{ __('customer.button.cancel') }}
                </a>
            </div>

            {!! Form::close() !!}
        </div>

    </div>
</x-layouts.cpanel.customers.app-layout>
