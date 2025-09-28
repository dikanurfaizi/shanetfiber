@php
    $firstItem = $olts->firstItem() ?: 0;
    $lastItem = $olts->lastItem() ?: 0;
    $currentPage = $olts->currentPage() ?: 0;
    $lastPage = $olts->lastPage() ?: 0;
@endphp

<div class="d-flex justify-content-between align-items-center text-muted small mb-2">
    {{-- Left: Showing entries --}}
    <div>
        Showing <span class="fw-semibold">{{ $firstItem }}</span>
        to <span class="fw-semibold">{{ $lastItem }}</span>
        of <span class="fw-semibold">{{ $olts->total() }}</span> entries
    </div>

    {{-- Right: Page info --}}
    <div>
        Page <span class="fw-semibold">{{ $currentPage }}</span>
        of <span class="fw-semibold">{{ $lastPage }}</span>
    </div>
</div>
