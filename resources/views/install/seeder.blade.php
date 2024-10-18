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


                            <div class="form-group">
							@if ($status === 'true')
								Database <b class="text-success"> {{ $dbName }} </b> sudah berhasil dibuat.
                                
                                
								@else
									Upss... maaf, saya tidak dapat menemukan database dengan nama <b class="text-danger">{{ $dbName }}</b>. Mungkin saat instalasi awal tadi kami mengalami masalah saat membuat database <b class="text-danger">{{ $dbName }}</b>. Silahkan ulangi dari awal lagi ya...                                
								@endif
                    
					
								<br><br>
                                
                            </div>
							@if ($status === 'true')
                            <form action="{{ route('installCreateSeeder') }}" method="GET">
                                <div class="text-center">   
                                    <a class="btn  btn-primary btn-user btn-block" onclick="event.preventDefault(); this.closest('form').submit();">{{ __('Lanjutkan') }}</a>
                                   
                                </div>
                            </form>
							@else
								<form action="{{ route('install') }}" method="GET">
                                <div class="text-center">   
                                    <a class="btn  btn-primary btn-user btn-block" onclick="event.preventDefault(); this.closest('form').submit();">{{ __('Mulai dari awal') }}</a>
                                   
                                </div>
                            </form>
							@endif


                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>

</div>
@endsection