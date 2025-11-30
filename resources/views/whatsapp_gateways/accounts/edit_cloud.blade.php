<x-layouts.cpanel.settings.app-layout title="Edit Cloud API Account">

    <div class="card-header py-3">
        <h5 class="m-0 font-weight-bold text-primary">
            Edit Cloud API Account ({{ $whatsappgateway->name }})
        </h5>
    </div>

    <div class="card mt-4 shadow-sm p-4">
        <form action="{{ route('whatsappgateway.accounts.update', [$whatsappgateway->slug, $account->id]) }}"
            method="POST">
            @csrf
            @method('PUT')

            {{-- GENERAL --}}
            <h6 class="font-weight-bold text-muted mb-3">General Settings</h6>

            <div class="mb-3">
                <label class="text-muted">Purpose</label>
                <input type="text" name="purpose" class="form-control"
                    value="{{ old('purpose', $account->purpose) }}">
            </div>

            <div class="mb-3">
                <label class="text-muted">Description</label>
                <textarea name="description" class="form-control">{{ old('description', $account->description) }}</textarea>
            </div>

            <div class="mb-3">
                <label class="text-muted">Active</label> <br>
                <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $account->is_active))>
            </div>

            <hr class="my-4">

            {{-- CLOUD FIELDS --}}
            <h6 class="font-weight-bold text-muted mb-3">Cloud API Settings</h6>

            <div class="mb-3">
                <label class="text-muted">WABA ID</label>
                <input type="text" name="meta_waba_id" class="form-control"
                    value="{{ old('meta_waba_id', $account->meta_waba_id) }}" required>
            </div>

            <div class="mb-3">
                <label class="text-muted">Graph Version</label>
                <input type="text" name="meta_graph_version" class="form-control"
                    value="{{ old('meta_graph_version', $account->meta_graph_version) }}">
            </div>

            <div class="mb-3">
                <label class="text-muted">Graph Base URL</label>
                <input type="text" name="meta_graph_url" class="form-control"
                    value="{{ old('meta_graph_url', $account->meta_graph_url) }}">
            </div>

            <div class="mb-3">
                <label class="text-muted">Access Token</label>
                <input type="password" name="meta_user_access_token" class="form-control"
                    placeholder="Leave blank to keep current token">
            </div>

            <div class="mb-3">
                <label class="text-muted">Webhook Verify Token</label>
                <input type="text" name="meta_webhook_verify_token" class="form-control"
                    value="{{ old('meta_webhook_verify_token', $account->meta_webhook_verify_token) }}">
            </div>

            <hr class="my-4">

            {{-- PHONE NUMBERS --}}
            <h6 class="font-weight-bold text-muted mb-3">Phone Numbers (Cloud API)</h6>

            <div id="phone_numbers_container">

                @foreach ($account->numbers as $idx => $num)
                    <div class="phone-number-group border rounded p-3 mb-3 bg-light">

                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <strong class="text-secondary">Phone Number</strong>
                            <button type="button" class="btn btn-sm btn-outline-danger btn-remove"
                                @disabled($account->numbers->count() == 1)>
                                <i class="fa fa-trash"></i>
                            </button>
                        </div>

                        <div class="row">

                            <div class="col-md-4 mb-3">
                                <label class="form-label text-muted fw-bold">Meta Phone Number ID</label>
                                <input type="text" name="phone_numbers[{{ $idx }}][meta_phone_number_id]"
                                    class="form-control" value="{{ $num->meta_phone_number_id }}" required>
                            </div>

                            <div class="col-md-4 mb-3">
                                <label class="form-label text-muted fw-bold">Display Number</label>
                                <input type="text" name="phone_numbers[{{ $idx }}][number]"
                                    class="form-control" value="{{ $num->number }}">
                            </div>

                            <div class="col-md-3 mb-3">
                                <label class="form-label text-muted fw-bold">Country</label>
                                <input type="text" name="phone_numbers[{{ $idx }}][country_code]"
                                    class="form-control" value="{{ $num->country_code }}">
                            </div>

                            <div class="col-md-1 mb-3">
                                <label class="form-label text-muted fw-bold me-2">Default</label>
                                <div class="form-check">
                                    <input type="radio" class="form-check-input" name="default_phone_number"
                                        value="{{ $idx }}" @checked($num->is_default)>
                                </div>
                            </div>

                        </div>

                    </div>
                @endforeach

            </div>

            <button type="button" id="add_phone_button" class="btn btn-outline-primary mb-4">
                + Add Phone Number
            </button>

            <hr class="my-4">

            <button class="btn btn-success"><i class="fa fa-save"></i> Update</button>
            <a href="{{ route('whatsappgateway.show', $whatsappgateway->slug) }}" class="btn btn-secondary">Back</a>

        </form>
    </div>

    <script>
        let index = {{ $account->numbers->count() }};

        document.getElementById('add_phone_button').addEventListener('click', function() {

            const group = `
<div class="phone-number-group border rounded p-3 mb-3 bg-light">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <strong class="text-secondary">Phone Number</strong>
        <button type="button" class="btn btn-sm btn-outline-danger btn-remove">
            <i class="fa fa-trash"></i>
        </button>
    </div>

    <div class="row">

        <div class="col-md-4 mb-3">
            <label class="form-label text-muted fw-bold">Meta Phone Number ID</label>
            <input type="text" name="phone_numbers[${index}][meta_phone_number_id]"
                   class="form-control" required>
        </div>

        <div class="col-md-4 mb-3">
            <label class="form-label text-muted fw-bold">Display Number</label>
            <input type="text" name="phone_numbers[${index}][number]" class="form-control">
        </div>

        <div class="col-md-3 mb-3">
            <label class="form-label text-muted fw-bold">Country</label>
            <input type="text" name="phone_numbers[${index}][country_code]"
                   class="form-control" value="62">
        </div>

        <div class="col-md-1 mb-3">
            <label class="form-label text-muted fw-bold me-2">Default</label>
            <div class="form-check">
                <input class="form-check-input" type="radio"
                       name="default_phone_number" value="${index}">
            </div>
        </div>

    </div>
</div>
`;

            document.getElementById('phone_numbers_container')
                .insertAdjacentHTML('beforeend', group);

            index++;
        });

        document.addEventListener('click', function(e) {
            if (e.target.closest('.btn-remove')) {
                if (document.querySelectorAll('.phone-number-group').length === 1) {
                    alert("At least 1 phone number is required.");
                    return;
                }
                e.target.closest('.phone-number-group').remove();
            }
        });
    </script>

</x-layouts.cpanel.settings.app-layout>
