@extends('layouts.guest')

@section('content')
    <div class="row justify-content-center align-items-center min-vh-100">
        <div class="col-xl-8 col-lg-10 col-md-9">
            <div class="card border-0 shadow-lg my-5"
                style="background: rgba(15, 23, 42, 0.75); backdrop-filter: blur(16px); -webkit-backdrop-filter: blur(16px); border-radius: 1rem; border: 1px solid rgba(255, 255, 255, 0.1);">
                <div class="card-body p-5 text-white">
                    <div class="text-center mb-4">
                        <h1 class="h4 font-weight-bold mb-3" style="color: #f8fafc;">{{ __('install.title.primary') }}</h1>
                    </div>

                    <div class="mb-4" style="font-size: 0.95rem; line-height: 1.6; color: #cbd5e1;">
                        {{ __('install.finish.desc_1') }}<br>
                        <b class="text-danger font-weight-bold">{{ __('install.title.important') }}</b><br>
                        {{ __('install.finish.desc_2') }}<br>
                        {{ __('install.finish.desc_3') }} <b class="text-danger">web.php</b>
                        {{ __('install.finish.desc_4') }} <i class="text-primary">root/routes</i>
                        {{ __('install.finish.desc_4') }} <i class="text-primary">customer-management/routes</i>,
                        {{ __('install.finish.desc_5') }}<br><br>

                        <div class="p-3 rounded"
                            style="background: rgba(0, 0, 0, 0.3); border: 1px solid rgba(255, 255, 255, 0.05); overflow-x: auto;">
                            <code class="text-danger" style="font-size: 0.85rem;">
                                Route::get('install', [InstallController::class, 'install'])->name('install');<br>
                                Route::get('install/start', [InstallController::class, 'start'])->name('install.start');<br>
                                Route::get('install/create', [InstallController::class,
                                'create_admin'])->name('installCreateAdmin');<br>
                                Route::post('install/store', [InstallController::class,
                                'store_admin'])->name('installStoreAdmin');<br>
                                Route::get('install/finish', [InstallController::class, 'finish'])->name('installFinish');
                            </code>
                        </div>
                    </div>

                    <div class="text-center mt-4">
                        <a class="btn btn-primary btn-user w-100 py-2 font-weight-bold shadow-sm"
                            href="{{ route('login') }}"
                            style="background-color: #2563eb; border: none; border-radius: 0.5rem;">
                            {{ __('install.button.login') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
