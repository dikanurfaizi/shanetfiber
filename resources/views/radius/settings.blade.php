<x-layouts.cpanel.settings.app-layout :title="__('radius.settings.title')">
    <div class="card-header py-3">
        <h5 class="m-0 font-weight-bold text-primary">
            {{ __('radius.settings.header') }}
        </h5>
    </div>

    <div class="card-body">
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle mr-1"></i> {{ session('success') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-triangle mr-1"></i> {{ $errors->first() }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif

        <form method="POST" action="{{ route('radius.settings.update') }}">
            @csrf
            @method('PUT')

            <div class="row">
                <div class="col-md-8 col-12">
                    <div class="form-group">
                        <label class="font-weight-bold text-dark">{{ __('radius.settings.select_path_label') }}</label>

                        {{-- Option 1: Dual Provisioning (Hybrid / Both) --}}
                        <div class="card mb-3 border-left-primary shadow-sm">
                            <div class="card-body py-2">
                                <div class="custom-control custom-radio">
                                    <input type="radio" id="driverBoth" name="provisioning_driver" value="both"
                                        class="custom-control-input" {{ $currentDriver === 'both' ? 'checked' : '' }}>
                                    <label class="custom-control-label font-weight-bold text-primary" for="driverBoth">
                                        {{ __('radius.settings.driver_both_title') }}
                                    </label>
                                    <p class="small text-muted mb-0">
                                        {{ __('radius.settings.driver_both_desc') }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        {{-- Option 2: RADIUS Primary --}}
                        <div class="card mb-3 border-left-success shadow-sm">
                            <div class="card-body py-2">
                                <div class="custom-control custom-radio">
                                    <input type="radio" id="driverRadius" name="provisioning_driver" value="radius"
                                        class="custom-control-input" {{ $currentDriver === 'radius' ? 'checked' : '' }}>
                                    <label class="custom-control-label font-weight-bold text-success"
                                        for="driverRadius">
                                        {{ __('radius.settings.driver_radius_title') }}
                                    </label>
                                    <p class="small text-muted mb-0">
                                        {{ __('radius.settings.driver_radius_desc') }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        {{-- Option 3: MikroTik Native API Primary --}}
                        <div class="card mb-3 border-left-warning shadow-sm">
                            <div class="card-body py-2">
                                <div class="custom-control custom-radio">
                                    <input type="radio" id="driverMikrotik" name="provisioning_driver"
                                        value="mikrotik" class="custom-control-input"
                                        {{ $currentDriver === 'mikrotik' ? 'checked' : '' }}>
                                    <label class="custom-control-label font-weight-bold text-warning"
                                        for="driverMikrotik">
                                        {{ __('radius.settings.driver_mikrotik_title') }}
                                    </label>
                                    <p class="small text-muted mb-0">
                                        {{ __('radius.settings.driver_mikrotik_desc') }}
                                    </p>
                                </div>
                            </div>
                        </div>

                    </div>

                    <div class="mt-4">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save mr-1"></i> {{ __('radius.settings.btn_save') }}
                        </button>
                        <a href="{{ route('radius.index') }}" class="btn btn-secondary ml-2">
                            <i class="fas fa-arrow-left mr-1"></i> {{ __('radius.settings.btn_back') }}
                        </a>
                    </div>
                </div>
            </div>
        </form>
    </div>
</x-layouts.cpanel.settings.app-layout>
