@extends('layouts.cust-guest')

@section('content')
    <!-- Navigation Bar (no longer fixed) -->
    <nav class="navbar navbar-expand-lg navbar-light navbar-transparent shadow-sm">
        <div class="container-fluid px-4">
            <div class="d-flex align-items-center">
                <a class="navbar-brand me-4" href="#">
                    <img src="/images/logo_top_customer.png" alt="Shawnn Network Fiber" height="50">
                </a>

                <!-- Contact Info -->
                <div id="contact" class="d-none d-lg-flex align-items-center me-auto">
                    {{-- Telepon / WhatsApp --}}
                    <div class="contact-item ml-2">
                        <i class="fas fa-phone-alt text-primary me-2"></i>
                        <a href="https://wa.me/{{ $phoneNumber }}?text={{ $generalMessage }}" class="text-dark"
                            target="_blank" rel="noopener noreferrer">
                            {{ $whatsappCall }}
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
                <i class="fas fa-sign-in-alt me-2"></i> {{ __('auth.login') }}
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
                        <div class="package-slider swiper">
                            <div class="swiper-wrapper pb-4">
                                @foreach ($packages->where('category', $category) as $package)
                                    <div class="swiper-slide">
                                        <div class="package-internet">
                                            <!-- Header with dynamic class -->
                                            <div
                                                class="internet-card
                                                    @if ($category == 'Internet Only') internet-only
                                                    @elseif($category == 'Internet Stream') internet-stream
                                                    @else internet-tv @endif">
                                                <h3 class="package-title">{{ $package->formatted_name }}</h3>
                                            </div>

                                            <div class="package-price">
                                                <div class="package-price-value">
                                                    <p>{{ $package->formatted_price }}
                                                    </p>
                                                </div>
                                                <p class="package-price-note">Harga Sudah Termasuk PPN</p>
                                            </div>

                                            <hr class="package-divider" />

                                            <!-- Features List -->
                                            <div class="list-package-point">
                                                <div class="list-package-item">
                                                    <div class="list-package-icon">
                                                        <i class="fas fa-laptop"></i>
                                                    </div>
                                                    <p class="list-package-text">
                                                        Ideal untuk
                                                        {{ $package->recommended_device_min }}-{{ $package->recommended_device_max }}
                                                        perangkat
                                                    </p>
                                                </div>

                                                <!-- Quota -->
                                                <div class="list-package-item">
                                                    <div class="list-package-icon">
                                                        <i class="fas fa-database text-sm"></i>
                                                    </div>
                                                    <p class="list-package-text">
                                                        {{ $package->quota ?? 'Unlimited' }} kuota
                                                    </p>
                                                </div>

                                                <!-- Modem -->
                                                <div class="list-package-item">
                                                    <div class="list-package-icon">
                                                        <i class="fas fa-wifi text-sm"></i>
                                                    </div>
                                                    <p class="list-package-text">
                                                        Modem {{ $package->include_modem ? 'Termasuk' : 'Tidak termasuk' }}
                                                    </p>
                                                </div>

                                                <!-- Installation -->
                                                <div class="list-package-item">
                                                    <div class="list-package-icon">
                                                        <i class="fas fa-tools text-sm"></i>
                                                    </div>
                                                    <p class="list-package-text">
                                                        Instalasi <span class="font-semibold">
                                                            {{ $package->is_installation_free ? 'Gratis' : 'Rp ' . number_format($package->installation_fee, 0, ',', '.') }}
                                                        </span>
                                                    </p>
                                                </div>
                                            </div>

                                            <hr class="package-divider" />

                                            <!-- CTA Buttons -->
                                            <div
                                                class="cta-package-buttons
                                                    @if ($category == 'Internet Only') internet-only
                                                    @elseif($category == 'Internet Stream') internet-stream
                                                    @else internet-tv @endif">
                                                <a href="https://wa.me/{{ $phoneNumber }}?text={{ $package->wa_message }}"
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
                    <img src="/images/logo_footer_customer.png" alt="Shawnn Network Fiber" height="75"
                        class="mb-3">
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
                            <a href="https://wa.me/{{ $phoneNumber }}?text={{ $generalMessage }}" class="text-dark"
                                target="_blank" rel="noopener noreferrer">
                                {{ $whatsappCall }}
                            </a>
                        </li>
                        <li class="mb-2"><i class="fas fa-envelope me-2"></i> <a
                                href="mailto:{{ $websystem->email }}?subject={{ urlencode('Info Paket Internet Shanetfiber') }}&body={{ urlencode($generalMessage) }}"
                                class="text-dark d-none d-md-inline" title="Kirim via aplikasi email">
                                {{ $websystem->email }}
                            </a>
                            <a href="https://mail.google.com/mail/?view=cm&fs=1&to={{ urlencode($websystem->email) }}&su={{ urlencode('Info Paket Internet Shanetfiber') }}&body={{ urlencode($generalMessage) }}"
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
            PT GLOBAL SARANA ELEKTRONIK
        </div>
        <div id="copyright-content" class="copyright-text text-center">
            &copy; 2025 Shanum Network Fiber. All rights reserved.
        </div>
    </footer>

    <!-- Login Modal -->
    <div class="modal fade" id="loginModal" tabindex="-1" aria-labelledby="loginModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-md">
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

                    <!-- Forgot Password trigger -->
                    <div class="mt-2 text-end">
                        <a href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#forgotPasswordModal">
                            {{ __('Forgot Your Password?') }}
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Forgot Password -->
    <div class="modal fade" id="forgotPasswordModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Lupa Password</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">

                    <!-- Step 1: Pilih Channel -->
                    <div id="step-channel">
                        <p>Pilih metode reset password:</p>
                        <button class="btn btn-primary w-100 mb-2" onclick="showStep('step-confirm', 'email')">Via
                            Email</button>
                        <button class="btn btn-success w-100" onclick="showStep('step-confirm', 'whatsapp')">Via
                            WhatsApp</button>
                        <div class="text-center mt-3">
                            <button type="button" class="btn btn-link" onclick="showLoginModal()">Ingat password?
                                Kembali ke Login</button>
                        </div>
                    </div>

                    <!-- Step 2: Input ulang email/nomor -->
                    <div id="step-confirm" class="d-none">
                        <form id="form-confirm" method="POST" action="{{ route('password.channel.verify') }}">
                            @csrf
                            <input type="hidden" name="channel" id="channelInput">
                            <div class="mb-2">
                                <label id="confirmLabel"></label>
                                <input type="text" name="login" class="form-control" required>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">Kirim OTP</button>
                        </form>
                        <button type="button" class="btn btn-link mt-2" onclick="showStep('step-channel')">Ubah
                            Channel</button>
                        <div class="text-center mt-3">
                            <button type="button" class="btn btn-link" onclick="showLoginModal()">Ingat password?
                                Kembali ke Login</button>
                        </div>
                    </div>

                    <!-- Step 3: Input OTP -->
                    <div id="step-otp" class="d-none">
                        <form id="form-otp" method="POST" action="{{ route('password.otp.verify') }}">
                            @csrf
                            <input type="text" name="otp" class="form-control mb-2" placeholder="Masukkan OTP"
                                required>
                            <button type="submit" class="btn btn-primary w-100">Verifikasi</button>
                        </form>
                        <form method="POST" action="{{ route('password.otp.resend') }}" class="mt-2">
                            @csrf
                            <button type="submit" id="btn-resend" class="btn btn-link">Kirim Ulang OTP</button>
                        </form>
                        <button type="button" class="btn btn-link mt-2" onclick="showStep('step-channel')">Ubah
                            Channel</button>
                        <div class="text-center mt-3">
                            <button type="button" class="btn btn-link" onclick="showLoginModal()">Ingat password?
                                Kembali ke Login</button>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
@endsection

@section('custom_scripts')
    <script>
        // pasang listener sekali saja, misal di bawah script
        document.getElementById('forgotPasswordModal')
            .addEventListener('hidden.bs.modal', function() {
                // reset state setelah modal benar-benar tertutup
                document.getElementById('step-channel').classList.remove('d-none');
                document.getElementById('step-confirm').classList.add('d-none');
                document.getElementById('step-otp').classList.add('d-none');
                document.getElementById('form-confirm').reset();
                document.getElementById('form-otp').reset();
            });

        function showLoginModal() {
            let forgotEl = document.getElementById('forgotPasswordModal');
            let forgotModal = bootstrap.Modal.getInstance(forgotEl);
            if (forgotModal) forgotModal.hide();

            let loginModal = new bootstrap.Modal(document.getElementById('loginModal'));
            loginModal.show();
        }

        function showStep(step, channel = null) {
            document.getElementById('step-channel').classList.add('d-none');
            document.getElementById('step-confirm').classList.add('d-none');
            document.getElementById('step-otp').classList.add('d-none');

            document.getElementById(step).classList.remove('d-none');

            if (channel) {
                document.getElementById('channelInput').value = channel;
                document.getElementById('confirmLabel').innerText =
                    channel === 'email' ? 'Masukkan Email Anda' : 'Masukkan Nomor WhatsApp';
            }
        }
    </script>
@endsection
