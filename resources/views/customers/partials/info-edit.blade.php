<form method="POST" action="{{ route('customers.update.info', $slug) }}" class="mt-3">

    @csrf
    @method('PATCH')

    @include('customers.partials.table-open')

    {{-- ID & Responsibility --}}
    <tr>
        <td class="label-col"><strong>{{ __('customer.label.id') }}</strong></td>
        <td class="colon-col">:</td>
        <td>
            <input class="form-control form-control-sm" value="{{ $customer_id }}" readonly>
        </td>

        <td class="label-col"><strong>{{ __('customer.label.responsibility') }}</strong></td>
        <td class="colon-col">:</td>
        <td>
            <select name="user_id" class="form-control form-control-sm">
                @foreach ($users as $user)
                    <option value="{{ $user->id }}" {{ ($user_id ?? '') == $user->id ? 'selected' : '' }}>
                        {{ $user->name }}
                    </option>
                @endforeach
            </select>
        </td>
    </tr>

    {{-- Name & Email --}}
    <tr>
        <td class="label-col"><strong>{{ __('customer.label.name') }}</strong></td>
        <td class="colon-col">:</td>
        <td>
            <input type="text" name="name" value="{{ old('name', $name) }}" class="form-control form-control-sm">
            @error('name')
                <small class="text-danger">{{ $message }}</small>
            @enderror
        </td>

        <td class="label-col"><strong>{{ __('customer.label.email') }}</strong></td>
        <td class="colon-col">:</td>
        <td>
            <input type="email" name="email" value="{{ old('email', $email) }}"
                class="form-control form-control-sm">
            @error('email')
                <small class="text-danger">{{ $message }}</small>
            @enderror
        </td>
    </tr>

    {{-- Address & Contact --}}
    <tr>
        <td class="label-col"><strong>{{ __('customer.label.address') }}</strong></td>
        <td class="colon-col">:</td>
        <td>
            <input type="text" name="address" value="{{ old('address', $address) }}"
                class="form-control form-control-sm">
            @error('address')
                <small class="text-danger">{{ $message }}</small>
            @enderror
        </td>

        <td class="label-col"><strong>{{ __('customer.label.contact') }}</strong></td>
        <td class="colon-col">:</td>
        <td>
            <input type="text" name="contact" value="{{ old('contact', $contact) }}"
                class="form-control form-control-sm">
            @error('contact')
                <small class="text-danger">{{ $message }}</small>
            @enderror
        </td>
    </tr>

    {{-- Latitude & Longitude Input --}}
    <tr>
        <td class="label-col"><strong>{{ __('customer.wizard.label.latitude') }}</strong></td>
        <td class="colon-col">:</td>
        <td>
            <input type="text" id="edit-latitude" name="latitude" value="{{ old('latitude', $latitude ?? '') }}"
                class="form-control form-control-sm" placeholder="-7.654321">
            @error('latitude')
                <small class="text-danger">{{ $message }}</small>
            @enderror
        </td>

        <td class="label-col"><strong>{{ __('customer.wizard.label.longitude') }}</strong></td>
        <td class="colon-col">:</td>
        <td>
            <input type="text" id="edit-longitude" name="longitude" value="{{ old('longitude', $longitude ?? '') }}"
                class="form-control form-control-sm" placeholder="109.123456">
            @error('longitude')
                <small class="text-danger">{{ $message }}</small>
            @enderror
        </td>
    </tr>

    {{-- Activation Date (readonly) --}}
    <tr>
        <td class="label-col"><strong>{{ __('customer.label.activation_date') }}</strong></td>
        <td class="colon-col">:</td>
        <td colspan="3">
            <input type="text" class="form-control form-control-sm"
                value="{{ $activation_date ? \Carbon\Carbon::parse($activation_date)->format('d F Y') : '-' }}"
                readonly>
        </td>
    </tr>

    @include('customers.partials.table-close')

    {{-- INTERACTIVE MAP PICKER CONTAINER (BOOTSTRAP 4) --}}
    <div class="customer-map-picker-wrapper mt-3">
        <div class="d-flex justify-content-between align-items-center mb-2 flex-wrap">
            <label class="fw-bold mb-1 mb-md-0">
                <i class="fas fa-map-marker-alt text-danger me-1"></i> {{ __('customer.wizard.label.map_picker') }}
            </label>
            <div class="d-flex align-items-center">
                <small class="text-muted d-none d-md-inline me-2">
                    {{ __('customer.info.map_instruction') }}
                </small>
                <button type="button" id="btn-edit-current-location" class="btn btn-sm btn-outline-primary py-1 px-2">
                    <i class="fas fa-crosshairs me-1"></i> {{ __('customer.wizard.button.use_current_location') }}
                </button>
            </div>
        </div>
        <div id="customer-edit-map" data-lat="{{ old('latitude', $latitude ?? '-7.797068') }}"
            data-lng="{{ old('longitude', $longitude ?? '110.370529') }}"
            style="height: 300px; width: 100%; border-radius: 8px; border: 1px solid #ced4da; z-index: 1;"></div>
    </div>

    {{-- ACTION BUTTONS --}}
    <div class="mt-4 action-group">
        <button type="submit" class="btn-soft btn-green">
            <i class="fa fa-save me-1"></i> {{ __('customer.button.save_changes') }}
        </button>

        <a href="{{ route('customers.show', [$slug, 'tab' => 'info']) }}" class="btn-soft btn-gray">
            {{ __('customer.button.cancel') }}
        </a>
    </div>

</form>

<style>
    /* ===== FORM TABLE STYLING ===== */
    .label-col {
        width: 140px;
        white-space: nowrap;
    }

    .colon-col {
        width: 12px;
        text-align: center;
        font-weight: 600;
        color: #6c757d;
    }

    input[readonly] {
        background-color: #f1f3f5;
    }

    /* ===== CUSTOM BUTTONS (BS4 STYLES) ===== */
    .btn-soft {
        border: none;
        border-radius: 6px;
        padding: 8px 20px;
        font-size: 14px;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        text-decoration: none !important;
        cursor: pointer;
        transition: all 0.2s ease-in-out;
    }

    .btn-green {
        background-color: #28a745;
        color: #ffffff !important;
    }

    .btn-green:hover {
        background-color: #218838;
        box-shadow: 0 3px 8px rgba(40, 167, 69, 0.3);
    }

    .btn-gray {
        background-color: #6c757d;
        color: #ffffff !important;
    }

    .btn-gray:hover {
        background-color: #5a6268;
        box-shadow: 0 3px 8px rgba(108, 117, 125, 0.3);
    }

    .action-group {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    /* ===== RESPONSIVE MOBILE (BS4 MAX-WIDTH 767px) ===== */
    @media (max-width: 767.98px) {

        .table-form,
        .table-form tbody,
        .table-form tr,
        .table-form td {
            display: block;
            width: 100%;
        }

        .table-form tr {
            margin-bottom: 12px;
        }

        .table-form .colon-col {
            display: none;
        }

        .table-form .label-col {
            width: auto;
            font-weight: 600;
            margin-bottom: 2px;
            color: #495057;
        }

        .table-form td input,
        .table-form td select {
            width: 100%;
        }

        .table-form td {
            padding: 2px 0 !important;
            border-top: none !important;
        }

        .action-group {
            flex-direction: column;
        }

        .action-group .btn-soft {
            width: 100%;
            justify-content: center;
        }
    }
</style>
