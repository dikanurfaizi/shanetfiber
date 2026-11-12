@extends('layouts.guest')

@section('content')
    <!-- Outer Row -->
    <div class="row justify-content-center">
        <div class="col-xl-10 col-lg-12 col-md-9">
            <div class="card o-hidden border-0 shadow-lg my-5">
                <div class="card-body p-0">
                    <!-- Nested Row within Card Body -->
                    <div class="row">

                        <div class="col-lg-12">
                            <div class="p-5">
                                <div class="text-center">
                                    <h1 class="h4 text-gray-900 mb-4">{{ __('Install Customer Management') }}</h1>
                                </div>
                                <div class="mb-3">
                                    Installasi Customer Management telah selesai.<br>
                                    <b class="text-danger">Penting!!!</b>
                                    Kamu harus menghapus route url installer.<br>
                                    Buka file <b class="text-danger">web.php</b> di folder "<i
                                        class="text-primary">root/routes</i>" atau "<i
                                        class="text-primary">customer-management/routes</i>",
                                    kemudian hapus kode: <br><br>
                                    <b class="text-danger">
                                        Route::get('install', [InstallController::class, 'install'])->name('install');<br>
                                        Route::get('install/start', [InstallController::class,
                                        'start'])->name('install.start');<br>
                                        Route::get('install/create', [InstallController::class,
                                        'create_admin'])->name('installCreateAdmin');<br>
                                        Route::post('install/store', [InstallController::class,
                                        'store_admin'])->name('installStoreAdmin');<br>
                                        Route::get('install/finish', [InstallController::class,
                                        'finish'])->name('installFinish');
                                    </b>
                                </div>
                                <div class="text-center">
                                    <a class="btn btn-primary btn-user w-100"
                                        href="{{ route('login') }}">{{ __('Login Admin') }}</a>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>

    </div>
@endsection
