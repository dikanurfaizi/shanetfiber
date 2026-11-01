@extends('layouts.cust-guest')

@section('content')
    <!-- Navigation Bar Glassmorphism -->
    <nav class="navbar navbar-expand-lg navbar-glass sticky-top">
        <div class="container-fluid px-3 px-lg-5">
            <div class="d-flex align-items-center w-100 justify-content-between">
                <div class="d-flex align-items-center">
                    <!-- Navbar Logo (Background Putih Padat Khusus Logo) -->
                    <a class="navbar-brand mr-3 mr-lg-4" href="#">
                        <div class="logo-white-box">
                            <img src="{{ asset('images/logo_top_customer.png') }}" alt="Shanum Network Fiber"
                                class="logo-img-top">
                        </div>
                    </a>

                    <!-- Contact Info Header (Desktop) -->
                    <div id="contact" class="d-none d-lg-flex align-items-center mr-auto text-white-50 small">
                        {{-- Telepon / WhatsApp --}}
                        <div class="contact-item mr-3">
                            <i class="fas fa-phone-alt text-success mr-2"></i>
                            <a href="https://wa.me/{{ $phoneNumber }}?text={{ $generalMessage }}"
                                class="text-white text-decoration-none" target="_blank" rel="noopener noreferrer">
                                {{ $whatsappCall }}
                            </a>
                        </div>

                        <div class="contact-item">
                            <i class="fas fa-envelope text-info mr-2"></i>
                            <a href="mailto:{{ $websystem->email }}?subject={{ urlencode(__('customer.info.email_subject') ?? 'Info Paket Internet Shanetfiber') }}&body={{ urlencode($generalMessage) }}"
                                class="text-white text-decoration-none d-none d-md-inline" title="Kirim via aplikasi email">
                                {{ $websystem->email }}
                            </a>
                            <a href="https://mail.google.com/mail/?view=cm&fs=1&to={{ urlencode($websystem->email) }}&su={{ urlencode(__('customer.info.email_subject') ?? 'Info Paket Internet Shanetfiber') }}&body={{ urlencode($generalMessage) }}"
                                class="text-white text-decoration-none d-inline d-md-none" target="_blank"
                                rel="noopener noreferrer" title="Kirim via Gmail">
                                {{ $websystem->email }}
                            </a>
                        </div>
                    </div>
                </div>

                <button class="btn btn-glass-primary px-3 px-lg-4 py-2" data-toggle="modal" data-target="#loginModal">
                    <i class="fas fa-sign-in-alt mr-2"></i> {{ __('customer.button.login') ?? 'Masuk' }}
                </button>
            </div>
        </div>
    </nav>

    <!-- Hero Slider -->
    <div class="hero-slider swiper mt-0">
        <div class="swiper-wrapper">
            <!-- Slide 1 -->
            <div class="swiper-slide">
                <div class="slider-image" style="background-image: url('/images/slider1.jpeg');">
                    <div class="slider-overlay-glass"></div>
                    <div class="container-fluid h-100 d-flex align-items-center px-4 px-lg-5 position-relative z-index-2">
                        <div class="slider-content text-left text-white">
                            <h1 class="display-5 display-lg-2 fw-bold mb-3">
                                {{ __('customer.hero.slide1_title') ?? 'Solusi Internet Super Cepat' }}</h1>
                            <p class="lead mb-4 d-none d-sm-block text-white-50">
                                {{ __('customer.hero.slide1_subtitle') ?? 'Nikmati konektivitas tanpa batas dengan infrastruktur jaringan terpercaya kami' }}
                            </p>
                            <div class="d-flex flex-column flex-sm-row">
                                <a href="#packages"
                                    class="btn btn-glass-primary btn-lg px-4 py-2 fs-6 mr-sm-2 mb-2 mb-sm-0">{{ __('customer.hero.view_packages') ?? 'Lihat Paket' }}</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Slide 2 -->
            <div class="swiper-slide">
                <div class="slider-image" style="background-image: url('/images/slider2.png');">
                    <div class="slider-overlay-glass"></div>
                    <div class="container-fluid h-100 d-flex align-items-center px-4 px-lg-5 position-relative z-index-2">
                        <div class="slider-content text-left text-white">
                            <h1 class="display-5 display-lg-2 fw-bold mb-3">
                                {{ __('customer.hero.slide2_title') ?? 'Jaringan Fiber Optik Andal' }}</h1>
                            <p class="lead mb-4 d-none d-sm-block text-white-50">
                                {{ __('customer.hero.slide2_subtitle') ?? 'Koneksi stabil untuk kebutuhan rumah dan bisnis Anda' }}
                            </p>
                            <div class="d-flex flex-column flex-sm-row">
                                <a href="#packages"
                                    class="btn btn-glass-primary btn-lg px-4 py-2 fs-6 mr-sm-2 mb-2 mb-sm-0">{{ __('customer.hero.view_packages') ?? 'Lihat Paket' }}</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Slide 3 -->
            <div class="swiper-slide">
                <div class="slider-image" style="background-image: url('/images/slider3.jpeg');">
                    <div class="slider-overlay-glass"></div>
                    <div class="container-fluid h-100 d-flex align-items-center px-4 px-lg-5 position-relative z-index-2">
                        <div class="slider-content text-left text-white">
                            <h1 class="display-5 display-lg-2 fw-bold mb-3">
                                {{ __('customer.hero.slide3_title') ?? 'Layanan Pelanggan 24/7' }}</h1>
                            <p class="lead mb-4 d-none d-sm-block text-white-50">
                                {{ __('customer.hero.slide3_subtitle') ?? 'Tim kami selalu siap membantu Anda setiap saat' }}
                            </p>
                            <div class="d-flex flex-column flex-sm-row">
                                <a href="#packages"
                                    class="btn btn-glass-primary btn-lg px-4 py-2 fs-6 mr-sm-2 mb-2 mb-sm-0">{{ __('customer.hero.view_packages') ?? 'Lihat Paket' }}</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="swiper-pagination"></div>
        <div class="swiper-button-next d-none d-md-flex"></div>
        <div class="swiper-button-prev d-none d-md-flex"></div>
    </div>

    <!-- Section Packages -->
    <div class="container-fluid py-5" id="packages" style="background: linear-gradient(180deg, #0f172a 0%, #1e1b4b 100%);">
        <div class="text-center mb-4">
            <h2 class="text-white font-weight-bold mb-2">
                {{ __('customer.package.choose_title') ?? 'Pilih Paket Internet Anda' }}</h2>
            <p class="text-white-50">
                {{ __('customer.package.choose_subtitle') ?? 'Layanan internet cepat dan stabil sesuai kebutuhan Anda' }}
            </p>
        </div>

        <!-- Category Tab Navigation Glass -->
        <div class="d-flex justify-content-center mb-4 px-2">
            <div class="btn-group category-tab-wrapper p-1" role="group" id="categoryTabs">
                @foreach ($categories as $index => $category)
                    <button type="button" data-category="{{ $category }}"
                        class="category-tab btn border-0 py-2 px-3 px-md-4 transition {{ $index === 0 ? 'active' : '' }}"
                        onclick="changeCategory('{{ $category }}')">
                        {{ __("customer.category.{$category}") ?? $category }}
                    </button>
                @endforeach
            </div>
        </div>

        <!-- Packages Container -->
        <div id="packagesContainer" class="px-2 px-md-4">
            @foreach ($categories as $index => $category)
                <div class="category-packages {{ $index === 0 ? '' : 'd-none' }}" id="{{ $category }}Packages">
                    <div class="position-relative py-3">
                        <div class="package-slider swiper">
                            <div class="swiper-wrapper pb-4">
                                @foreach ($packages->where('category', $category) as $package)
                                    <div class="swiper-slide h-auto">
                                        <div
                                            class="package-glass-card d-flex flex-column justify-content-between h-100 p-4">
                                            <div>
                                                <!-- Header Card -->
                                                <div class="glass-card-header text-center pb-3 mb-3 border-glass-bottom">
                                                    <span
                                                        class="badge bg-glass-light text-white mb-2 px-3 py-1 font-monospace">
                                                        {{ __("customer.category.{$category}") ?? $category }}
                                                    </span>
                                                    <h3 class="package-title text-white font-weight-bold h4 mb-0">
                                                        {{ $package->formatted_name }}</h3>
                                                </div>

                                                <!-- Price Section -->
                                                <div class="package-price text-center mb-3">
                                                    <div
                                                        class="package-price-value h3 font-weight-bold text-white text-center d-block mb-1">
                                                        {{ $package->formatted_price }}
                                                    </div>
                                                    <small class="text-white-50 text-center d-block">
                                                        {{ __('customer.package.vat_included') ?? 'Harga Sudah Termasuk PPN' }}
                                                    </small>
                                                </div>

                                                <hr class="border-glass my-3" />

                                                <!-- Features List -->
                                                <ul class="list-unstyled mb-4 text-white-50 small">
                                                    <li class="d-flex align-items-center mb-3">
                                                        <div class="icon-circle mr-3"><i
                                                                class="fas fa-laptop text-info"></i></div>
                                                        <span>{{ __('customer.package.ideal_for') ?? 'Ideal untuk' }}
                                                            <strong>{{ $package->recommended_device_min }}-{{ $package->recommended_device_max }}</strong>
                                                            {{ __('customer.package.devices') ?? 'perangkat' }}</span>
                                                    </li>
                                                    <li class="d-flex align-items-center mb-3">
                                                        <div class="icon-circle mr-3"><i
                                                                class="fas fa-database text-warning"></i></div>
                                                        <span><strong>{{ $package->quota ?? __('customer.package.unlimited') }}</strong>
                                                            {{ __('customer.package.quota') ?? 'kuota' }}</span>
                                                    </li>
                                                    <li class="d-flex align-items-center mb-3">
                                                        <div class="icon-circle mr-3"><i
                                                                class="fas fa-wifi text-success"></i></div>
                                                        <span>{{ __('customer.package.modem') ?? 'Modem' }}
                                                            <strong>{{ $package->include_modem ? __('customer.package.included') ?? 'Termasuk' : __('customer.package.not_included') ?? 'Tidak termasuk' }}</strong></span>
                                                    </li>
                                                    <li class="d-flex align-items-center">
                                                        <div class="icon-circle mr-3"><i
                                                                class="fas fa-tools text-primary"></i></div>
                                                        <span>{{ __('customer.package.installation') ?? 'Instalasi' }}
                                                            <strong
                                                                class="text-white">{{ $package->is_installation_free ? __('customer.package.free') ?? 'Gratis' : 'Rp ' . number_format($package->installation_fee, 0, ',', '.') }}</strong></span>
                                                    </li>
                                                </ul>
                                            </div>

                                            <!-- CTA Buttons -->
                                            <div class="cta-package-buttons mt-3">
                                                <a href="https://wa.me/{{ $phoneNumber }}?text={{ $package->wa_message }}"
                                                    target="_blank" rel="noopener noreferrer"
                                                    class="btn btn-glass-primary btn-block py-2 font-weight-bold">
                                                    {{ __('customer.button.subscribe') ?? 'Berlangganan' }}
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            <div class="swiper-button-next d-none d-lg-flex"></div>
                            <div class="swiper-button-prev d-none d-lg-flex"></div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Footer Custom Glass -->
    <footer class="footer-glass py-5 px-3">
        <div class="container-fluid px-3 px-lg-5">
            <div class="row">
                <div class="col-lg-6 col-md-12 mb-4 mb-lg-0">
                    <!-- Footer Logo (Background Putih Padat Kustom) -->
                    <div class="logo-white-box logo-footer-box mb-3">
                        <img src="{{ asset('images/logo_footer_customer.png') }}" alt="Shanum Network Fiber"
                            class="logo-img-footer">
                    </div>
                    <p class="text-white-50 small mb-0">
                        <a href="https://maps.app.goo.gl/zRT4QfXNmoJysKRQ8" target="_blank" rel="noopener noreferrer"
                            class="text-white-50 text-decoration-none hover-white">
                            <i class="fas fa-map-marker-alt mr-2 text-danger"></i>{{ $websystem->address }},
                            {{ $websystem->city }}, {{ $websystem->postal_code }}
                        </a>
                    </p>
                </div>
                <div class="col-lg-3 col-md-6 mb-4 mb-md-0">
                    <h6 class="mb-3 font-weight-bold text-white text-uppercase tracking-wider">
                        {{ __('customer.footer.quick_links') ?? 'Tautan Cepat' }}</h6>
                    <ul class="list-unstyled small">
                        <li class="mb-2"><a href="#packages"
                                class="text-white-50 text-decoration-none hover-white">{{ __('customer.footer.packages') ?? 'Paket Internet' }}</a>
                        </li>
                        <li class="mb-2"><a href="#contact"
                                class="text-white-50 text-decoration-none hover-white">{{ __('customer.footer.contact_us') ?? 'Hubungi Kami' }}</a>
                        </li>
                        <li><a href="#"
                                class="text-white-50 text-decoration-none hover-white">{{ __('customer.footer.terms') ?? 'Syarat & Ketentuan' }}</a>
                        </li>
                    </ul>
                </div>
                <div class="col-lg-3 col-md-6">
                    <h6 class="mb-3 font-weight-bold text-white text-uppercase tracking-wider">
                        {{ __('customer.footer.contact') ?? 'Kontak' }}</h6>
                    <ul class="list-unstyled text-white-50 small">
                        <li class="mb-2">
                            <i class="fas fa-phone-alt mr-2 text-success"></i>
                            <a href="https://wa.me/{{ $phoneNumber }}?text={{ $generalMessage }}"
                                class="text-white-50 text-decoration-none hover-white" target="_blank"
                                rel="noopener noreferrer">
                                {{ $whatsappCall }}
                            </a>
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-envelope mr-2 text-info"></i>
                            <a href="mailto:{{ $websystem->email }}?subject={{ urlencode(__('customer.info.email_subject') ?? 'Info Paket Internet Shanetfiber') }}&body={{ urlencode($generalMessage) }}"
                                class="text-white-50 text-decoration-none hover-white" title="Kirim via aplikasi email">
                                {{ $websystem->email }}
                            </a>
                        </li>
                        <li>
                            <i class="fas fa-map-marker-alt mr-2 text-danger"></i>
                            <a href="https://maps.app.goo.gl/zRT4QfXNmoJysKRQ8"
                                class="text-white-50 text-decoration-none hover-white" target="_blank"
                                rel="noopener noreferrer">
                                Purwakarta, Indonesia
                            </a>
                        </li>
                    </ul>
                </div>
            </div>

            <hr class="my-4 border-glass">

            <div class="text-center text-white-50 small mb-1">
                MITRA RESELLER<br>
                PT GLOBAL SARANA ELEKTRONIK
            </div>
            <div class="text-center text-white-50 small">
                &copy; {{ date('Y') }} Shanum Network Fiber. All rights reserved.
            </div>
        </div>
    </footer>

    <!-- Login Modal Glass -->
    <div class="modal fade" id="loginModal" tabindex="-1" role="dialog" aria-labelledby="loginModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-md" role="document">
            <div class="modal-content glass-card p-3 p-sm-4">
                <div class="modal-header border-0 pb-0 justify-content-end position-relative">
                    <button type="button" class="close custom-modal-close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body pt-0">
                    <div class="text-center mb-4">
                        <div class="glass-logo-wrapper mb-3 mx-auto d-flex align-items-center justify-content-center">
                            <img src="{{ asset('images/logo-secondary.png') }}" alt="Logo"
                                class="img-fluid glass-logo">
                        </div>
                        <h5 class="text-white font-weight-bold mb-1">
                            {{ __('customer.login.title') ?? 'Cek Tagihan Anda' }}</h5>
                        <small
                            class="text-white-50">{{ __('customer.login.subtitle') ?? 'Masuk untuk melihat detail tagihan' }}</small>
                    </div>

                    <form action="{{ route('customerLoginPost') }}" method="post">
                        @csrf
                        <div class="form-group mb-3">
                            <input type="text" name="email" value="{{ old('email') }}"
                                class="form-control form-control-glass @error('email') is-invalid @enderror"
                                id="exampleInputEmail"
                                placeholder="{{ __('customer.placeholder.email_or_phone') ?? 'Email atau Nomor HP' }}"
                                required autofocus>
                            @error('email')
                                <small class="text-danger mt-1 d-block font-weight-semibold pl-2">{{ $message }}</small>
                            @enderror
                        </div>

                        <div class="form-group mb-3 position-relative">
                            <input type="password" name="password"
                                class="form-control form-control-glass @error('password') is-invalid @enderror"
                                id="passwordInput" placeholder="{{ __('customer.placeholder.password') ?? 'Password' }}"
                                required>
                            <span class="password-toggle-btn" onclick="togglePasswordVisibility()">
                                <i id="passwordIcon" class="fas fa-eye text-white-50"></i>
                            </span>
                            @error('password')
                                <small class="text-danger mt-1 d-block font-weight-semibold pl-2">{{ $message }}</small>
                            @enderror
                        </div>

                        <button type="submit"
                            class="btn btn-glass-primary btn-block font-weight-bold py-2 shadow-sm">{{ __('customer.button.login') ?? 'Login' }}</button>

                        <div class="mt-3 text-center">
                            <a href="javascript:void(0)" class="small text-white-50 hover-white text-decoration-none"
                                onclick="openForgotPasswordFromLogin()">
                                {{ __('customer.button.forgot_password') ?? 'Lupa Password?' }}
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Forgot Password Glass -->
    <div class="modal fade" id="forgotPasswordModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content glass-card p-3 p-sm-4">
                <div class="modal-header border-0 pb-0 justify-content-between align-items-center">
                    <h5 class="modal-title text-white font-weight-bold">
                        {{ __('customer.forgot.modal_title') ?? 'Lupa Password' }}
                    </h5>
                    <button type="button" class="close custom-modal-close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body text-white">

                    <!-- Step 1: Pilih Channel -->
                    <div id="step-channel">
                        <p class="text-white-50 mb-3">
                            {{ __('customer.forgot.choose_method') ?? 'Pilih metode reset password:' }}</p>
                        <button class="btn btn-glass-primary btn-block mb-2 py-2 font-weight-bold"
                            onclick="showStep('step-confirm', 'email')">
                            <i class="fas fa-envelope mr-2"></i>{{ __('customer.forgot.via_email') ?? 'Via Email' }}
                        </button>
                        <button class="btn btn-success btn-block mb-3 py-2 font-weight-bold"
                            onclick="showStep('step-confirm', 'whatsapp')">
                            <i class="fab fa-whatsapp mr-2"></i>{{ __('customer.forgot.via_whatsapp') ?? 'Via WhatsApp' }}
                        </button>
                        <div class="text-center mt-3">
                            <button type="button"
                                class="btn btn-link text-white-50 hover-white text-decoration-none p-0 small"
                                onclick="showLoginModal()">
                                {{ __('customer.forgot.back_to_login') ?? 'Ingat password? Kembali ke Login' }}
                            </button>
                        </div>
                    </div>

                    <!-- Step 2: Input email/nomor -->
                    <div id="step-confirm" class="d-none">
                        <form id="form-confirm" method="POST" action="{{ route('password.channel.verify') }}">
                            @csrf
                            <input type="hidden" name="channel" id="channelInput">
                            <div class="form-group mb-3">
                                <label id="confirmLabel" class="form-label text-white-50 small mb-1"></label>
                                <input type="text" name="login" class="form-control form-control-glass" required>
                            </div>
                            <button type="submit"
                                class="btn btn-glass-primary btn-block py-2 font-weight-bold">{{ __('customer.forgot.send_otp') ?? 'Kirim OTP' }}</button>
                        </form>
                        <div class="text-center mt-2">
                            <button type="button"
                                class="btn btn-link text-white-50 hover-white text-decoration-none p-0 small"
                                onclick="showStep('step-channel')">
                                {{ __('customer.forgot.change_channel') ?? 'Ubah Channel' }}
                            </button>
                        </div>
                        <div class="text-center mt-3">
                            <button type="button"
                                class="btn btn-link text-white-50 hover-white text-decoration-none p-0 small"
                                onclick="showLoginModal()">
                                {{ __('customer.forgot.back_to_login') ?? 'Ingat password? Kembali ke Login' }}
                            </button>
                        </div>
                    </div>

                    <!-- Step 3: Input OTP -->
                    <div id="step-otp" class="d-none">
                        <form id="form-otp" method="POST" action="{{ route('password.otp.verify') }}">
                            @csrf
                            <div class="form-group mb-3">
                                <input type="text" name="otp" class="form-control form-control-glass"
                                    placeholder="{{ __('customer.forgot.otp_placeholder') ?? 'Masukkan OTP' }}" required>
                            </div>
                            <button type="submit"
                                class="btn btn-glass-primary btn-block py-2 font-weight-bold">{{ __('customer.forgot.verify') ?? 'Verifikasi' }}</button>
                        </form>
                        <form method="POST" action="{{ route('password.otp.resend') }}" class="mt-2 text-center">
                            @csrf
                            <button type="submit" id="btn-resend"
                                class="btn btn-link text-white-50 hover-white text-decoration-none p-0 small">
                                {{ __('customer.forgot.resend_otp') ?? 'Kirim Ulang OTP' }}
                            </button>
                        </form>
                        <div class="text-center mt-2">
                            <button type="button"
                                class="btn btn-link text-white-50 hover-white text-decoration-none p-0 small"
                                onclick="showStep('step-channel')">
                                {{ __('customer.forgot.change_channel') ?? 'Ubah Channel' }}
                            </button>
                        </div>
                        <div class="text-center mt-3">
                            <button type="button"
                                class="btn btn-link text-white-50 hover-white text-decoration-none p-0 small"
                                onclick="showLoginModal()">
                                {{ __('customer.forgot.back_to_login') ?? 'Ingat password? Kembali ke Login' }}
                            </button>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
@endsection

@section('custom_styles')
    <style>
        /* ===== GLASSMORPHISM GENERAL STYLES (BOOTSTRAP 4 COMPATIBLE) ===== */
        body {
            background-color: #0f172a;
            color: #ffffff;
            font-family: system-ui, -apple-system, "Segoe UI", Roboto, sans-serif;
        }

        /* Navbar Glass */
        .navbar-glass {
            background: rgba(15, 23, 42, 0.75) !important;
            backdrop-filter: blur(12px) saturate(180%);
            -webkit-backdrop-filter: blur(12px) saturate(180%);
            border-bottom: 1px solid rgba(255, 255, 255, 0.12);
        }

        /* KHUSUS LOGO: Box Putih Padat */
        .logo-white-box {
            background: #ffffff !important;
            border-radius: 12px;
            padding: 6px 14px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        }

        .logo-img-top {
            height: 32px;
            width: auto;
            object-fit: contain;
        }

        /* Footer Logo Box (Lebih Kompak) */
        .logo-footer-box {
            padding: 5px 12px;
            border-radius: 10px;
        }

        .logo-img-footer {
            height: 36px;
            width: auto;
            object-fit: contain;
        }

        /* Slider Overlay Glass */
        .slider-overlay-glass {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(180deg, rgba(15, 23, 42, 0.5) 0%, rgba(15, 23, 42, 0.85) 100%);
            backdrop-filter: blur(3px);
            -webkit-backdrop-filter: blur(3px);
        }

        /* CATEGORY TAB GLASS (PERBAIKAN BACKGROUND BLANK / WHITE) */
        .category-tab-wrapper {
            background: rgba(255, 255, 255, 0.08) !important;
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.15);
            border-radius: 50px;
        }

        /* Tab Kategori Tidak Dipilih: Glass Blur Soft & Text Jelas */
        .category-tab {
            border-radius: 50px !important;
            background: rgba(255, 255, 255, 0.12) !important;
            color: rgba(255, 255, 255, 0.85) !important;
            font-weight: 600;
            font-size: 14px;
            border: 1px solid rgba(255, 255, 255, 0.1) !important;
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
            margin: 0 2px;
            transition: all 0.3s ease;
        }

        .category-tab:hover {
            background: rgba(255, 255, 255, 0.22) !important;
            color: #ffffff !important;
            border-color: rgba(255, 255, 255, 0.3) !important;
        }

        /* Tab Kategori Aktif: Gradasi Biru Modern */
        .category-tab.active {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%) !important;
            color: #ffffff !important;
            border-color: rgba(255, 255, 255, 0.4) !important;
            box-shadow: 0 4px 15px rgba(37, 99, 235, 0.4);
        }

        /* Package Glass Card */
        .package-glass-card {
            background: rgba(255, 255, 255, 0.07) !important;
            backdrop-filter: blur(16px) saturate(180%);
            -webkit-backdrop-filter: blur(16px) saturate(180%);
            border: 1px solid rgba(255, 255, 255, 0.15) !important;
            border-radius: 20px !important;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3) !important;
            transition: all 0.3s ease;
            color: #ffffff !important;
        }

        .package-glass-card:hover {
            transform: translateY(-5px);
            border-color: rgba(255, 255, 255, 0.35) !important;
            box-shadow: 0 15px 35px rgba(59, 130, 246, 0.25) !important;
        }

        .bg-glass-light {
            background: rgba(255, 255, 255, 0.12);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 50px;
        }

        .icon-circle {
            width: 32px;
            height: 32px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        /* Glass Modal Container */
        .glass-card {
            background: rgba(30, 41, 59, 0.92) !important;
            backdrop-filter: blur(20px) saturate(180%);
            -webkit-backdrop-filter: blur(20px) saturate(180%);
            border: 1px solid rgba(255, 255, 255, 0.18) !important;
            border-radius: 24px !important;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.5) !important;
        }

        /* Custom Modal Close Button Bootstrap 4 (Fix Tombol X) */
        .custom-modal-close {
            color: #ffffff !important;
            text-shadow: 0 0 10px rgba(0, 0, 0, 0.5);
            opacity: 0.8;
            font-size: 1.75rem;
            font-weight: 300;
            padding: 0.5rem 0.75rem !important;
            margin: -0.5rem -0.75rem -0.5rem auto !important;
            outline: none !important;
            transition: all 0.2s ease;
            background: transparent;
            border: none;
        }

        .custom-modal-close:hover {
            color: #ffffff !important;
            opacity: 1;
            transform: scale(1.1);
        }

        .glass-logo-wrapper {
            width: 85px;
            height: 85px;
            background: rgba(255, 255, 255, 0.12);
            border: 1px solid rgba(255, 255, 255, 0.25);
            border-radius: 22px;
            padding: 12px;
        }

        /* Form Controls Glass */
        .form-control-glass {
            background: rgba(255, 255, 255, 0.08) !important;
            border: 1px solid rgba(255, 255, 255, 0.2) !important;
            color: #ffffff !important;
            border-radius: 12px !important;
            padding: 0.75rem 1rem !important;
            height: 48px;
        }

        .form-control-glass::placeholder {
            color: rgba(255, 255, 255, 0.45) !important;
        }

        .form-control-glass:focus {
            background: rgba(255, 255, 255, 0.16) !important;
            border-color: rgba(255, 255, 255, 0.5) !important;
            box-shadow: 0 0 15px rgba(59, 130, 246, 0.3) !important;
            color: #ffffff !important;
        }

        .password-toggle-btn {
            position: absolute;
            right: 14px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            z-index: 10;
        }

        /* Buttons Glass */
        .btn-glass-primary {
            background: linear-gradient(135deg, rgba(59, 130, 246, 0.9), rgba(37, 99, 235, 1)) !important;
            border: 1px solid rgba(255, 255, 255, 0.25) !important;
            color: #ffffff !important;
            border-radius: 12px !important;
            transition: all 0.2s ease;
        }

        .btn-glass-primary:hover {
            box-shadow: 0 8px 20px rgba(37, 99, 235, 0.4) !important;
            transform: translateY(-1px);
            color: #ffffff !important;
        }

        /* Footer Glass */
        .footer-glass {
            background: rgba(15, 23, 42, 0.95);
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }

        /* Borders & Utilities */
        .border-glass {
            border-top: 1px solid rgba(255, 255, 255, 0.12) !important;
        }

        .border-glass-bottom {
            border-bottom: 1px solid rgba(255, 255, 255, 0.12) !important;
        }

        .hover-white:hover {
            color: #ffffff !important;
        }

        /* ===== OPTIMASI MOBILE ===== */
        @media (max-width: 767.98px) {
            .logo-white-box {
                padding: 4px 10px;
                border-radius: 8px;
            }

            .logo-img-top {
                height: 26px;
            }

            .logo-img-footer {
                height: 28px;
            }

            .glass-card {
                width: 95% !important;
                margin: 0 auto;
                border-radius: 20px !important;
            }

            .category-tab-wrapper {
                width: 100%;
                display: flex;
                overflow-x: auto;
                white-space: nowrap;
            }

            .category-tab {
                flex: 1;
                font-size: 13px;
                padding: 8px 12px !important;
            }

            .package-glass-card {
                padding: 1.25rem !important;
            }
        }

        .package-price,
        .package-price-value {
            text-align: center !important;
            justify-content: center !important;
        }
    </style>
@endsection

@section('custom_scripts')
    <script>
        // Toggle Visibility Password
        function togglePasswordVisibility() {
            let input = document.getElementById("passwordInput");
            let icon = document.getElementById("passwordIcon");

            if (input.type === "password") {
                input.type = "text";
                icon.classList.remove("fa-eye");
                icon.classList.add("fa-eye-slash");
            } else {
                input.type = "password";
                icon.classList.remove("fa-eye-slash");
                icon.classList.add("fa-eye");
            }
        }

        // Handler Pergantian Modal Bootstrap 4
        function openForgotPasswordFromLogin() {
            $('#loginModal').modal('hide');
            setTimeout(function() {
                $('#forgotPasswordModal').modal('show');
            }, 350);
        }

        function showLoginModal() {
            $('#forgotPasswordModal').modal('hide');
            setTimeout(function() {
                $('#loginModal').modal('show');
            }, 350);
        }

        // Stepper Form Reset Password
        function showStep(step, channel = null) {
            $('#step-channel').addClass('d-none');
            $('#step-confirm').addClass('d-none');
            $('#step-otp').addClass('d-none');

            $('#' + step).removeClass('d-none');

            if (channel) {
                document.getElementById('channelInput').value = channel;
                document.getElementById('confirmLabel').innerText =
                    channel === 'email' ?
                    "{{ __('customer.forgot.enter_email') ?? 'Masukkan Email Anda' }}" :
                    "{{ __('customer.forgot.enter_whatsapp') ?? 'Masukkan Nomor WhatsApp' }}";
            }
        }

        // Event Listener Reset Modal ketika ditutup di Bootstrap 4 (jQuery)
        $(document).ready(function() {
            $('#forgotPasswordModal').on('hidden.bs.modal', function() {
                $('#step-channel').removeClass('d-none');
                $('#step-confirm').addClass('d-none');
                $('#step-otp').addClass('d-none');
                $('#form-confirm')[0]?.reset();
                $('#form-otp')[0]?.reset();
            });

            // Memastikan Tombol X Modal Menutup Modal dengan Sempurna
            $('.custom-modal-close').on('click', function() {
                $(this).closest('.modal').modal('hide');
            });
        });
    </script>
@endsection
