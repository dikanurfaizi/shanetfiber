<footer class="sticky-footer bg-white">
    <div class="container my-auto">
        <div class="copyright text-center my-auto">
            <span>Customer Management V{{ $websystem->version }}</span>
            <div>
                <span>
                    {{ $websystem->title }} &#64;{{ Carbon\Carbon::now()->format('Y') }}
                </span>
            </div>
        </div>
    </div>
</footer>
