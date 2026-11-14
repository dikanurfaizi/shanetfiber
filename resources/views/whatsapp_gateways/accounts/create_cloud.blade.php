<x-layouts.cpanel.settings.app-layout :title="__('Add Cloud API Account')">

    <div class="container-fluid px-4 py-4">
        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-header bg-white py-3">
                <h5 class="m-0 fw-bold text-primary">
                    Add Cloud API Account ({{ $whatsappgateway->name }})
                </h5>
            </div>

            <div class="card-body p-4">
                <form action="{{ route('whatsappgateway.accounts.store', $whatsappgateway->slug) }}" method="POST">
                    @csrf

                    {{-- GENERAL --}}
                    <h6 class="fw-bold text-muted mb-3">General Settings</h6>

                    <div class="mb-3">
                        <label class="form-label text-muted">Purpose</label>
                        <input type="text" name="purpose" class="form-control" value="{{ old('purpose') }}"
                            placeholder="billing / notification">
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-muted">Description</label>
                        <textarea name="description" class="form-control" rows="3">{{ old('description') }}</textarea>
                    </div>

                    <div class="mb-3 form-check">
                        <input type="checkbox" name="is_active" value="1" class="form-check-input"
                            id="isActiveCheck" @checked(old('is_active', true))>
                        <label class="form-check-label text-muted" for="isActiveCheck">Active</label>
                    </div>

                    <hr class="my-4">

                    {{-- CLOUD API BASE --}}
                    <h6 class="fw-bold text-muted mb-3">Cloud API Settings</h6>

                    <div class="mb-3">
                        <label class="form-label text-muted">WABA ID</label>
                        <input type="text" name="meta_waba_id" class="form-control" value="{{ old('meta_waba_id') }}"
                            required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-muted">Graph Version</label>
                        <input type="text" name="meta_graph_version" class="form-control"
                            value="{{ old('meta_graph_version', 'v21.0') }}">
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-muted">Graph Base URL</label>
                        <input type="text" name="meta_graph_url" class="form-control"
                            value="{{ old('meta_graph_url', 'https://graph.facebook.com') }}">
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-muted">Access Token</label>
                        <input type="password" name="meta_user_access_token" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-muted">Webhook Verify Token</label>
                        <input type="text" name="meta_webhook_verify_token" class="form-control"
                            value="{{ old('meta_webhook_verify_token') }}">
                    </div>

                    <hr class="my-4">

                    {{-- PHONE NUMBERS DYNAMIC --}}
                    <h6 class="fw-bold text-muted mb-3">Phone Numbers (Cloud API)</h6>

                    <div id="phone_numbers_container">
                        {{-- FIRST ROW --}}
                        <div class="phone-number-group border rounded p-3 mb-3 bg-light">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <strong class="text-secondary">Phone Number</strong>
                                <button type="button" class="btn btn-sm btn-outline-danger btn-remove" disabled>
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>

                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label class="form-label text-muted fw-bold">Meta Phone Number ID</label>
                                    <input type="text" name="phone_numbers[0][meta_phone_number_id]"
                                        class="form-control" placeholder="1234567890" required>
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label class="form-label text-muted fw-bold">Display Number (optional)</label>
                                    <input type="text" name="phone_numbers[0][number]" class="form-control"
                                        placeholder="628xxxxxx">
                                </div>

                                <div class="col-md-3 mb-3">
                                    <label class="form-label text-muted fw-bold">Country</label>
                                    <input type="text" name="phone_numbers[0][country_code]" class="form-control"
                                        value="62">
                                </div>

                                <div class="col-md-1 mb-3">
                                    <label class="form-label text-muted fw-bold me-2">Default</label>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="default_phone_number"
                                            value="0" checked>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <button type="button" id="add_phone_button" class="btn btn-outline-primary mb-4">
                        <i class="fas fa-plus me-1"></i> Add Phone Number
                    </button>

                    <hr class="my-4">

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-success"><i class="fas fa-save me-1"></i> Save</button>
                        <a href="{{ route('whatsappgateway.show', $whatsappgateway->slug) }}"
                            class="btn btn-secondary">Back</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('custom_scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                let index = 1;

                document.getElementById('add_phone_button').addEventListener('click', function() {
                    const group = `
                    <div class="phone-number-group border rounded p-3 mb-3 bg-light">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <strong class="text-secondary">Phone Number</strong>
                            <button type="button" class="btn btn-sm btn-outline-danger btn-remove">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label text-muted fw-bold">Meta Phone Number ID</label>
                                <input type="text" name="phone_numbers[${index}][meta_phone_number_id]" class="form-control" required>
                            </div>

                            <div class="col-md-4 mb-3">
                                <label class="form-label text-muted fw-bold">Display Number (optional)</label>
                                <input type="text" name="phone_numbers[${index}][number]" class="form-control">
                            </div>

                            <div class="col-md-3 mb-3">
                                <label class="form-label text-muted fw-bold">Country</label>
                                <input type="text" name="phone_numbers[${index}][country_code]" class="form-control" value="62">
                            </div>

                            <div class="col-md-1 mb-3">
                                <label class="form-label text-muted fw-bold me-2">Default</label>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="default_phone_number" value="${index}">
                                </div>
                            </div>
                        </div>
                    </div>`;

                    document.getElementById('phone_numbers_container').insertAdjacentHTML('beforeend', group);
                    index++;
                });

                document.addEventListener('click', function(e) {
                    if (e.target.closest('.btn-remove')) {
                        const group = e.target.closest('.phone-number-group');

                        if (document.querySelectorAll('.phone-number-group').length === 1) {
                            alert("At least 1 phone number is required.");
                            return;
                        }

                        group.remove();
                    }
                });
            });
        </script>
    @endpush

</x-layouts.cpanel.settings.app-layout>
