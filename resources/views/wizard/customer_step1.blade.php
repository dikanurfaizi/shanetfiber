<x-layouts.cpanel.customers.app-layout :title="'Detail Customer'">

    {{-- Leaflet CSS & JS --}}
    @push('custom_styles')
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    @endpush

    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white py-3">
            <h5 class="mb-0 fw-bold">{{ __('customer.wizard.step.1') }}</h5>
        </div>

        <form method="POST" action="{{ route('wizard.step1') }}">
            @csrf

            <div class="card-body">

                {{-- Admin / PIC --}}
                <div class="mb-3">
                    <label for="user_id" class="form-label">
                        <strong>{{ __('customer.wizard.label.pic') }}</strong> <span class="text-danger">*</span>
                    </label>
                    <select name="user_id" id="user_id" class="form-select @error('user_id') is-invalid @enderror"
                        required>
                        <option value="">{{ __('customer.wizard.placeholder.select_pic') }}</option>
                        @foreach ($users as $u)
                            <option value="{{ $u->id }}" @selected(old('user_id', session('wizard.step1.user_id')) == $u->id)>
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
                <div class="mb-3">
                    <label for="name" class="form-label">
                        <strong>{{ __('customer.wizard.label.name') }}</strong> <span class="text-danger">*</span>
                    </label>
                    <input type="text" class="form-control @error('name') is-invalid @enderror" name="name"
                        id="name" value="{{ old('name', session('wizard.step1.name')) }}" required>

                    @error('name')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                    @enderror
                </div>

                {{-- Email --}}
                <div class="mb-3">
                    <label for="email" class="form-label">
                        <strong>{{ __('customer.wizard.label.email') }}</strong> <span class="text-danger">*</span>
                    </label>
                    <input type="email" class="form-control @error('email') is-invalid @enderror" name="email"
                        id="email" value="{{ old('email', session('wizard.step1.email')) }}" required>

                    @error('email')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                    @enderror
                </div>

                {{-- Contact --}}
                <div class="mb-3">
                    <label for="contact" class="form-label">
                        <strong>{{ __('customer.wizard.label.contact') }}</strong> <span class="text-danger">*</span>
                    </label>
                    <input type="tel" class="form-control @error('contact') is-invalid @enderror" name="contact"
                        id="contact" inputmode="numeric" pattern="[0-9]*" maxlength="15"
                        oninput="this.value=this.value.replace(/[^0-9]/g,'')"
                        value="{{ old('contact', session('wizard.step1.contact')) }}" required>

                    @error('contact')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                    @enderror
                </div>

                {{-- Address --}}
                <div class="mb-3">
                    <label for="address-input" class="form-label">
                        <strong>{{ __('customer.wizard.label.address') }}</strong>
                        <span class="text-muted fw-normal">({{ __('customer.wizard.label.optional') }})</span>
                    </label>
                    <input type="text" class="form-control" name="address" id="address-input"
                        value="{{ old('address', session('wizard.step1.address')) }}">
                </div>

                {{-- MAP PICKER SECTION --}}
                <div class="mb-3 p-3 bg-light rounded border">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <label
                            class="mb-0 form-label"><strong>{{ __('customer.wizard.label.map_picker') }}</strong></label>
                        <button type="button" class="btn btn-sm btn-outline-primary" id="btn-current-location">
                            <i class="fa fa-crosshairs me-1"></i>
                            {{ __('customer.wizard.button.use_current_location') }}
                        </button>
                    </div>

                    {{-- Container Peta --}}
                    <div id="map-picker" style="height: 320px; width: 100%; border-radius: 6px;" class="mb-3 border">
                    </div>

                    {{-- Input Coordinates --}}
                    <div class="row">
                        <div class="col-md-6 mb-2 mb-md-0">
                            <label for="latitude"
                                class="form-label small text-muted">{{ __('customer.wizard.label.latitude') }}</label>
                            <input type="text" class="form-control @error('latitude') is-invalid @enderror"
                                name="latitude" id="latitude"
                                value="{{ old('latitude', session('wizard.step1.latitude')) }}" readonly
                                placeholder="-6.20000000">
                            @error('latitude')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="longitude"
                                class="form-label small text-muted">{{ __('customer.wizard.label.longitude') }}</label>
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
                <div class="mb-3">
                    <label for="password" class="form-label">
                        <strong>{{ __('customer.wizard.label.password') }}</strong>
                        <span class="text-muted fw-normal">({{ __('customer.wizard.label.optional') }})</span>
                    </label>
                    <input type="password" class="form-control @error('password') is-invalid @enderror" name="password"
                        id="password">
                    <small
                        class="text-muted d-block mt-1">{{ __('customer.wizard.message.default_password') }}</small>
                    @error('password')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Confirm Password --}}
                <div class="mb-3">
                    <label for="password_confirmation" class="form-label">
                        <strong>{{ __('customer.wizard.label.confirm_password') }}</strong>
                    </label>
                    <input type="password" class="form-control" name="password_confirmation"
                        id="password_confirmation">
                </div>

            </div>

            <div class="card-footer text-end">
                <button type="submit" class="btn btn-primary">
                    {{ __('customer.wizard.button.next_step') }} <i class="fa fa-arrow-right ms-1"></i>
                </button>
            </div>

        </form>
    </div>

    {{-- SCRIPT JAVASCRIPT LEAFLET PICKER --}}
    @push('custom_scripts')
        <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
        <script>
            document.addEventListener("DOMContentLoaded", function() {
                const defaultLat = parseFloat("{{ old('latitude', session('wizard.step1.latitude', -6.2)) }}");
                const defaultLng = parseFloat("{{ old('longitude', session('wizard.step1.longitude', 106.816666)) }}");

                const latInput = document.getElementById('latitude');
                const lngInput = document.getElementById('longitude');

                const map = L.map('map-picker').setView([defaultLat, defaultLng], 13);

                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    maxZoom: 19,
                    attribution: '&copy; OpenStreetMap contributors'
                }).addTo(map);

                let marker = L.marker([defaultLat, defaultLng], {
                    draggable: true
                }).addTo(map);

                function updateCoordinates(lat, lng) {
                    latInput.value = lat.toFixed(8);
                    lngInput.value = lng.toFixed(8);
                }

                if (!latInput.value || !lngInput.value) {
                    updateCoordinates(defaultLat, defaultLng);
                }

                marker.on('dragend', function(e) {
                    const position = marker.getLatLng();
                    updateCoordinates(position.lat, position.lng);
                });

                map.on('click', function(e) {
                    const lat = e.latlng.lat;
                    const lng = e.latlng.lng;

                    marker.setLatLng([lat, lng]);
                    updateCoordinates(lat, lng);
                });

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
    @endpush

</x-layouts.cpanel.customers.app-layout>
