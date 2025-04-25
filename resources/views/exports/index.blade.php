<x-layouts.cpanel.settings.app-layout title="Exports">

    <div class="card-header py-3">
        <h5 class="m-0 font-weight-bold text-primary">{{ __('customer.header.export') }}</h5>
        <span
            class="text-danger">{{ __('Warning!! Export customer included create profiles secret on this customer management.') }}</span>
    </div>

    <div class="row">
        <div class="col-lg-12">
            <div class="card-body col-lg-8">

                {!! Form::open(['route' => 'export.post', 'method' => 'POST', 'id' => 'export-form']) !!}

                <div class="row align-items-center">
                    <div class="col-md-12 mb-3">
                        <label for="source-server"><strong>{{ __('customer.label.source-server') }}</strong></label>
                        {!! Form::select('source_server', $mikrotiks, null, [
                            'placeholder' => 'Select Source Server',
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
                            for="destination-server"><strong>{{ __('customer.label.destination-server') }}</strong></label>
                        {!! Form::select('destination_server', $mikrotiks, null, [
                            'placeholder' => 'Select Destination Server',
                            'class' => 'form-control',
                            'id' => 'destination-server',
                        ]) !!}
                        <span id="destination-server-error" class="text-danger"></span>
                        @if ($errors->has('destination_server'))
                            <span class="text-danger">{{ $errors->first('destination_server') }}</span>
                        @endif
                    </div>
                </div>

                <div class="card-footer">
                    <button type="submit" class="btn btn-success" id="export-button" disabled>
                        <i class="fas fa-arrow-alt-circle-right"></i> &nbsp {{ __('customer.button.export') }}
                    </button>
                </div>

                {!! Form::close() !!}

            </div>
        </div>
    </div>

    @section('custom_scripts')
        <script>
            $(document).ready(function() {
                function validateSelection() {
                    const source = $('#source-server').val();
                    const destination = $('#destination-server').val();
                    let isValid = true;

                    if (!source) {
                        $('#source-server-error').text('Please select source Mikrotik.');
                        isValid = false;
                    } else {
                        $('#source-server-error').text('');
                    }

                    if (!destination) {
                        $('#destination-server-error').text('Please select destination Mikrotik.');
                        isValid = false;
                    } else {
                        $('#destination-server-error').text('');
                    }

                    if (source && destination && source === destination) {
                        $('#destination-server-error').text('Source and Destination Mikrotik cannot be the same.');
                        isValid = false;
                    }

                    $('#export-button').prop('disabled', !isValid);
                }

                $('#source-server, #destination-server').on('change', validateSelection);
            });
        </script>
    @endsection

</x-layouts.cpanel.settings.app-layout>
