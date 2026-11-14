<x-layouts.app-layout title="About">

    <div class="card shadow-sm mb-4">
        <div class="card-header py-3 d-flex flex-column gap-1">
            <h5 class="m-0 fw-bold text-primary">About Customer Management</h5>
            <div>
                <span>This project developed by <a href="https://www.youtube.com/@griya-net" target="_blank"
                        class="text-decoration-none fw-semibold">GriyaNet</a></span>
            </div>
            <div>
                <span class="text-muted small">Don't forget to subscribe to my channel</span>
            </div>
        </div>

        <div class="card-body">
            <p class="card-text mb-4">
                {{ __('Silahkan subscribe chanel kami jika bermanfaat untuk kalian.') }}
            </p>

            <div class="row">
                {{-- Video 1 --}}
                <div class="col-lg-8 mb-4">
                    <label for="video-auto-isolir" class="form-label">
                        <strong>{{ __('1. Install Auto Isolir') }}</strong>
                    </label>
                    <div class="ratio ratio-16x9 rounded overflow-hidden shadow-sm border">
                        <iframe src="https://www.youtube.com/embed/dB7s_lsq2yM" id="video-auto-isolir"
                            title="Install Auto Isolir" allowfullscreen></iframe>
                    </div>
                </div>

                {{-- Video 2 --}}
                <div class="col-lg-8 mb-3">
                    <label for="video-how-to-use" class="form-label">
                        <strong>{{ __('2. How to use Customer Management') }}</strong>
                    </label>
                    <div class="ratio ratio-16x9 rounded overflow-hidden shadow-sm border">
                        <iframe src="https://www.youtube.com/embed/E0dBOwqvwZ4" id="video-how-to-use"
                            title="How to use Customer Management" allowfullscreen></iframe>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('custom_scripts')
        <script data-name="NBJ-Widget" data-cfasync="false"
            src="https://www.nihbuatjajan.com/javascripts/widget.prod.min.js?u=griyanet" data-id="griyanet"
            data-domain="https://www.nihbuatjajan.com" data-description="" data-message="" data-color="#FF813F"
            data-position="Right" data-x_margin="18" data-y_margin="18"></script>
    @endpush

</x-layouts.app-layout>
