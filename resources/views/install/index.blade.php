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
                                Selamat datang kawan,
                                Kelola customer mikrotik pake Customer Management jadi lebih mudah.<br>
                                Source code ini gratis ya dan dilarang diperjual belikan. Jika kamu menyukai Customer Management ini, jangan lupa bantu chanel youtube <a href="https://www.youtube.com/@griya-net" target="_blank"><i class="fab fa-youtube" style="color:red"></i> @griya-net</a> untuk berkembang ya..<br>
                                Hanya dengan 1 subscribe kamu akan menambah semangatku...<br>
                                Atau kalo mau ngasih kopi bisa lewat <a href="https://www.nihbuatjajan.com/griyanet" target="_blank"><img src="/images/nih_buat_jajan.png" title="Nih Buat Jajan" alt="Nih Buat Jajan" width="150" height="50"></a> ya...
                                <br><br>
                                Subscribe <a href="https://www.youtube.com/@griya-net" target="_blank"><i class="fab fa-youtube" style="color:red"></i> @griya-net</a> dulu yuk..
                                <br><br>
                                @if ($status === 'false')
                                Upss... database dengan nama <b class="text-danger">{{ $dbName }}</b> sudah ada di servermu. Jika kamu ingin melanjutkan menginstall, database <b class="text-danger">{{ $dbName }}</b> dapat terhapus secara ludes semua lo.
                                <br><br>
                                @endif

                                @if ($errors->has('setuju'))
                                <div class="text-center">
                                    <h1 class="h4 text-danger mb-4"><b>{{ $errors->first('setuju') }}</b></h1>
                                </div>
                                @endif
                            </div>

                            <form action="{{ route('installStart') }}" method="GET">
                                <input type="checkbox" id="setuju" name="setuju" value="true">
                                <label for="setuju"><b> Saya sudah subscribe <a href="https://www.youtube.com/@griya-net" target="_blank"><i class="fab fa-youtube" style="color:red"></i> @griya-net</a></b></label><br><br>

                                <div class="text-center">
                                    @if ($status === 'true')
                                    <a class="btn  btn-primary btn-user btn-block" onclick="event.preventDefault(); this.closest('form').submit();">{{ __('Install') }}</a>
                                    @else
                                    <a class="btn  btn-danger  btn-user btn-block" onclick="event.preventDefault(); this.closest('form').submit();">{{ __('Gak papa bro lanjut install dan hapus database lamaku!') }}</a>

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
@endsection