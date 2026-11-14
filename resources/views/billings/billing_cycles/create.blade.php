<x-layouts.app-layout title="Crate Package">
    <div class="card-header py-3">
        <h5 class="m-0 fw-bold text-primary"> {{ __('package.header.create') }}</h5>
    </div>

    <div class="row">
        <div class="col-lg-12">

            <form action="{{ route('packets.store') }}" method="POST">
                @csrf

            <div class="card-body col-lg-8">

                <div class="row align-items-center">
                    <div class="col-md-12 mb-3">
                        <label for="name"><strong>{{ __('package.label.name') }}</strong></label>
                        <div class="input-group">
                            <input type="text" name="name" class="form-control" placeholder="{{ __('package.placeholder.name') }}" value="{{ old('name') }}">
                        </div>
                        @if ($errors->has('name'))
                            <span class="text-danger">{{ $errors->first('name') }}</span>
                        @endif
                    </div>
                </div>

                <div class="row align-items-center">
                    <div class="col-md-12 mb-3">
                        <label for="profile"><strong>{{ __('package.label.profile') }}</strong></label>
                        <div class="input-group">
                            <select id="profile-dropdown" name="profile" class="form-control">
                                <option value="">Select Profile</option>
                                @foreach ($ppp_profiles as $profile)
                                    <option value="{{ $profile->profile_name }}">{{ $profile->profile_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        @if ($errors->has('profile'))
                            <span class="text-danger">{{ $errors->first('profile') }}</span>
                        @endif
                    </div>
                </div>

                <div class="row align-items-center">
                    <div class="col-md-12 mb-3">
                        <label for="price"><strong>{{ __('package.label.price') }}</strong></label>
                        <div class="input-group">
                            <input type="text" name="price" class="form-control" placeholder="{{ __('package.placeholder.price') }}" value="{{ old('price') }}">
                        </div>
                        @if ($errors->has('price'))
                            <span class="text-danger">{{ $errors->first('price') }}</span>
                        @endif
                    </div>
                </div>

                <div class="row align-items-center">
                    <div class="col-md-12 mb-3">
                        <label for="description"><strong>{{ __('package.label.description') }}</strong></label>
                        <div class="input-group">
                            <input type="text" name="description" class="form-control" placeholder="{{ __('package.placeholder.description') }}" value="{{ old('description') }}">
                        </div>
                        @if ($errors->has('mikrotik_id'))
                            <span class="text-danger">{{ $errors->first('mikrotik_id') }}</span>
                        @endif
                    </div>
                </div>
            </div>
            <div class="card-footer">
                <button type="submit" class="btn btn-sm btn-success"><i class="fa fa-save"></i> &nbsp
                    {{ __('package.button.save') }}</button>
            </div>
            </form>
        </div>
    </div>

    {{-- @section('custom_scripts')
        <script type="text/javascript">
            $(document).ready(function() {

                /*==================================================
                    Server Dropdown Change Event
                ====================================================*/
                $('#server-dropdown').on('change', function() {
                    var idServer = this.value;
                    $("#profile-dropdown").html('');
                    $.ajax({

                        url: "{{ url('api/fetch-profiles') }}",
                        type: "POST",
                        data: {
                            mikrotik_id: idServer,
                            _token: '{{ csrf_token() }}'
                        },
                        dataType: 'json',
                        success: function(result_server) {
                            $('#profile-dropdown').html('<option value="">Select Profile</option>');
                            $.each(result_server, function(key, value) {
                                $("#profile-dropdown").append('<option value="' + value
                                    .name + '">' + value.name + '</option>');
                            });
                        }

                    });

                });



            });
        </script>
    @endsection --}}
</x-layouts.app-layout>
