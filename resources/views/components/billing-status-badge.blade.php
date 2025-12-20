@props(['status'])

@switch($status)
    @case('LS')
        <span class="badge bg-success text-white">
            <i class="fas fa-check-circle"></i> Paid
        </span>
    @break

    @case('PL')
        <span class="badge bg-warning text-white">
            <i class="fas fa-clock"></i> Pay Later</span>
    @break

    @default
        <span class="badge bg-danger text-white">
            <i class="fas fa-times-circle"></i> Unpaid
        </span>
@endswitch
