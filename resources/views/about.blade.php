<x-layouts.app-layout title="About">
    <div class="card-header py-3">
        <h5 class="m-0 font-weight-bold text-primary">About Customer Management</h5>
        <span>This project developed by <a href="https://www.youtube.com/@griya-net" target="_blank">GriyaNet</a></span>
        <span>Don't forget to subscribe to my channel</span>
    </div>
    <div class="card-body col-lg-8">
        <p class="card-text">
            {{ __('Silahkan subscribe chanel kami jika bermanfaat untuk kalian.') }}
        </p>
        <label for="periode"><strong>{{ __('1. Install Auto Isolir') }}</strong></label>
        <div class="embed-responsive embed-responsive-16by9">
            <iframe src="https://www.youtube.com/embed/dB7s_lsq2yM" class="embed-responsive-item" frameborder="0"
                allowfullscreen></iframe>
        </div>
    </div>
    <div class="col-md-12 mb-3">
        <label for="periode"><strong>{{ __('2. How to use Customer Management') }}</strong></label>
        <div class="embed-responsive embed-responsive-16by9">
            <iframe src="https://www.youtube.com/embed/E0dBOwqvwZ4" class="embed-responsive-item" frameborder="0"
                allowfullscreen></iframe>
        </div>
    </div>
    @section('custom_scripts')
        <script data-name="NBJ-Widget" data-cfasync="false"
            src="https://www.nihbuatjajan.com/javascripts/widget.prod.min.js?u=griyanet" data-id="griyanet"
            data-domain="https://www.nihbuatjajan.com" data-description="" data-message="" data-color="#FF813F"
            data-position="Right" data-x_margin="18" data-y_margin="18"></script>
    @endsection
</x-layouts.app-layout>
