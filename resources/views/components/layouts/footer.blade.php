<footer class="sticky-footer bg-light py-3 border-top">
    <div class="container">
        <div class="text-center text-muted small">
            <div>
                Customer Management V{{ $websystem->version }}
            </div>
            <div class="mt-1">
                {{ $websystem->title }} © {{ Carbon\Carbon::now()->format('Y') }}
            </div>
        </div>
    </div>
</footer>
