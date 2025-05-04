<nav class="pagination">
    <ul class="pagination">
        {{-- Page Number Links --}}
        @php
            $current = $paginator->currentPage();
            $last = $paginator->lastPage();
            $start = max(1, $current - 1);
            $end = min($last, $current + 1);
        @endphp

        {{-- Back to First Page --}}
        @if ($current > 2)
            <li class="page-item">
                <a class="page-link" href="{{ $paginator->url(1) }}" title="First page">&laquo;&laquo;</a>
            </li>
        @else
            <li class="page-item disabled"><span class="page-link">&laquo;&laquo;</span></li>
        @endif

        {{-- Previous Page Link --}}
        @if ($paginator->onFirstPage())
            <li class="page-item disabled"><span class="page-link">&laquo;</span></li>
        @else
            <li class="page-item">
                <a class="page-link" href="{{ $paginator->previousPageUrl() }}" title="Previous page">&laquo;</a>
            </li>
        @endif

        @for ($i = $start; $i <= $end; $i++)
            @if ($i == $current)
                <li class="page-item active"><span class="page-link">{{ $i }}</span></li>
            @else
                <li class="page-item">
                    <a class="page-link" href="{{ $paginator->url($i) }}">{{ $i }}</a>
                </li>
            @endif
        @endfor

        {{-- Next Page Link --}}
        @if ($paginator->hasMorePages())
            <li class="page-item">
                <a class="page-link" href="{{ $paginator->nextPageUrl() }}" title="Next page">&raquo;</a>
            </li>
        @else
            <li class="page-item disabled"><span class="page-link">&raquo;</span></li>
        @endif

        {{-- Go to Last Page --}}
        @if ($current < $last - 1)
            <li class="page-item">
                <a class="page-link" href="{{ $paginator->url($last) }}" title="Last page">&raquo;&raquo;</a>
            </li>
        @else
            <li class="page-item disabled"><span class="page-link">&raquo;&raquo;</span></li>
        @endif
    </ul>
</nav>
