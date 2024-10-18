@extends('layouts.isolir')
@section('content')
<!-- Outer Row -->
<div class="row justify-content-center">
    <div class="col-xl-10 col-lg-12 col-md-9">
        <div class="card o-hidden border-0 shadow-lg my-5">
            <div class="card-body p-0">
                <!-- Nested Row within Card Body -->
                <div class="col-lg-6 d-none d-lg-block bg-isolir-image">
                </div>
                <div class="row">
                    <div class="col-lg-13">
                        <div class="p-5">
                            <div class="text-center">
                                <h1 class="h1 mb-1"><span class="badge rounded-pill bg-danger text-white">{{ __('Akun anda terisolir!') }}</span></h1>
                            </div>
                            <div class="card-body">
                                <div class="py-3 text-center">
                                    <strong>{{ __('Halo Shanum Network Fiber,') }}</strong>
                                </div>
                                <div class="card-body col-lg-12 text-center">
                                    <span>
                                        Mohon maaf. Dengan berat hati kami sampaikan bahwa akun
                                        anda saat ini sedang terisolir otomatis oleh system kami.
                                        Untuk dapat kembali menggunakan layanan internet kami,
                                        silahkan lakukan pembayaran paket berlangganan internet anda.
                                        <br><br>
                                        Pembayaran dapat dilakukan dengan cara:<br>
                                        1. Cash/Tunai.<br>
                                        2. Transfer ke rekening kami<br>
                                        - Bank Mandiri 1730012695905<br>
                                        - Bank BCA 2990407315<br>
                                        - DANA 085795909904<br>
                                        atas nama Dian Ardiansyah<br><br>

                                        harap konfirmasi ke nomor 087818040024 setelah
                                        melakukan pembayaran dengan mengirimkan bukti transfer.

                                        Jika anda sudah melakukan pembayaran, dan muncul halaman ini
                                        silahkan hubungi kami melalui tombol "Hubungi Kami"
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection