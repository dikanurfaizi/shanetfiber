<x-layouts.cpanel.customers.app-layout :title="'Detail Customer'">

    {{-- Leaflet CSS & JS --}}
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">{{ __('customer.wizard.step.1') }}</h5>
        </div>

        <form method="POST" action="{{ route('wizard.step1') }}">
            @csrf

            <div class="card-body">

                {{-- Admin --}}
                <div class="form-group mb-3">
                    <label><strong>{{ __('customer.wizard.label.pic') }}</strong> <span
                            class="text-danger">*</span></label>
                    <select name="user_id" class="form-control @error('user_id') is-invalid @enderror" required>
                        <option value="">{{ __('customer.wizard.placeholder.select_pic') }}</option>
                        @foreach ($users as $u)
                            <option value="{{ $u->id }}"
                                {{ old('user_id', session('wizard.step1.user_id')) == $u->id ? 'selected' : '' }}>
                                {{ $u->name }}
                            </option>
                        @endforeach
                    </select>

                    @error('user_id')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                    @enderror
                </div>

                {{-- Name --}}
                <div class="form-group mb-3">
                    <label><strong>{{ __('customer.wizard.label.name') }}</strong> <span
                            class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('name') is-invalid @enderror" name="name"
                        value="{{ old('name', session('wizard.step1.name')) }}">

                    @error('name')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                    @enderror
                </div>

                {{-- Email --}}
                <div class="form-group mb-3">
                    <label><strong>{{ __('customer.wizard.label.email') }}</strong> <span
                            class="text-danger">*</span></label>
                    <input type="email" class="form-control @error('email') is-invalid @enderror" name="email"
                        value="{{ old('email', session('wizard.step1.email')) }}">

                    @error('email')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                    @enderror
                </div>

                {{-- Contact --}}
                <div class="form-group mb-3">
                    <label><strong>{{ __('customer.wizard.label.contact') }}</strong> <span
                            class="text-danger">*</span></label>

                    <input type="tel" class="form-control @error('contact') is-invalid @enderror" name="contact"
                        inputmode="numeric" pattern="[0-9]*" maxlength="15"
                        oninput="this.value=this.value.replace(/[^0-9]/g,'')"
                        value="{{ old('contact', session('wizard.step1.contact')) }}">

                    @error('contact')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                    @enderror
                </div>

                {{-- Address --}}
                <div class="form-group mb-3">
                    <label><strong>{{ __('customer.wizard.label.address') }}</strong>
                        {{ __('customer.wizard.label.optional') }}</label>
                    <input type="text" class="form-control" name="address" id="address-input"
                        value="{{ old('address', session('wizard.step1.address')) }}">
                </div>

                {{-- MAP PICKER SECTION --}}
                <div class="form-group mb-3 p-3 bg-light rounded border">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <label class="mb-0"><strong>{{ __('customer.wizard.label.map_picker') }}</strong></label>
                        <button type="button" class="btn btn-sm btn-outline-primary" id="btn-current-location">
                            <i class="fa fa-crosshairs mr-1"></i>
                            {{ __('customer.wizard.button.use_current_location') }}
                        </button>
                    </div>

                    {{-- Container Peta --}}
                    <div id="map-picker" style="height: 320px; width: 100%; border-radius: 6px;" class="mb-3 border">
                    </div>

                    {{-- Input Coordinates (Readonly / Editable) --}}
                    <div class="row">
                        <div class="col-md-6 mb-2 mb-md-0">
                            <label class="small text-muted">{{ __('customer.wizard.label.latitude') }}</label>
                            <input type="text" class="form-control @error('latitude') is-invalid @enderror"
                                name="latitude" id="latitude"
                                value="{{ old('latitude', session('wizard.step1.latitude')) }}" readonly
                                placeholder="-6.20000000">
                            @error('latitude')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="small text-muted">{{ __('customer.wizard.label.longitude') }}</label>
                            <input type="text" class="form-control @error('longitude') is-invalid @enderror"
                                name="longitude" id="longitude"
                                value="{{ old('longitude', session('wizard.step1.longitude')) }}" readonly
                                placeholder="106.81666600">
                            @error('longitude')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                {{-- Password --}}
                <div class="form-group mb-3">
                    <label><strong>{{ __('customer.wizard.label.password') }}</strong>
                        {{ __('customer.wizard.label.optional') }}</label>
                    <input type="password" class="form-control" name="password">
                    <small class="text-muted">{{ __('customer.wizard.message.default_password') }}</small>
                    @error('password')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>

                {{-- Confirm Password --}}
                <div class="form-group mb-3">
                    <label><strong>{{ __('customer.wizard.label.confirm_password') }}</strong></label>
                    <input type="password" class="form-control" name="password_confirmation">
                </div>

            </div>

            <div class="card-footer text-right">
                <button class="btn btn-primary">{{ __('customer.wizard.button.next_step') }}</button>
            </div>

        </form>
    </div>

    {{-- SCRIPT JAVASCRIPT LEAFLET PICKER --}}
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            // Default koordinat (Jakarta) jika belum ada session / input sebelumnya
            const defaultLat = parseFloat("{{ old('latitude', session('wizard.step1.latitude', -6.2)) }}");
            const defaultLng = parseFloat("{{ old('longitude', session('wizard.step1.longitude', 106.816666)) }}");

            const latInput = document.getElementById('latitude');
            const lngInput = document.getElementById('longitude');

            // Inisialisasi Peta
            const map = L.map('map-picker').setView([defaultLat, defaultLng], 13);

            // Layer OpenStreetMap
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '&copy; OpenStreetMap contributors'
            }).addTo(map);

            // Inisialisasi Marker Draggable (Dapat Digeser)
            let marker = L.marker([defaultLat, defaultLng], {
                draggable: true
            }).addTo(map);

            // Update nilai input saat marker di-drag
            function updateCoordinates(lat, lng) {
                latInput.value = lat.toFixed(8);
                lngInput.value = lng.toFixed(8);
            }

            // Jalankan update nilai awal jika input masih kosong
            if (!latInput.value || !lngInput.value) {
                updateCoordinates(defaultLat, defaultLng);
            }

            // Event saat marker selesai ditarik/digeser
            marker.on('dragend', function(e) {
                const position = marker.getLatLng();
                updateCoordinates(position.lat, position.lng);
            });

            // Event saat area peta diklik
            map.on('click', function(e) {
                const lat = e.latlng.lat;
                const lng = e.latlng.lng;

                marker.setLatLng([lat, lng]);
                updateCoordinates(lat, lng);
            });

            // Tombol "Gunakan Lokasi Saya" (Geolocation API Browser dengan SweetAlert2)
            document.getElementById('btn-current-location').addEventListener('click', function() {
                const btn = this;
                btn.disabled = true;

                if ("geolocation" in navigator) {
                    navigator.geolocation.getCurrentPosition(
                        function(position) {
                            const userLat = position.coords.latitude;
                            const userLng = position.coords.longitude;

                            map.setView([userLat, userLng], 16);
                            marker.setLatLng([userLat, userLng]);
                            updateCoordinates(userLat, userLng);

                            btn.disabled = false;
                        },
                        function(error) {
                            btn.disabled = false;

                            // Peta pesan error Geolocation berdasarkan error code
                            let errorMessage =
                            "{{ __('customer.wizard.message.geolocation_failed') }}";
                            if (error.code === error.PERMISSION_DENIED) {
                                errorMessage =
                                "{{ __('customer.wizard.message.geolocation_denied') }}";
                            }

                            Swal.fire({
                                icon: 'error',
                                title: "{{ __('customer.wizard.message.oops') }}",
                                text: errorMessage + " (" + error.message + ")",
                                confirmButtonColor: '#0d6efd'
                            });
                        }, {
                            enableHighAccuracy: true,
                            timeout: 10000,
                            maximumAge: 0
                        }
                    );
                } else {
                    btn.disabled = false;

                    Swal.fire({
                        icon: 'warning',
                        title: "{{ __('customer.wizard.message.not_supported') }}",
                        text: "{{ __('customer.wizard.message.geolocation_unsupported') }}",
                        confirmButtonColor: '#0d6efd'
                    });
                }
            });
        });
    </script>

</x-layouts.cpanel.customers.app-layout>
