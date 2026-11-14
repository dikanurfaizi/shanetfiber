<x-layouts.cpanel.customers.app-layout :title="'Detail Customer'">

    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white py-3">
            <h5 class="mb-0 fw-bold">{{ __('customer.wizard.step.2') }}</h5>
        </div>

        <form method="POST" action="{{ route('wizard.step2') }}">
            @csrf

            <div class="card-body">

                {{-- SERVER --}}
                <div class="mb-3">
                    <label for="server-dropdown" class="form-label">
                        <strong>{{ __('customer.wizard.label.mikrotik_server') }}</strong> <span
                            class="text-danger">*</span>
                    </label>
                    <select name="mikrotik_id" id="server-dropdown"
                        class="form-select @error('mikrotik_id') is-invalid @enderror" required>
                        <option value="">{{ __('customer.wizard.placeholder.select_server') }}</option>
                        @foreach ($mikrotiks as $id => $name)
                            <option value="{{ $id }}" @selected(old('mikrotik_id', session('wizard.step2.mikrotik_id')) == $id)>
                                {{ $name }}
                            </option>
                        @endforeach
                    </select>
                    @error('mikrotik_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- INTERNET SERVICE --}}
                <div class="mb-3">
                    <label for="service-dropdown" class="form-label">
                        <strong>{{ __('customer.wizard.label.internet_service') }}</strong> <span
                            class="text-danger">*</span>
                    </label>
                    <select name="internet_service" id="service-dropdown"
                        class="form-select @error('internet_service') is-invalid @enderror" required>
                        <option value="">{{ __('customer.wizard.placeholder.select_service') }}</option>
                        <option value="ppp" @selected(old('internet_service', session('wizard.step2.internet_service')) == 'ppp')>{{ __('customer.wizard.placeholder.ppp') }}
                        </option>
                        <option value="ip_static" @selected(old('internet_service', session('wizard.step2.internet_service')) == 'ip_static')>
                            {{ __('customer.wizard.placeholder.ip_static') }}</option>
                    </select>
                    @error('internet_service')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- PPP TYPE --}}
                <div id="input-ppp" class="mb-3 @if (old('internet_service', session('wizard.step2.internet_service')) !== 'ppp') d-none @endif">
                    <label for="ppp_type_id" class="form-label">
                        <strong>{{ __('customer.wizard.label.ppp_type') }}</strong> <span class="text-danger">*</span>
                    </label>
                    <select name="ppp_type_id" id="ppp_type_id"
                        class="form-select @error('ppp_type_id') is-invalid @enderror">
                        <option value="">Select PPP Type</option>
                        @foreach ($pppservices as $id => $name)
                            <option value="{{ $id }}" @selected(old('ppp_type_id', session('wizard.step2.ppp_type_id')) == $id)>
                                {{ $name }}
                            </option>
                        @endforeach
                    </select>
                    @error('ppp_type_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- IP STATIC SECTION --}}
                <div id="input-ip-static" class="@if (old('internet_service', session('wizard.step2.internet_service')) !== 'ip_static') d-none @endif">

                    {{-- Interface --}}
                    <div class="mb-3">
                        <label for="interface-dropdown" class="form-label">
                            <strong>{{ __('customer.wizard.label.interface') }}</strong> <span
                                class="text-danger">*</span>
                        </label>
                        <select name="interface" id="interface-dropdown"
                            class="form-select @error('interface') is-invalid @enderror">
                            <option value="">{{ __('customer.wizard.label.select_interface') }}</option>
                        </select>
                        @error('interface')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- IP Address --}}
                    <div class="mb-3">
                        <label for="ip_static" class="form-label">
                            <strong>{{ __('customer.wizard.label.ip_static') }}</strong> <span
                                class="text-danger">*</span>
                        </label>
                        <input type="text" name="ip_static" id="ip_static"
                            class="form-control @error('ip_static') is-invalid @enderror"
                            value="{{ old('ip_static', session('wizard.step2.ip_static')) }}"
                            placeholder="192.168.1.10">
                        @error('ip_static')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- MAC Address --}}
                    <div class="mb-3">
                        <label for="mac_address" class="form-label">
                            <strong>{{ __('customer.wizard.label.mac_address') }}</strong>
                            <span
                                class="text-muted fw-normal">({{ __('customer.wizard.label.optional') ?? 'Optional' }})</span>
                        </label>
                        <input type="text" name="mac_address" id="mac_address"
                            class="form-control @error('mac_address') is-invalid @enderror"
                            value="{{ old('mac_address', session('wizard.step2.mac_address')) }}"
                            placeholder="AA:BB:CC:DD:EE:FF">
                        @error('mac_address')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                </div>

            </div>

            <div class="card-footer d-flex justify-content-between align-items-center">
                <a href="{{ route('wizard.step1') }}" class="btn btn-secondary">
                    <i class="fa fa-arrow-left me-1"></i> {{ __('customer.wizard.button.back') }}
                </a>
                <button type="submit" class="btn btn-primary">
                    {{ __('customer.wizard.button.next_step') }} <i class="fa fa-arrow-right ms-1"></i>
                </button>
            </div>
        </form>
    </div>

    @push('custom_scripts')
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const serviceDropdown = document.getElementById('service-dropdown');
                const serverDropdown = document.getElementById('server-dropdown');
                const pppSection = document.getElementById('input-ppp');
                const staticSection = document.getElementById('input-ip-static');

                function handleServiceChange(serviceValue) {
                    if (serviceValue === 'ppp') {
                        pppSection.classList.remove('d-none');
                        staticSection.classList.add('d-none');
                    } else if (serviceValue === 'ip_static') {
                        pppSection.classList.add('d-none');
                        staticSection.classList.remove('d-none');

                        // Fetch interface if server is selected
                        if (serverDropdown.value) {
                            fetchInterfaces(serverDropdown.value);
                        }
                    } else {
                        pppSection.classList.add('d-none');
                        staticSection.classList.add('d-none');
                    }
                }

                // Initial check on load (for validation error re-renders)
                if (serviceDropdown.value) {
                    handleServiceChange(serviceDropdown.value);
                }

                serviceDropdown.addEventListener('change', function() {
                    handleServiceChange(this.value);
                });

                serverDropdown.addEventListener('change', function() {
                    if (serviceDropdown.value === 'ip_static' && this.value) {
                        fetchInterfaces(this.value);
                    }
                });

                function fetchInterfaces(serverId) {
                    if (!serverId) return;

                    fetch("{{ route('fetch.interface') }}", {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content
                            },
                            body: JSON.stringify({
                                mikrotik_id: serverId
                            })
                        })
                        .then(res => res.json())
                        .then(data => {
                            const dropdown = document.getElementById('interface-dropdown');
                            const oldInterface = "{{ old('interface', session('wizard.step2.interface')) }}";
                            dropdown.innerHTML = '<option value="">Select Interface</option>';

                            data.forEach(iface => {
                                const selected = (iface.name === oldInterface) ? 'selected' : '';
                                dropdown.innerHTML +=
                                    `<option value="${iface.name}" ${selected}>${iface.name}</option>`;
                            });
                        })
                        .catch(err => console.error('Error fetching interfaces:', err));
                }
            });
        </script>
    @endpush

</x-layouts.cpanel.customers.app-layout>
