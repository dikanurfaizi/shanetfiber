@extends('layouts.guest')

@push('custom_styles')
    <style>
        /* Container Full Viewport untuk Posisi Tengah Sempurna */
        .install-wrapper {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem 0;
        }

        /* Efek iOS Glassmorphism Card yang Premium */
        .glass-card {
            background: rgba(30, 41, 59, 0.75) !important;
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 1.25rem;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.5);
        }

        .glass-alert {
            background: rgba(15, 23, 42, 0.6);
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 0.85rem;
        }
    </style>
@endpush

@section('content')
    <div class="install-wrapper container">
        <div class="row justify-content-center w-100">
            <div class="col-xl-7 col-lg-8 col-md-10">
                <div class="card glass-card o-hidden border-0 text-white">
                    <div class="card-body p-0">
                        <!-- Nested Row within Card Body -->
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="p-5">
                                    <div class="text-center mb-4">
                                        <h1 class="h4 text-white fw-bold tracking-wide">
                                            {{ __('install.title', ['default' => 'Install Customer Management']) }}
                                        </h1>
                                    </div>

                                    <div class="mb-4 text-light">
                                        {{-- Welcome Message dengan Glassmorphism Box --}}
                                        <div class="glass-alert p-4 mb-3">
                                            <h6 class="fw-bold text-white mb-2">
                                                {{ __('install.greeting', ['default' => 'Selamat datang kawan,']) }}
                                            </h6>
                                            <p class="mb-0 small text-gray-300 lh-base">
                                                {{ __('install.welcome_message', ['default' => 'Kelola customer mikrotik pakai Customer Management jadi lebih mudah.']) }}
                                            </p>
                                        </div>

                                        <p class="mb-3 small text-gray-400">
                                            {{ __('install.license_notice', ['default' => 'Source code ini gratis ya dan dilarang diperjualbelikan.']) }}
                                        </p>

                                        @if ($status === 'false')
                                            <div class="alert alert-warning border-0 shadow-xs mb-3 bg-warning bg-opacity-20 text-white"
                                                role="alert">
                                                <i class="fas fa-exclamation-triangle me-2"></i>
                                                {!! __('install.db_exists_warning', [
                                                    'db' => '<b class="text-danger">' . $dbName . '</b>',
                                                    'default' =>
                                                        'Ups... Database dengan nama ' .
                                                        $dbName .
                                                        ' sudah ada di servermu. Jika kamu ingin melanjutkan menginstall, database ' .
                                                        $dbName .
                                                        ' dapat terhapus secara ludes semua lo.',
                                                ]) !!}
                                            </div>
                                        @endif

                                        @if ($errors->has('setuju'))
                                            <div class="alert alert-danger border-0 shadow-xs text-center mb-3 bg-danger bg-opacity-25 text-white"
                                                role="alert">
                                                <strong>{{ $errors->first('setuju') }}</strong>
                                            </div>
                                        @endif
                                    </div>

                                    <form action="{{ route('installStart') }}" method="GET">
                                        <div class="form-check mb-4">
                                            <input class="form-check-input" type="checkbox" id="setuju" name="setuju"
                                                value="true">
                                            <label class="form-check-label text-gray-300 fw-semibold" for="setuju">
                                                {{ __('install.agreement_label', ['default' => 'Saya mengerti dan setuju untuk melanjutkan proses instalasi.']) }}
                                            </label>
                                        </div>

                                        <div class="text-center">
                                            @if ($status === 'true')
                                                <button type="submit"
                                                    class="btn btn-primary btn-user w-100 py-2 fw-bold shadow-sm">
                                                    {{ __('install.btn_install', ['default' => 'Install']) }}
                                                </button>
                                            @else
                                                <button type="submit"
                                                    class="btn btn-danger btn-user w-100 py-2 fw-bold shadow-sm">
                                                    {{ __('install.btn_overwrite', ['default' => 'Gak papa bro lanjut install dan hapus database lamaku!']) }}
                                                </button>
                                            @endif
                                        </div>
                                    </form>

                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
