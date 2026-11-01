@php
    use App\Enums\MikrotikStatus;

    /**
     * Expected variables:
     * - $customer (Customer model)
     * - $disabledState (string|null)
     */

@endphp

{{-- NOT ACTIVATED --}}
@if (!$customer->activation)
    <span class="badge badge-danger">
        <i class="fa fa-times mr-1"></i>
        {{ __('customer.info.not-activation') }}
    </span>

    {{-- ACTIVATED --}}
@else
    @if ($disabledState === MikrotikStatus::PPPOE_ENABLE->value)
        <span class="badge badge-success">
            <i class="fas fa-lock-open mr-1"></i>
            {{ __('pppoe.secret.enabled') }}
        </span>
    @elseif ($disabledState === MikrotikStatus::PPPOE_DISABLE->value)
        <span class="badge badge-danger">
            <i class="fas fa-lock mr-1"></i>
            {{ __('pppoe.secret.disabled') }}
        </span>
    @else
        <span class="badge badge-secondary">
            {{ __('pppoe.secret.unknown-state') }}
        </span>
    @endif
@endif
