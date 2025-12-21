@php
    use App\Enums\MikrotikStatus;

    /**
     * Expected variables:
     * - $customer (Customer model)
     * - $disabledState (string|null)
     */

@endphp

{{-- NOT ACTIVATED --}}
@if ($customer->activation === 'false')
    <span class="badge bg-danger text-white">
        <i class="fa fa-times me-1"></i>
        {{ __('customer.info.not-activation') }}
    </span>

    {{-- ACTIVATED --}}
@else
    @if ($disabledState === MikrotikStatus::PPPOE_ENABLE->value)
        <span class="badge bg-success text-white">
            <i class="fas fa-lock-open me-1"></i>
            {{ __('pppoe.secret.enabled') }}
        </span>
    @elseif ($disabledState === MikrotikStatus::PPPOE_DISABLE->value)
        <span class="badge bg-danger text-white">
            <i class="fas fa-lock me-1"></i>
            {{ __('pppoe.secret.disabled') }}
        </span>
    @else
        <span class="badge bg-secondary text-white">
            {{ __('pppoe.secret.unknown-state') }}
        </span>
    @endif
@endif
