<x-layouts.app-layout title="Create PPP Profile">
    <div class="card-header py-3">
        <h5 class="m-0 font-weight-bold text-primary">{{ __('paket.profile.header.create') }}</h5>
    </div>

    <div class="row">
        <div class="col-lg-12">
            {!! Form::open(['route' => 'ppp-profiles.store', 'method' => 'POST']) !!}

            <div class="card-body col-lg-8">
                @include('packets.profiles.partials.general')
                @include('packets.profiles.partials.limits')
                @include('packets.profiles.partials.queue')
                @include('packets.profiles.partials.advanced')
                @include('packets.profiles.partials.scripts')
                @include('packets.profiles.partials.comment-rate')
            </div>

            <div class="card-footer">
                <button type="submit" class="btn btn-sm btn-success">
                    <i class="fa fa-save"></i> &nbsp {{ __('paket.profile.button.save') }}
                </button>
                <a href="{{ route('ppp-profiles.index') }}" class="btn btn-sm btn-secondary">
                    <i class="fa fa-arrow-left"></i> &nbsp {{ __('paket.profile.button.back') }}
                </a>
            </div>
            {!! Form::close() !!}
        </div>
    </div>

    @section('custom_scripts')
        <script type="text/javascript">
            $(document).ready(function() {
                /*==================================================
                    Server Dropdown Change Event
                ====================================================*/
                $('#server-dropdown').on('change', function() {
                    var idServer = this.value;
                    $("#parent-queue-dropdown").html('');
                    $("#queue-type-dropdown").html('');
                    $("#insert-before-dropdown").html('');


                    $.ajax({
                        url: "{{ url('api/fetch-parent-queues') }}",
                        type: "POST",
                        data: {
                            mikrotik_id: idServer,
                            _token: '{{ csrf_token() }}'
                        },
                        dataType: 'json',
                        success: function(result_server) {
                            $('#parent-queue-dropdown').html('<option value="none">None</option>');
                            $.each(result_server, function(key, value) {
                                $("#parent-queue-dropdown").append('<option value="' + value
                                    .name + '">' + value.name + '</option>');
                            });
                            $('#insert-before-dropdown').html(
                                '<option value="default">Default</option><option value="bottom">Bottom</option><option value="first">First</option>'
                            );
                            $.each(result_server, function(key, value) {
                                $("#insert-before-dropdown").append('<option value="' +
                                    value.name + '">' + value.name + '</option>');
                            });
                        }
                    });


                    setTimeout(() => {
                        $.ajax({
                            url: "{{ url('api/fetch-queue-types') }}",
                            type: "POST",
                            data: {
                                mikrotik_id: idServer,
                                _token: '{{ csrf_token() }}'
                            },
                            dataType: 'json',
                            success: function(result_server) {
                                $('#queue-type-dropdown').html(
                                    '<option value="none">None</option>');
                                $.each(result_server, function(key, value) {
                                    $("#queue-type-dropdown").append(
                                        '<option value="' + value.name + '">' +
                                        value.name + '</option>');
                                });
                            }
                        });
                    }, 250)
                });
            });

            function calculateRateLimit() {
                let uploadInput = $('input[name="max_upload"]').val().trim();
                let downloadInput = $('input[name="max_download"]').val().trim();
                let priority = $('select[name="priority"]').val();
                let rasioCir = parseFloat($('input[name="rasio_cir"]').val()) || 1;

                // Ekstrak angka dan unit
                let uploadValue = parseInt(uploadInput.replace(/[^0-9]/g, '')) || 0;
                let uploadUnit = uploadInput.replace(/[^a-zA-Z]/g, '').toLowerCase();

                let downloadValue = parseInt(downloadInput.replace(/[^0-9]/g, '')) || 0;
                let downloadUnit = downloadInput.replace(/[^a-zA-Z]/g, '').toLowerCase();

                // Normalisasi satuan
                if (uploadUnit === 'k') {
                    // Jika unitnya K, ubah ke M
                    uploadValue = uploadValue / 1000; // Konversi ke Mbps
                    uploadUnit = 'm'; // Ubah jadi M
                } else if (!uploadUnit || uploadUnit === '') {
                    // Jika tidak ada unit, anggap itu Kbps dan ubah ke M
                    uploadValue = uploadValue / 1000; // Anggap angka tersebut dalam Kbps, ubah jadi Mbps
                    uploadUnit = 'm'; // Tambahkan unit M
                }

                if (downloadUnit === 'k') {
                    // Jika unitnya K, ubah ke M
                    downloadValue = downloadValue / 1000; // Konversi ke Mbps
                    downloadUnit = 'm'; // Ubah jadi M
                } else if (!downloadUnit || downloadUnit === '') {
                    // Jika tidak ada unit, anggap itu Kbps dan ubah ke M
                    downloadValue = downloadValue / 1000; // Anggap angka tersebut dalam Kbps, ubah jadi Mbps
                    downloadUnit = 'm'; // Tambahkan unit M
                }

                // Menambahkan unit M untuk upload dan download
                let maxUpload = `${uploadValue}M`;
                let maxDownload = `${downloadValue}M`;

                // Hitung limit-at
                let limitAtUpload = `${Math.floor(uploadValue / rasioCir)}M`;
                let limitAtDownload = `${Math.floor(downloadValue / rasioCir)}M`;

                if (uploadValue && downloadValue && priority) {
                    let rateLimit = `${maxUpload}/${maxDownload}`;
                    $('#rate-limit').val(rateLimit);
                }
            }

            $(document).on('input', 'input[name="max_upload"], input[name="max_download"], input[name="rasio_cir"]',
                calculateRateLimit);
            $(document).on('change', 'select[name="priority"]', calculateRateLimit);
        </script>
    @endsection

</x-layouts.app-layout>
