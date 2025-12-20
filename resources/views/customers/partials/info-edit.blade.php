<form method="POST" action="{{ route('customers.update.info', $customer->slug) }}" class="mt-3">

    @csrf
    @method('PATCH')

    @include('customers.partials.table-open')

    {{-- ID (readonly) --}}
    <tr>
        <td class="label-col"><strong>ID</strong></td>
        <td class="colon-col">:</td>
        <td>
            <input class="form-control form-control-sm" value="{{ $customer_id }}" readonly>
        </td>

        <td class="label-col"><strong>Responsibility</strong></td>
        <td class="colon-col">:</td>
        <td>
            <select name="user_id" class="form-control form-control-sm">
                @foreach ($users as $user)
                    <option value="{{ $user->id }}" {{ $customer->user_id == $user->id ? 'selected' : '' }}>
                        {{ $user->name }}
                    </option>
                @endforeach
            </select>
        </td>
    </tr>


    {{-- Name & Email --}}
    <tr>
        <td class="label-col"><strong>Name</strong></td>
        <td class="colon-col">:</td>
        <td>
            <input type="text" name="name" value="{{ old('name', $name) }}" class="form-control form-control-sm">
            @error('name')
                <small class="text-danger">{{ $message }}</small>
            @enderror
        </td>

        <td class="label-col"><strong>Email</strong></td>
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
        <td class="label-col"><strong>Address</strong></td>
        <td class="colon-col">:</td>
        <td>
            <input type="text" name="address" value="{{ old('address', $address) }}"
                class="form-control form-control-sm">
            @error('address')
                <small class="text-danger">{{ $message }}</small>
            @enderror
        </td>

        <td class="label-col"><strong>Contact</strong></td>
        <td class="colon-col">:</td>
        <td>
            <input type="text" name="contact" value="{{ old('contact', $contact) }}"
                class="form-control form-control-sm">
            @error('contact')
                <small class="text-danger">{{ $message }}</small>
            @enderror
        </td>
    </tr>

    {{-- Activation Date (readonly) --}}
    <tr>
        <td class="label-col"><strong>Activation Date</strong></td>
        <td class="colon-col">:</td>
        <td colspan="3">
            <input type="text" class="form-control form-control-sm"
                value="{{ $activation_date ? \Carbon\Carbon::parse($activation_date)->format('d F Y') : '-' }}"
                readonly>
        </td>
    </tr>

    @include('customers.partials.table-close')

    {{-- ACTION BUTTONS --}}
    <div class="mt-4 action-group">
        <button type="submit" class="btn-soft btn-green">
            <i class="fa fa-save"></i> Save Changes
        </button>

        <a href="{{ route('customers.show', [$customer->slug, 'tab' => 'info']) }}" class="btn-soft btn-gray">
            Cancel
        </a>
    </div>


</form>

<style>
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

    @media (max-width: 767px) {

        /* Matikan table layout */
        .table-form,
        .table-form tbody,
        .table-form tr,
        .table-form td {
            display: block;
            width: 100%;
        }

        .table-form tr {
            margin-bottom: 16px;
        }

        /* Hilangkan colon */
        .table-form .colon-col {
            display: none;
        }

        /* Label */
        .table-form .label-col {
            width: auto;
            font-weight: 600;
            margin-bottom: 4px;
            color: #495057;
        }

        /* Input */
        .table-form td input,
        .table-form td select {
            width: 100%;
        }

        /* Spasi antar field */
        .table-form td {
            padding: 4px 0 !important;
        }

        .action-group {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .action-group .btn-soft {
            width: 100%;
            justify-content: center;
        }
    }
</style>
