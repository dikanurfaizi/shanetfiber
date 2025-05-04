@if ($paginator->hasPages())
    <ul class="pagination">

        {{-- First Page Link --}}
        @if ($paginator->onFirstPage())
            <li class="page-item disabled"><span class="page-link">&laquo;&laquo;</span></li>
        @else
            <li class="page-item">
                <a class="page-link" href="{{ $paginator->appends(request()->query())->url(1) }}"
                    title="First page">&laquo;&laquo;</a>
            </li>
        @endif

        {{-- Previous Page Link --}}
        @if ($paginator->onFirstPage())
            <li class="page-item disabled"><span class="page-link">&laquo;</span></li>
        @else
            <li class="page-item">
                <a class="page-link" href="{{ $paginator->appends(request()->query())->previousPageUrl() }}"
                    title="Previous page">&laquo;</a>
            </li>
        @endif

        {{-- Pagination Elements --}}
        @php
            $current = $paginator->currentPage();
            $last = $paginator->lastPage();
            $start = max(1, $current - 1);
            $end = min($last, $current + 1);
        @endphp

        @for ($i = $start; $i <= $end; $i++)
            @if ($i == $current)
                <li class="page-item active"><span class="page-link">{{ $i }}</span></li>
            @else
                <li class="page-item">
                    <a class="page-link"
                        href="{{ $paginator->appends(request()->query())->url($i) }}">{{ $i }}</a>
                </li>
            @endif
        @endfor

        {{-- Next Page Link --}}
        @if ($paginator->hasMorePages())
            <li class="page-item">
                <a class="page-link" href="{{ $paginator->appends(request()->query())->nextPageUrl() }}"
                    title="Next page">&raquo;</a>
            </li>
        @else
            <li class="page-item disabled"><span class="page-link">&raquo;</span></li>
        @endif

        {{-- Last Page Link --}}
        @if ($paginator->currentPage() == $paginator->lastPage())
            <li class="page-item disabled"><span class="page-link">&raquo;&raquo;</span></li>
        @else
            <li class="page-item">
                <a class="page-link" href="{{ $paginator->appends(request()->query())->url($last) }}"
                    title="Last page">&raquo;&raquo;</a>
            </li>
        @endif

    </ul>
@endif
