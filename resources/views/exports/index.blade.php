<x-layouts.cpanel.settings.app-layout title="Exports">

    <div class="card-header py-3">
        <h5 class="m-0 fw-bold text-primary">{{ __('export.title.export') }}</h5>
        <span class="text-danger">{{ __('export.message.warning') }}</span>
    </div>

    <div class="row">
        <div class="col-lg-12">
            <div class="card-body col-lg-8">

                {!! Form::open(['route' => 'export.post', 'method' => 'POST', 'id' => 'export-form']) !!}

                <div class="row align-items-center">
                    <div class="col-md-12 mb-3">
                        <label for="source-server"><strong>{{ __('export.label.source_server') }}</strong></label>
                        {!! Form::select('source_server', $mikrotiks, null, [
                            'placeholder' => __('export.placeholder.select_source_server'),
                            'class' => 'form-control',
                            'id' => 'source-server',
                        ]) !!}
                        <span id="source-server-error" class="text-danger"></span>
                        @if ($errors->has('source_server'))
                            <span class="text-danger">{{ $errors->first('source_server') }}</span>
                        @endif
                    </div>
                </div>

                <div class="row align-items-center">
                    <div class="col-md-12 mb-3">
                        <label
                            for="destination-server"><strong>{{ __('export.label.destination_server') }}</strong></label>
                        {!! Form::select('destination_server', $mikrotiks, null, [
                            'placeholder' => __('export.placeholder.select_destination_server'),
                            'class' => 'form-control',
                            'id' => 'destination-server',
                        ]) !!}
                        <span id="destination-server-error" class="text-danger"></span>
                        @if ($errors->has('destination_server'))
                            <span class="text-danger">{{ $errors->first('destination_server') }}</span>
                        @endif
                    </div>
                </div>

                <div class="card-footer px-0 bg-transparent border-0 d-flex gap-2">
                    <button type="submit" class="btn btn-success" id="export-button" disabled>
                        <i class="fas fa-arrow-alt-circle-right"></i> &nbsp;
                        {{ __('export.button.export_customer_data') }}
                    </button>
                </div>

                {!! Form::close() !!}

                <hr class="my-4">

                <!-- AKSI BARU: FORM UNTUK UPDATE/SYNC COMMENT DENGAN PILIHAN SERVER TARGET -->
                {!! Form::open(['route' => 'export.sync.comment', 'method' => 'POST', 'id' => 'sync-comment-form']) !!}
                <div class="card-body px-0 py-2">
                    <h5 class="fw-bold text-primary">{{ __('export.title.comment') }}</h5>
                    <p class="text-muted small">{{ __('export.message.comment') }}</p>

                    <div class="row align-items-center">
                        <div class="col-md-12 mb-3">
                            <label
                                for="sync-target-server"><strong>{{ __('export.label.target_server') }}</strong></label>
                            {!! Form::select('mikrotik_id', $mikrotiks, null, [
                                'placeholder' => __('export.placeholder.select_target_server'),
                                'class' => 'form-control',
                                'id' => 'sync-target-server',
                            ]) !!}
                            <span id="sync-target-error" class="text-danger"></span>
                            @if ($errors->has('mikrotik_id'))
                                <span class="text-danger">{{ $errors->first('mikrotik_id') }}</span>
                            @endif
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary" id="sync-comment-button" disabled>
                        <i class="fas fa-sync-alt"></i> &nbsp; {{ __('export.button.export_comment_data') }}
                    </button>
                </div>
                {!! Form::close() !!}

            </div>
        </div>
    </div>

    @section('custom_scripts')
        <script>
            $(document).ready(function() {
                // 1. Validasi Form Export
                function validateExportSelection() {
                    const source = $('#source-server').val();
                    const destination = $('#destination-server').val();
                    let isValid = true;

                    if (!source) {
                        // Dicetak menggunakan Blade ke dalam string JavaScript
                        $('#source-server-error').text("{{ __('export.message.select_source_server') }}");
                        isValid = false;
                    } else {
                        $('#source-server-error').text('');
                    }

                    if (!destination) {
                        $('#destination-server-error').text("{{ __('export.message.select_destination_server') }}");
                        isValid = false;
                    } else {
                        $('#destination-server-error').text('');
                    }

                    $('#export-button').prop('disabled', !isValid);
                }

                $('#source-server, #destination-server').on('change', validateExportSelection);

                // 2. Validasi Form Sync Comment (Target Server)
                function validateSyncSelection() {
                    const target = $('#sync-target-server').val();
                    let isValid = true;

                    if (!target) {
                        $('#sync-target-error').text("{{ __('export.message.select_target_server') }}");
                        isValid = false;
                    } else {
                        $('#sync-target-error').text('');
                    }

                    $('#sync-comment-button').prop('disabled', !isValid);
                }

                $('#sync-target-server').on('change', validateSyncSelection);

                // Efek loading saat tombol sync comment ditekan
                $('#sync-comment-form').on('submit', function() {
                    const $btn = $('#sync-comment-button');
                    $btn.prop('disabled', true);
                    $btn.html('<i class="fas fa-spinner fa-spin"></i> &nbsp; ' +
                        "{{ __('export.message.processing_sync') }}");
                });
            });
        </script>
    @endsection

</x-layouts.cpanel.settings.app-layout>
