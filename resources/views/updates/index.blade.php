@extends('layouts.guest')

@section('content')
<div class="row justify-content-center">
    <div class="col-xl-10 col-lg-12 col-md-9">
        <div class="card o-hidden border-0 shadow-lg my-5">
            <div class="card-body p-0">
                <div class="col-lg-12">
                    <div class="p-5">
                        <div class="text-center mb-4">
                            <h1 class="h4 text-gray-900">{{ __('Customer Management') }}</h1> developer by GriyaNet
                        </div>
                        <div class="form-group">
                            <span>Selamat datang kawan,
                                Kelola customer mikrotik pake Customer Management jadi lebih mudah.<br>
                                Source code ini gratis ya dan dilarang diperjual belikan. Jika kamu menyukai Customer Management ini, jangan lupa bantu chanel youtube <a href="https://www.youtube.com/@griya-net" target="_blank"><i class="fab fa-youtube" style="color:red"></i> @griya-net</a> untuk berkembang ya..<br>
                                Hanya dengan 1 subscribe kamu akan menambah semangatku...<br></span>
                            <span class="text-primary mt-4"><strong>Apa yang baru di versi ini?</strong></span><br>
                            1. Penambahan Fitur IP Static<br>
                            2. Penambahan fitur lupa password<br>
                            Atau kalo mau ngasih kopi bisa lewat <a href="https://www.nihbuatjajan.com/griyanet" target="_blank"><img src="/images/nih_buat_jajan.png" title="Nih Buat Jajan" alt="Nih Buat Jajan" width="150" height="50"></a> ya...
                            <br><br>
                            Subscribe <a href="https://www.youtube.com/@griya-net" target="_blank"><i class="fab fa-youtube" style="color:red"></i> @griya-net</a> dulu yuk..
                            <br><br>
                        </div>
                    </div>
                </div>
                <div class="card-header py-3">
                    @if ($version != $newVersion)
                    <h5 class="m-0 font-weight-bold text-primary">{{ __('Update') }}</h5>
                    @else
                    <h5 class="m-0 font-weight-bold text-primary">{{ __('Latest Version') }}</h5>
                    @endif
                </div>

                <div class="card-body">
                    @if ($version != $newVersion)
                    <table id="serverTable" cellspacing="0" width="100%" class="table no-footer">
                        <tr class="table-borderless">
                            <td width="150px"><strong>{{ __('websystem.version') }}</strong></td>
                            <td width="380px">{{ __(':') }} {{ $version }}</td>
                            <td width="150px"><strong>{{ __('websystem.new-version') }}</strong></td>
                            <td width="380px">{{ __(':') }} {{ $newVersion }}</td>
                        </tr>
                    </table>
                    <a class="btn btn-sm btn-success mt-2" href="{{ route('settings.update.start') }}"><i class="fas fa-upload"></i> {{ __('websystem.button.update-new-version', ['version' => $newVersion]) }}</a>
                    @else
                    <table id="serverTable" cellspacing="0" width="100%" class="table no-footer">
                        <tr class="table-borderless">
                            <td width="150px"><strong>{{ __('websystem.version') }}</strong></td>
                            <td width="380px">{{ __(':') }} {{ $version }}</td>
                        </tr>
                    </table>

                    <a class="btn btn-sm btn-success mt-2" href="{{ route('home') }}"><i class="fas fa-arrow-left"></i> {{ __('websystem.button.cancel', ['version' => $newVersion]) }}</a>
                    <a class="btn btn-sm btn-danger mt-2" href="{{ route('settings.update.optimize') }}"><i class="fas fa-sync-alt"></i> {{ __('Optimize') }}</a>
                    @endif
                </div>
            </div>
        </div>

    </div>

</div>
@endsection