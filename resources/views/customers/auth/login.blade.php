@extends('layouts.cust-guest')

@php
    $formatter = new \App\Helpers\FormatReceiver();
    $formatPackage = new \App\Helpers\FormatPackage();
    // Get unique categories dynamically from packages
    $categories = $pakets->pluck('category')->unique()->toArray();

    // Ambil semua digit saja
    $phoneNumber = preg_replace('/\D/', '', $websystem->phone);

    // Ganti 0 di depan jadi 62
    if (substr($phoneNumber, 0, 1) === '0') {
        $phoneNumber = '62' . substr($phoneNumber, 1);
    }

    $generalMessage = urlencode('Halo, saya tertarik dengan paket internet yang tersedia. Bisa dibantu informasinya?');
@endphp

@section('content')
    <!-- Navigation Bar (no longer fixed) -->
    <nav class="navbar navbar-expand-lg navbar-light navbar-transparent shadow-sm">
        <div class="container-fluid px-4">
            <div class="d-flex align-items-center">
                <a class="navbar-brand me-4" href="#">
                    <img src="/images/receipt_logo1.png" alt="Shawnn Network Fiber" height="40">
                </a>

                <!-- Contact Info -->
                <div id="contact" class="d-none d-lg-flex align-items-center me-auto">
                    {{-- Telepon / WhatsApp --}}
                    <div class="contact-item ml-2">
                        <i class="fas fa-phone-alt text-primary me-2"></i>
                        <a href="https://wa.me/{{ $phoneNumber }}?text={{ $generalMessage }}" class="text-dark"
                            target="_blank" rel="noopener noreferrer">
                            {{ $formatter->formatReceiver($websystem->phone, 'homepage') }}
                        </a>
                    </div>

                    <div class="contact-item ml-2">
                        <i class="fas fa-envelope text-primary me-2"></i>
                        <a href="mailto:{{ $websystem->email }}?subject={{ urlencode('Info Paket Internet Shanetfiber') }}&body={{ urlencode($generalMessage) }}"
                            class="text-dark d-none d-md-inline" title="Kirim via aplikasi email">
                            {{ $websystem->email }}
                        </a>
                        <a href="https://mail.google.com/mail/?view=cm&fs=1&to={{ urlencode($websystem->email) }}&su={{ urlencode('Info Paket Internet Shanetfiber') }}&body={{ urlencode($generalMessage) }}"
                            class="text-dark d-inline d-md-none" target="_blank" rel="noopener noreferrer"
                            title="Kirim via Gmail">
                            {{ $websystem->email }}
                        </a>
                    </div>
                </div>
            </div>

            <button class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#loginModal">
                <i class="fas fa-sign-in-alt me-2"></i> Login
            </button>
        </div>
    </nav>

    <!-- Hero Slider with margin top -->
    <div class="hero-slider swiper mt-0">
        <div class="swiper-wrapper">
            <!-- Slide 1 -->
            <div class="swiper-slide">
                <div class="slider-image" style="background-image: url('/images/slider1.jpeg');">
                    <div class="slider-overlay-dark"></div>
                    <div class="container-fluid h-100 d-flex align-items-center px-4 px-lg-5">
                        <div class="slider-content text-start">
                            <h1 class="display-4 display-lg-2 fw-bold mb-3 mb-lg-4">Ultra-Fast Internet Solutions</h1>
                            <p class="lead mb-3 mb-lg-4 d-none d-sm-block">Experience seamless connectivity with our
                                reliable network infrastructure</p>
                            <div class="d-flex flex-column flex-sm-row gap-2 gap-lg-3">
                                <a href="#packages" class="btn btn-primary btn-lg px-3 px-lg-4">View Packages</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Slide 2 -->
            <div class="swiper-slide">
                <div class="slider-image" style="background-image: url('/images/slider2.png');">
                    <div class="slider-overlay-dark"></div>
                    <div class="container-fluid h-100 d-flex align-items-center px-4 px-lg-5">
                        <div class="slider-content text-start">
                            <h1 class="display-4 display-lg-2 fw-bold mb-3 mb-lg-4">Reliable Fiber Optic Network</h1>
                            <p class="lead mb-3 mb-lg-4 d-none d-sm-block">Stable connection for your home and business
                                needs</p>
                            <div class="d-flex flex-column flex-sm-row gap-2 gap-lg-3">
                                <a href="#packages" class="btn btn-primary btn-lg px-3 px-lg-4">View Packages</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Slide 3 -->
            <div class="swiper-slide">
                <div class="slider-image" style="background-image: url('/images/slider3.jpeg');">
                    <div class="slider-overlay-dark"></div>
                    <div class="container-fluid h-100 d-flex align-items-center px-4 px-lg-5">
                        <div class="slider-content text-start">
                            <h1 class="display-4 display-lg-2 fw-bold mb-3 mb-lg-4">24/7 Customer Support</h1>
                            <p class="lead mb-3 mb-lg-4 d-none d-sm-block">Our team is always ready to assist you</p>
                            <div class="d-flex flex-column flex-sm-row gap-2 gap-lg-3">
                                <a href="#packages" class="btn btn-primary btn-lg px-3 px-lg-4">View Packages</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Add Pagination -->
        <div class="swiper-pagination"></div>

        <!-- Add Navigation -->
        <div class="swiper-button-next"></div>
        <div class="swiper-button-prev"></div>
    </div>

    <div class="container-fluid py-5" id="packages" style="background-color: #f8f9fa;">
        <!-- Tab Navigation -->
        <div class="d-flex justify-content-center mb-4">
            <div class="btn-group" role="group" id="categoryTabs"
                style="border-radius: 50px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
                @foreach ($categories as $index => $category)
                    <button type="button" data-category="{{ $category }}"
                        class="category-tab btn border-0 py-2 px-4 focus:outline-none transition {{ $index === 0 ? 'active' : '' }}"
                        onclick="changeCategory('{{ $category }}')"
                        style="{{ $index === 0 ? 'background-color: ' . ($category === 'Internet Only' ? '#3b82f6' : ($category === 'Internet Stream' ? '#8b5cf6' : '#10b981')) . '; color: white;' : 'background-color: white; color: #374151;' }}">
                        {{ $category }}
                    </button>
                @endforeach
            </div>
        </div>

        <!-- Packages Container -->
        <div id="packagesContainer">
            @foreach ($categories as $index => $category)
                <div class="category-packages {{ $index === 0 ? '' : 'd-none' }}" id="{{ $category }}Packages">
                    <div class="position-relative py-4">
                        <div class="paket-slider swiper">
                            <div class="swiper-wrapper pb-4">
                                @foreach ($pakets->where('category', $category) as $package)
                                    @php
                                        $packageName = $formatPackage->formatPackageName($package->package_name);
                                        $waMessage = urlencode(
                                            "Halo, saya tertarik dengan paket {$packageName}. Bisa dibantu informasinya?",
                                        );
                                    @endphp
                                    <div class="swiper-slide">
                                        <div class="paket-internet">
                                            <!-- Header with dynamic class -->
                                            <div
                                                class="internet-card
                                                    @if ($category == 'Internet Only') internet-only
                                                    @elseif($category == 'Internet Stream') internet-stream
                                                    @else internet-tv @endif">
                                                <h3 class="paket-title">
                                                    {{ $formatPackage->formatPackageName($package->package_name) }}</h3>
                                            </div>

                                            <div class="paket-price">
                                                <div class="paket-price-value">
                                                    <p>Rp
                                                        {{ number_format($package->price, 0, ',', '.') }}/{{ $package->name_en }}
                                                    </p>
                                                </div>
                                                <p class="paket-price-note">Harga Sudah Termasuk PPN</p>
                                            </div>

                                            <hr class="paket-divider" />

                                            <!-- Features List -->
                                            <div class="list-paket-point">
                                                <div class="list-paket-item">
                                                    <div class="list-paket-icon">
                                                        <i class="fas fa-laptop"></i>
                                                    </div>
                                                    <p class="list-paket-text">
                                                        Ideal untuk
                                                        {{ $package->recommended_device_min }}-{{ $package->recommended_device_max }}
                                                        perangkat
                                                    </p>
                                                </div>

                                                <!-- Quota -->
                                                <div class="list-paket-item">
                                                    <div class="list-paket-icon">
                                                        <i class="fas fa-database text-sm"></i>
                                                    </div>
                                                    <p class="list-paket-text">
                                                        {{ $package->quota ?? 'Unlimited' }} kuota
                                                    </p>
                                                </div>

                                                <!-- Modem -->
                                                <div class="list-paket-item">
                                                    <div class="list-paket-icon">
                                                        <i class="fas fa-wifi text-sm"></i>
                                                    </div>
                                                    <p class="list-paket-text">
                                                        Modem {{ $package->include_modem ? 'Termasuk' : 'Tidak termasuk' }}
                                                    </p>
                                                </div>

                                                <!-- Installation -->
                                                <div class="list-paket-item">
                                                    <div class="list-paket-icon">
                                                        <i class="fas fa-tools text-sm"></i>
                                                    </div>
                                                    <p class="list-paket-text">
                                                        Instalasi <span class="font-semibold">
                                                            {{ $package->is_installation_free ? 'Gratis' : 'Rp ' . number_format($package->installation_fee, 0, ',', '.') }}
                                                        </span>
                                                    </p>
                                                </div>
                                            </div>

                                            <hr class="paket-divider" />

                                            <!-- CTA Buttons -->
                                            <div
                                                class="cta-paket-buttons
                                                    @if ($category == 'Internet Only') internet-only
                                                    @elseif($category == 'Internet Stream') internet-stream
                                                    @else internet-tv @endif">
                                                <a href="https://wa.me/{{ $phoneNumber }}?text={{ $waMessage }}"
                                                    target="_blank" rel="noopener noreferrer" class="btn-subscribe">
                                                    Berlangganan
                                                </a>
                                                <button class="btn-info">
                                                    <img src="https://g-fiber.co.id/img/webimg/info.svg" alt="info" />
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            <div class="swiper-button-next"></div>
                            <div class="swiper-button-prev"></div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <footer class="footer-custom py-4 px-3"> <!-- Tambahkan px-3 untuk padding horizontal -->
        <div class="container-fluid px-4">
            <div class="row">
                <div class="col-md-6 mb-3 mb-md-0">
                    <img src="/images/nih_buat_jajan.png" alt="Shawnn Network Fiber" height="50" class="mb-3">
                    <p class="text-sm text-neutral-200" style="margin-bottom: 0;">
                        <a href="https://maps.app.goo.gl/zRT4QfXNmoJysKRQ8" target="_blank" rel="noopener noreferrer"
                            class="text-neutral-200 text-decoration-none">
                            {{ $websystem->address }}, {{ $websystem->city }}, {{ $websystem->postal_code }}
                        </a>
                    </p>
                </div>
                <div class="col-md-3">
                    <h5 class="mb-3 fw-semibold">Quick Links</h5>
                    <ul class="list-unstyled">
                        <li class="mb-2"><a href="#packages"
                                class="text-dark text-decoration-none hover-underline">Packages</a></li>
                        <li class="mb-2"><a href="#contact"
                                class="text-dark text-decoration-none hover-underline">Contact
                                Us</a></li>
                        <li><a href="#" class="text-dark text-decoration-none hover-underline">Terms of Service</a>
                        </li>
                    </ul>
                </div>
                <div class="col-md-3">
                    <h5 class="mb-3 fw-semibold">Contact</h5>
                    <ul class="list-unstyled text-dark small">
                        <li class="mb-2"><i class="fas fa-phone-alt me-2"></i>
                            <a href="https://wa.me/{{ $phoneNumber }}?text={{ $waMessage }}" class="text-dark"
                                target="_blank" rel="noopener noreferrer">
                                {{ $formatter->formatReceiver($websystem->phone, 'homepage') }}
                            </a>
                        </li>
                        <li class="mb-2"><i class="fas fa-envelope me-2"></i> <a
                                href="mailto:{{ $websystem->email }}?subject={{ urlencode('Info Paket Internet Shanetfiber') }}&body={{ urlencode($waMessage) }}"
                                class="text-dark d-none d-md-inline" title="Kirim via aplikasi email">
                                {{ $websystem->email }}
                            </a>
                            <a href="https://mail.google.com/mail/?view=cm&fs=1&to={{ urlencode($websystem->email) }}&su={{ urlencode('Info Paket Internet Shanetfiber') }}&body={{ urlencode($waMessage) }}"
                                class="text-dark d-inline d-md-none" target="_blank" rel="noopener noreferrer"
                                title="Kirim via Gmail">
                                {{ $websystem->email }}
                            </a>
                        </li>
                        <li>
                            <i class="fas fa-map-marker-alt me-2"></i>
                            <a href="https://maps.app.goo.gl/zRT4QfXNmoJysKRQ8" class="text-dark" target="_blank"
                                rel="noopener noreferrer">
                                Purwakarta, Indonesia
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
        <hr class="my-4 border-secondary">

        <div id="copyright-content" class="mitra-text text-center">
            MITRA RESELLER<br>
            PT GLOBAL SARANA ELEKTRONIKA
        </div>
        <div id="copyright-content" class="copyright-text text-center">
            &copy; 2025 Shanum Network Fiber. All rights reserved.
        </div>
    </footer>

    <!-- Login Modal -->
    <!-- Modal -->
    <div class="modal fade" id="loginModal" tabindex="-1" aria-labelledby="loginModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-sm modal-md modal-lg">
            <div class="modal-content p-4">
                <!-- Logo / Gambar di atas -->
                <div class="text-center mb-4">
                    <img src="/images/receipt_logo1.png" alt="Logo" class="img-fluid"
                        style="max-height: 80px; object-fit: contain;">
                </div>

                <form action="{{ route('customerLoginPost') }}" method="post">
                    @csrf
                    <div class="form-group">
                        <input type="text" name="email" value="{{ old('email') }}"
                            class="form-control form-control-user @error('email') is-invalid @enderror"
                            id="exampleInputEmail" placeholder="{{ __('Email Address or Phone Number') }}" required
                            autofocus>
                    </div>
                    @error('email')
                        <div class="form-group custom-control">
                            <label class="text-danger">{{ $message }}</label>
                        </div>
                    @enderror

                    <div class="form-group position-relative">
                        <input type="password" name="password"
                            class="form-control form-control-user @error('password') is-invalid @enderror"
                            id="passwordInput" placeholder="{{ __('Password') }}" required>
                        <span class="position-absolute"
                            style="right: 15px; top: 50%; transform: translateY(-50%); cursor: pointer;"
                            onclick="togglePasswordVisibility()">
                            <i id="passwordIcon" class="fas fa-eye"></i>
                        </span>
                    </div>
                    @error('password')
                        <div class="form-group custom-control">
                            <label class="text-danger">{{ $message }}</label>
                        </div>
                    @enderror

                    <button type="submit" class="btn btn-primary w-100">Login</button>
                </form>
            </div>
        </div>
    </div>
@endsection
