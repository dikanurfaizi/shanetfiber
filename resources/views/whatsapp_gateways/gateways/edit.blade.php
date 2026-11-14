<x-layouts.cpanel.settings.app-layout title="Edit Provider">

    <div class="card-header py-3">
        <h5 class="m-0 fw-bold text-primary">Edit Provider</h5>
    </div>

    <div class="card mt-4 shadow-sm p-4">

        <form action="{{ route('whatsappgateway.update', $gateway->slug) }}" method="POST">
            @csrf
            @method('PUT')

            {{-- General Information --}}
            <h6 class="fw-bold text-muted mb-3">Provider Information</h6>

            {{-- Provider Name --}}
            <div class="mb-3 row">
                <label class="col-md-3 col-form-label text-muted">Name</label>
                <div class="col-md-9">
                    <input type="text" name="name" class="form-control" value="{{ old('name', $gateway->name) }}"
                        required>
                </div>
            </div>

            {{-- Provider Type --}}
            <div class="mb-3 row">
                <label class="col-md-3 col-form-label text-muted">Type</label>
                <div class="col-md-9">
                    <select name="type" class="form-select" required>
                        <option value="onprem" @selected($gateway->type === 'onprem')>On-Premise</option>
                        <option value="cloud" @selected($gateway->type === 'cloud')>Meta Cloud API</option>
                    </select>
                </div>
            </div>

            {{-- Description --}}
            <div class="mb-3 row">
                <label class="col-md-3 col-form-label text-muted">Description</label>
                <div class="col-md-9">
                    <textarea name="description" class="form-control" rows="3" placeholder="Optional description">{{ old('description', $gateway->description) }}</textarea>
                </div>
            </div>

            {{-- Status --}}
            <div class="mb-3 row">
                <label class="col-md-3 col-form-label text-muted">Status</label>
                <div class="col-md-9">
                    <div class="form-check form-switch">
                        <input type="hidden" name="is_active" value="0">
                        <input type="checkbox" name="is_active" value="1" class="form-check-input" id="is_active"
                            @checked(old('is_active', $gateway->is_active))>
                        <label class="form-check-label" for="is_active">Active</label>
                    </div>
                </div>
            </div>

            <hr class="my-4">

            {{-- Buttons --}}
            <button type="submit" class="btn btn-success">
                <i class="fa fa-save"></i> Save Changes
            </button>

            <a href="{{ route('whatsappgateway.show', $gateway->slug) }}" class="btn btn-secondary ms-2">
                <i class="fa fa-arrow-left"></i> Back
            </a>

        </form>

    </div>

</x-layouts.cpanel.settings.app-layout>
