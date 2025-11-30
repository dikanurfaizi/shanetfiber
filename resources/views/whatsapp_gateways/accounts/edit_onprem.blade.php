<x-layouts.cpanel.settings.app-layout title="Edit Gowa Account">

    <div class="card-header py-3">
        <h5 class="m-0 font-weight-bold text-primary">Edit Gowa Account</h5>
    </div>

    <div class="card mt-4 shadow-sm p-4">

        <form action="{{ route('whatsappgateway.accounts.update', [$whatsappgateway->slug, $account->id]) }}"
            method="POST">
            @csrf
            @method('PUT')

            {{-- General Settings --}}
            <h6 class="font-weight-bold text-muted mb-3">General Settings</h6>

            <div class="mb-3 row">
                <label class="col-md-3 col-form-label text-muted">Purpose</label>
                <div class="col-md-9">
                    <input type="text" name="purpose" class="form-control"
                        value="{{ old('purpose', $account->purpose) }}" placeholder="billing / notification / default">
                </div>
            </div>

            <div class="mb-3 row">
                <label class="col-md-3 col-form-label text-muted">Description</label>
                <div class="col-md-9">
                    <textarea name="description" class="form-control" rows="2">{{ old('description', $account->description) }}</textarea>
                </div>
            </div>

            {{-- Status --}}
            <div class="mb-3 row">
                <label class="col-md-3 col-form-label text-muted">Account Status</label>
                <div class="col-md-9">
                    <div class="form-switch">
                        <input type="checkbox" class="form-check-input" name="is_active" value="1"
                            @checked($account->is_active)>
                        <label class="ms-2">Active</label>
                    </div>
                </div>
            </div>

            <hr class="my-4">

            {{-- Gowa On-Prem Settings --}}
            <h6 class="font-weight-bold text-muted mb-3">Gowa On-Prem Settings</h6>

            <div class="mb-3 row">
                <label class="col-md-3 col-form-label text-muted">Base URL</label>
                <div class="col-md-9">
                    <input type="text" name="base_url" class="form-control"
                        value="{{ old('base_url', $account->base_url) }}" placeholder="https://gowa.local/api">
                </div>
            </div>

            <div class="mb-3 row">
                <label class="col-md-3 col-form-label text-muted">Webhook Secret</label>
                <div class="col-md-9">
                    <input type="text" name="webhook_secret" class="form-control"
                        value="{{ old('webhook_secret', $account->webhook_secret) }}">
                </div>
            </div>

            {{-- Authentication Toggle --}}
            <div class="mb-3 row">
                <label class="col-md-3 col-form-label text-muted">Enable Auth</label>
                <div class="col-md-9">
                    <div class="form-switch">
                        <input type="checkbox" id="auth_enabled" name="auth_enabled" value="1"
                            class="form-check-input" @checked($account->auth_enabled)>
                        <label class="ms-2">Use Basic Authentication</label>
                    </div>
                </div>
            </div>

            {{-- Authentication Fields --}}
            <div id="auth_fields">
                <div class="mb-3 row">
                    <label class="col-md-3 col-form-label text-muted">Auth Username</label>
                    <div class="col-md-9">
                        <input type="text" name="basic_auth_username" id="basic_auth_username" class="form-control"
                            value="{{ old('basic_auth_username', $account->basic_auth_username) }}">
                    </div>
                </div>

                <div class="mb-3 row">
                    <label class="col-md-3 col-form-label text-muted">Auth Password</label>
                    <div class="col-md-9">
                        <input type="password" name="basic_auth_password" id="basic_auth_password" class="form-control"
                            placeholder="••••••••">
                    </div>
                </div>
            </div>

            {{-- Certificate --}}
            <div class="mb-3 row">
                <label class="col-md-3 col-form-label text-muted">Cert File</label>
                <div class="col-md-9">
                    <input type="text" name="cert_file" class="form-control"
                        value="{{ old('cert_file', $account->cert_file) }}" placeholder="/path/to/cert.pem">
                </div>
            </div>

            <hr class="my-4">

            <button type="submit" class="btn btn-success">
                <i class="fa fa-save"></i> Save Changes
            </button>

            <a href="{{ route('whatsappgateway.show', $whatsappgateway->slug) }}" class="btn btn-secondary ms-2">
                <i class="fa fa-arrow-left"></i> Back
            </a>

        </form>

    </div>

    {{-- JavaScript Logic --}}
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
