<x-layouts.cpanel.customers.app-layout :title="'Detail Customer'">

    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">{{ __('customer.wizard.step.2') }}</h5>
        </div>

        <form method="POST" action="{{ route('wizard.step2') }}">
            @csrf

            <div class="card-body">

                {{-- SERVER --}}
                <div class="form-group mb-3">
                    <label><strong>{{ __('customer.wizard.label.mikrotik_server') }}</strong> <span
                            class="text-danger">*</span></label>
                    <select name="mikrotik_id" id="server-dropdown" class="form-control">
                        <option value="">{{ __('customer.wizard.placeholder.select_server') }}</option>
                        @foreach ($mikrotiks as $id => $name)
                            <option value="{{ $id }}">{{ $name }}</option>
                        @endforeach
                    </select>
                    @error('mikrotik_id')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>

                {{-- INTERNET SERVICE --}}
                <div class="form-group mb-3">
                    <label><strong>{{ __('customer.wizard.label.internet_service') }}</strong> <span
                            class="text-danger">*</span></label>
                    <select name="internet_service" id="service-dropdown" class="form-control">
                        <option value="">{{ __('customer.wizard.placeholder.select_service') }}</option>
                        <option value="ppp">{{ __('customer.wizard.placeholder.ppp') }}</option>
                        <option value="ip_static">{{ __('customer.wizard.placeholder.ip_static') }}</option>
                    </select>
                    @error('internet_service')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>

                {{-- PPP TYPE --}}
                <div id="input-ppp" class="form-group mb-3 d-none">
                    <label><strong>{{ __('customer.wizard.label.ppp_type') }}</strong></label>
                    <select name="ppp_type_id" id="ppp_type_id" class="form-control">
                        <option value="">Select PPP Type</option>
                        @foreach ($pppservices as $id => $name)
                            <option value="{{ $id }}">{{ $name }}</option>
                        @endforeach
                    </select>
                    @error('ppp_type_id')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>

                {{-- IP STATIC SECTION --}}
                <div id="input-ip-static" class="d-none">

                    {{-- Interface --}}
                    <div class="form-group mb-3">
                        <label><strong>{{ __('customer.wizard.label.interface') }}</strong></label>
                        <select name="interface" id="interface-dropdown" class="form-control">
                            <option value="">{{ __('customer.wizard.label.select_interface') }}</option>
                        </select>
                        @error('interface')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>

                    {{-- IP Address --}}
                    <div class="form-group mb-3">
                        <label><strong>{{ __('customer.wizard.label.ip_static') }}</strong></label>
                        <input type="text" name="ip_static" class="form-control" placeholder="192.168.1.10">
                        @error('ip_static')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>

                    {{-- MAC Address --}}
                    <div class="form-group mb-3">
                        <label><strong>{{ __('customer.wizard.label.mac_address') }}</strong></label>
                        <input type="text" name="mac_address" class="form-control" placeholder="AA:BB:CC:DD:EE:FF">
                        @error('mac_address')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>

                </div>

            </div>

            <div class="card-footer text-end">
                <a href="{{ route('wizard.step1') }}"
                    class="btn btn-secondary">{{ __('customer.wizard.button.back') }}</a>
                <button class="btn btn-primary">{{ __('customer.wizard.button.next_step') }}</button>
            </div>
        </form>
    </div>

    @section('custom_scripts')
        <script>
            document.addEventListener('DOMContentLoaded', () => {

                const serviceDropdown = document.getElementById('service-dropdown');
                const serverDropdown = document.getElementById('server-dropdown');
                const pppSection = document.getElementById('input-ppp');
                const staticSection = document.getElementById('input-ip-static');

                serviceDropdown.addEventListener('change', function() {
                    if (this.value === 'ppp') {
                        pppSection.classList.remove('d-none');
                        staticSection.classList.add('d-none');
                    } else if (this.value === 'ip_static') {
                        pppSection.classList.add('d-none');
                        staticSection.classList.remove('d-none');

                        // Fetch interface
                        fetchInterfaces(serverDropdown.value);
                    } else {
                        pppSection.classList.add('d-none');
                        staticSection.classList.add('d-none');
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
                            dropdown.innerHTML = '<option value="">Select Interface</option>';

                            data.forEach(iface => {
                                dropdown.innerHTML +=
                                    `<option value="${iface.name}">${iface.name}</option>`;
                            });
                        });
                }

            });
        </script>
    @endsection

</x-layouts.cpanel.customers.app-layout>
