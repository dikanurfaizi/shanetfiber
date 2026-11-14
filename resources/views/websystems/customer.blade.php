<x-layouts.cpanel.settings.app-layout title="Web System">
    <div class="card-header py-3">
        <h6 class="m-0 fw-bold text-primary">{{ __('websystem.header.customer') }}</h6>
    </div>

    <div class="row">
        <div class="col-lg-12">
            <form action="{{ route('websystem.update.customer', $websystem->slug) }}" method="POST">
                @csrf
                @method('PATCH')

                <div class="card-body col-lg-8">
                    {{-- Customer Code --}}
                    <div class="row align-items-center">
                        <div class="col-md-6 mb-3">
                            <label for="customer_code" class="form-label">
                                <strong>{{ __('websystem.label.customer_code') }}</strong>
                            </label>
                            <input type="text" name="customer_code" id="customer_code"
                                value="{{ old('customer_code', $websystem->customer_code) }}"
                                placeholder="{{ __('websystem.placeholder.customer_code') }}"
                                class="form-control @error('customer_code') is-invalid @enderror">

                            @error('customer_code')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @else
                                <div class="form-text text-primary">
                                    *required<br>
                                    *Ex: Customer Management, you can write CM
                                </div>
                            @enderror
                        </div>
                    </div>

                    {{-- Long Customer Code --}}
                    <div class="row align-items-center">
                        <div class="col-md-6 mb-3">
                            <label for="long_customer_code" class="form-label">
                                <strong>{{ __('websystem.label.long_customer_code') }}</strong>
                            </label>
                            <input type="text" name="long_customer_code" id="long_customer_code"
                                value="{{ old('long_customer_code', $websystem->long_customer_code) }}"
                                placeholder="{{ __('websystem.placeholder.long_customer_code') }}"
                                class="form-control @error('long_customer_code') is-invalid @enderror">

                            @error('long_customer_code')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @else
                                <div class="form-text text-primary">*between 3-25</div>
                            @enderror
                        </div>
                    </div>

                    {{-- Format Preview --}}
                    @php
                        $longCode = '%0' . $websystem->long_customer_code . 'd';
                        $customerId = $websystem->customer_code . sprintf('%02d', 1) . '-' . sprintf($longCode, 1);
                    @endphp
                    <div class="row align-items-center">
                        <div class="col-md-6 mb-3">
                            <label class="form-label d-block"><strong>{{ __('Format customer id:') }}</strong></label>
                            <span class="badge bg-success text-white mb-2">{{ $customerId }}</span><br>
                            <span class="text-primary"><strong>{{ $websystem->customer_code }}</strong></span>
                            <span class="text-secondary"> : Identify your company.</span><br>
                            <span class="text-primary"><strong>{{ sprintf('%02d', 1) }}</strong></span>
                            <span class="text-secondary"> : Identify your server id.</span><br>
                            <span class="text-primary"><strong>{{ sprintf($longCode, 1) }}</strong></span>
                            <span class="text-secondary"> : Identify your customer id.</span>
                        </div>
                    </div>

                    <div class="text-secondary small">
                        Updated at :
                        {{ \Carbon\Carbon::parse($websystem->updated_at)->translatedFormat('d M Y, H:i:s') }}
                    </div>
                </div>

                <div class="card-footer">
                    <button type="submit" class="btn btn-sm btn-success">
                        <i class="fa fa-save"></i>&nbsp;{{ __('websystem.button.update') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-layouts.cpanel.settings.app-layout>
