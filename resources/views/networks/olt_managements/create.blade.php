<x-layouts.app-layout title="Networks">

    <div class="card-header py-3 d-flex justify-content-between align-items-center">
        <h5 class="m-0 fw-bold text-primary mb-2">
            {{ __('networks.header.add-olt') }}
        </h5>
    </div>

    <div class="card-body">
        <form method="POST" action="{{ route('olts.store') }}" id="olt-form">
            @csrf
            <div class="mb-4">
                <label for="olt_name" class="form-label">{{ __('networks.label.olt-name') }} <span
                        class="text-danger">*</span></label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-tag"></i></span>
                    <input type="text" class="form-control" id="olt_name" name="olt_name"
                        placeholder="Enter a unique name" required="">
                </div>
                <div class="form-text">{{ __('networks.info.unique-name') }}</div>
            </div>

            <div class="row mb-4">
                <div class="col-md-8">
                    <label for="ip_address" class="form-label">{{ __('networks.label.ip-address') }} <span
                            class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-network-wired"></i></span>
                        <input type="text" class="form-control" id="ip_address" name="ip_address"
                            placeholder="{{ __('networks.placeholder.exp-ip') }}" required="">
                    </div>
                </div>
                <div class="col-md-4">
                    <label for="port" class="form-label">{{ __('networks.label.port') }}</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-plug"></i></span>
                        <input type="text" class="form-control" id="port" name="port"
                            placeholder="{{ __('networks.placeholder.exp-port') }}" required="">
                    </div>
                </div>
            </div>

            <div class="mb-4">
                <label for="community" class="form-label">{{ __('networks.label.snmp-community') }}</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-key"></i></span>
                    <input type="text" class="form-control" id="community" name="community"
                        placeholder="{{ __('networks.placeholder.exp-snmp-community') }}" required="">
                </div>
            </div>

            <div class="mb-4">
                <label for="olt_type" class="form-label">{{ __('networks.label.olt-type') }} <span
                        class="text-danger">*</span></label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-server"></i></span>
                    {!! Form::select('olt_type', ['' => 'OLT Types'] + $oidMappings->toArray(), $olt->vendor ?? null, [
                        'class' => 'form-control',
                        'id' => 'olt_type',
                    ]) !!}
                </div>
            </div>

            <div class="d-flex justify-content-between mt-4">
                <a href="{{ route('olts.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-2"></i> {{ __('networks.button.back') }}
                </a>
                <div>
                    <button type="button" id="test-snmp" class="btn btn-info me-2">
                        <i class="fas fa-check-circle me-1"></i> {{ __('networks.button.test-connection') }}
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-plus-circle me-1"></i> {{ __('networks.button.add-olt') }}
                    </button>
                </div>
            </div>
        </form>

        <x-snmp-test-modal id="snmpTestModal" :title="__('networks.title.snmp-test-connection')" :close-text="__('networks.button.close')" />
    </div>

    @section('custom_scripts')
        <script src="{{ asset('js/network-mgmt/snmp-con-test.js') }}"></script>
    @endsection
    </x-layouts.cpanel.customers.app-layout>
