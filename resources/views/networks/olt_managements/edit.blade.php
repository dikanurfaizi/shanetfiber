<x-layouts.app-layout title="Networks">

    @section('custom_styles')
        <!-- Leaflet CSS -->
        <link href="{{ asset('css/network-mgmt/leaflet-1.9.4.css') }}" rel="stylesheet">
        <link href="{{ asset('css/network-mgmt/data-update.css') }}" rel="stylesheet">
    @endsection

    <div class="card">
        <div class="card-header bg-primary text-white">
            <h4 class="mb-0"><i class="fas fa-edit me-2"></i> {{ __('networks.header.edit-olt') }}</h4>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('olts.update', $olt->id) }}" id="olt-form">
                @csrf
                @method('PUT')
                <input type="hidden" name="id" value="{{ $olt->id }}">
                <div class="mb-4">
                    <label for="olt_name" class="form-label">{{ __('networks.label.olt-name') }}</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-tag"></i></span>
                        <input type="text" class="form-control" id="olt_name" name="olt_name"
                            value="{{ $olt->name }}" placeholder="Enter a unique name" required>
                    </div>
                    <div class="form-text">{{ __('networks.info.unique-name') }}</div>
                </div>

                <div class="row mb-4">
                    <div class="col-md-8">
                        <label for="ip_address" class="form-label">{{ __('networks.label.ip-address') }}</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-network-wired"></i></span>
                            <input type="text" class="form-control" id="ip_address" name="ip_address"
                                value="{{ $olt->ip }}" placeholder="{{ __('networks.placeholder.exp-ip') }}"
                                required>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <label for="port" class="form-label">{{ __('networks.label.port') }}</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-plug"></i></span>
                            <input type="text" class="form-control" id="port" name="port"
                                value="{{ $olt->port }}" placeholder="{{ __('networks.placeholder.exp-port') }}"
                                required>
                        </div>
                    </div>
                </div>

                <div class="mb-4">
                    <label for="community" class="form-label">{{ __('networks.label.snmp-community') }}</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-key"></i></span>
                        <input type="text" class="form-control" id="community" name="community"
                            value="{{ $olt->community }}"
                            placeholder="{{ __('networks.placeholder.exp-snmp-community') }}" required>
                    </div>
                </div>

                <div class="mb-4">
                    <label for="olt_type" class="form-label">{{ __('networks.label.olt-type') }}</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-server"></i></span>
                        {!! Form::select('olt_type', ['' => 'OLT Types'] + $oidMappings->toArray(), $olt->vendor ?? null, [
                            'class' => 'form-control',
                            'id' => 'olt_type',
                        ]) !!}
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label">{{ __('networks.label.map-location') }}</label>
                    <div id="map-container"></div>
                    <div class="form-text">{{ __('networks.info.map-info') }}</div>
                </div>

                <div class="row mb-4">
                    <div class="col-md-6">
                        <label for="latitude" class="form-label">{{ __('networks.label.latitude') }}</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-map-marker-alt"></i></span>
                            <input type="text" class="form-control" id="latitude" name="latitude" value=""
                                placeholder="{{ __('networks.placeholder.exp-latitude') }}">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label for="longitude" class="form-label">{{ __('networks.label.longitude') }}</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-map-marker-alt"></i></span>
                            <input type="text" class="form-control" id="longitude" name="longitude" value=""
                                placeholder="{{ __('networks.placeholder.exp-longitude') }}">
                        </div>
                    </div>
                </div>

                <div class="mb-4">
                    <button type="button" id="get-location-btn" class="btn btn-outline-primary">
                        <i class="fas fa-map-marker-alt me-2"></i> {{ __('networks.button.current-location') }}
                    </button>

                    <button type="button" id="reset-to-default" class="btn btn-outline-secondary ms-2"
                        style="display: none;">
                        <i class="fas fa-undo me-2"></i> Reset to Default Location
                    </button>

                    <div id="location-status" class="form-text mt-2"></div>
                </div>

                <div class="d-flex justify-content-between mt-4">
                    <a href="{{ route('olts.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-2"></i> Back
                    </a>
                    <div>
                        <button type="button" id="test-snmp" class="btn btn-info me-2">
                            <i class="fas fa-check-circle me-1"></i> Test Connection
                        </button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i> Save Changes
                        </button>
                    </div>
                </div>
            </form>

            <x-snmp-test-modal id="snmpTestModal" :title="__('networks.title.snmp-test-connection')" :close-text="__('networks.button.close')" />
        </div>
    </div>
    @section('custom_scripts')
        <script>
            window.snmpTestUrl = "{{ route('olts.test.snmp') }}";
        </script>
        <!-- Leaflet JS -->
        <script src="{{ asset('js/network-mgmt/leaflet-1.9.4.js') }}"></script>
        <script src="{{ asset('js/network-mgmt/data-update.js') }}"></script>
        <script src="{{ asset('js/network-mgmt/snmp-con-test.js') }}"></script>
    @endsection
</x-layouts.app-layout>
