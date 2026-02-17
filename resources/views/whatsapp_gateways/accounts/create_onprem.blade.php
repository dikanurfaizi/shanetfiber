<x-layouts.cpanel.settings.app-layout title="Add Gowa Account">

    <div class="card-header py-3">
        <h5 class="m-0 font-weight-bold text-primary">Add Gowa Account</h5>
    </div>

    <div class="card mt-4 shadow-sm p-4">

        <form action="{{ route('whatsappgateway.accounts.store', $whatsappgateway->slug) }}" method="POST">
            @csrf

            {{-- General --}}
            <h6 class="font-weight-bold text-muted mb-3">General Settings</h6>

            <div class="mb-3 row">
                <label class="col-md-3 col-form-label text-muted">Purpose</label>
                <div class="col-md-9">
                    <input type="text" name="purpose" class="form-control"
                        placeholder="billing / notification / default" value="{{ old('purpose') }}">
                </div>
            </div>

            <div class="mb-3 row">
                <label class="col-md-3 col-form-label text-muted">Description</label>
                <div class="col-md-9">
                    <textarea name="description" class="form-control" rows="2">{{ old('description') }}</textarea>
                </div>
            </div>

            {{-- STATUS --}}
            <div class="mb-3 row">
                <label class="col-md-3 col-form-label text-muted">Active</label>
                <div class="col-md-9">
                    <input type="checkbox" name="is_active" value="1" class="form-check-input ml-1">
                </div>
            </div>

            <hr class="my-4">

            {{-- Number Settings --}}
            <h6 class="font-weight-bold text-muted mb-3">WhatsApp Number</h6>

            <div class="mb-3 row">
                <label class="col-md-3 col-form-label text-muted">Phone Number</label>
                <div class="col-md-9">
                    <input type="text" name="number" class="form-control" placeholder="6285xxxxxx"
                        value="{{ old('number') }}" required>
                </div>
            </div>

            <div class="mb-3 row">
                <label class="col-md-3 col-form-label text-muted">Country Code</label>
                <div class="col-md-9">
                    <input type="text" name="country_code" class="form-control" placeholder="62"
                        value="{{ old('country_code', '62') }}">
                </div>
            </div>

            <div class="mb-3 row">
                <label class="col-md-3 col-form-label text-muted">
                    Device ID <span class="text-danger">*</span>
                </label>
                <div class="col-md-9">
                    <input type="text" name="device_id" class="form-control"
                        placeholder="62851xxxxxxxx@s.whatsapp.net" value="{{ old('device_id') }}" required>
                    <small class="text-muted">
                        Ambil dari Gowa Device Management API (/devices)
                    </small>
                </div>
            </div>


            {{-- Gowa Settings --}}
            <h6 class="font-weight-bold text-muted mb-3 mt-4">Gowa On-Prem Settings</h6>

            <div class="mb-3 row">
                <label class="col-md-3 col-form-label text-muted">Base URL <span class="text-danger">*</span></label>
                <div class="col-md-9">
                    <input type="text" name="base_url" class="form-control" placeholder="https://gowa.local/api"
                        value="{{ old('base_url') }}">
                </div>
            </div>

            <div class="mb-3 row">
                <label class="col-md-3 col-form-label text-muted">Webhook Secret</label>
                <div class="col-md-9">
                    <input type="text" name="webhook_secret" class="form-control"
                        value="{{ old('webhook_secret') }}">
                </div>
            </div>

            {{-- AUTH TOGGLE --}}
            <h6 class="font-weight-bold text-muted mb-2">Basic Authentication</h6>

            <div class="mb-3 row">
                <label class="col-md-3 col-form-label text-muted">Enable Auth</label>
                <div class="col-md-9">
                    <input type="checkbox" id="auth_enabled" name="auth_enabled" value="1"
                        class="form-check-input ml-1">
                </div>
            </div>

            <div id="auth_fields">
                <div class="mb-3 row">
                    <label class="col-md-3 col-form-label text-muted">Auth Username</label>
                    <div class="col-md-9">
                        <input type="text" name="basic_auth_username" id="basic_auth_username" class="form-control">
                    </div>
                </div>

                <div class="mb-3 row">
                    <label class="col-md-3 col-form-label text-muted">Auth Password</label>
                    <div class="col-md-9">
                        <input type="password" name="basic_auth_password" id="basic_auth_password" class="form-control">
                    </div>
                </div>
            </div>

            <div class="mb-3 row">
                <label class="col-md-3 col-form-label text-muted">Cert File</label>
                <div class="col-md-9">
                    <input type="text" name="cert_file" class="form-control" placeholder="/path/to/cert.pem"
                        value="{{ old('cert_file') }}">
                </div>
            </div>

            <hr class="my-4">

            <button type="submit" class="btn btn-success">Save</button>
            <a href="{{ route('whatsappgateway.show', $whatsappgateway->slug) }}"
                class="btn btn-secondary ms-2">Back</a>

        </form>
    </div>

    <script>
        function toggleAuthFields() {
            const enabled = document.getElementById('auth_enabled').checked;
            document.getElementById('basic_auth_username').disabled = !enabled;
            document.getElementById('basic_auth_password').disabled = !enabled;
        }
        document.addEventListener('DOMContentLoaded', toggleAuthFields);
        document.getElementById('auth_enabled').addEventListener('change', toggleAuthFields);
    </script>

</x-layouts.cpanel.settings.app-layout>
