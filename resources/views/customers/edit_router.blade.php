<x-layouts.cpanel.customers.app-layout title="Edit Router">

    <div class="card-header py-3">
        <h5 class="m-0 fw-bold text-primary"> {{ __('customer.header.edit-router') }} -
            {{ $customer->name ?? 'Customer Not Found' }}
        </h5>
    </div>

    <div class="row">
        <div class="col-lg-12">
            <form action="{{ route('customers.update.router', $customer->slug ?? '') }}" method="POST">
                @csrf
                @method('PATCH')

            <div class="card-body col-lg-8">
                <div class="row align-items-center">
                    <div class="col-md-12 mb-3">
                        <label for="merk_ont"><strong>{{ __('customer.label.merk-router') }}</strong></label>
                        <select id="merk-ont-dropdown" name="merk_ont" class="form-control">
                            <option value="">{{ __('customer.placeholder.select-merk-router') }}</option>
                            @forelse ($merkRouters ?? [] as $merkRouter)
                                <option value="{{ $merkRouter->name }}" @selected(($customerRouter->merk_ont ?? null) == $merkRouter->name)>
                                    {{ $merkRouter->name }}
                                </option>
                            @empty
                                <option value="" disabled>{{ __('customer.message.no-router-available') }}
                                </option>
                            @endforelse
                        </select>
                        @if ($errors->has('merk_ont'))
                            <span class="text-danger">{{ $errors->first('merk_ont') }}</span>
                        @endif
                    </div>
                </div>

                <div class="row align-items-center">
                    <div class="col-md-12 mb-3">
                        <label for="type_ont"><strong>{{ __('customer.label.type-router') }}</strong></label>
                        <select id="type-ont-dropdown" name="type_ont" class="form-control dynamic"
                            data-dependent="details">
                            <option value="">{{ __('customer.placeholder.select-type-router') }}</option>
                            @forelse ($typeRouters ?? [] as $typeRouter)
                                <option value="{{ $typeRouter->name }}" @selected(($customerRouter->type_ont ?? null) == $typeRouter->name)>
                                    {{ $typeRouter->name }}
                                </option>
                            @empty
                                <option value="" disabled>{{ __('customer.placeholder.no-type-available') }}
                                </option>
                            @endforelse
                        </select>
                        @if ($errors->has('type_ont'))
                            <span class="text-danger">{{ $errors->first('type_ont') }}</span>
                        @endif
                    </div>
                </div>

                <!-- Form fields lainnya -->
                <div class="row align-items-center">
                    <div class="col-md-12 mb-3">
                        <label for="address"><strong>{{ __('customer.label.username-router') }}</strong></label>
                        <input type="text" name="username" class="form-control" placeholder="{{ __('customer.placeholder.username-router') }}" value="{{ old('username', $customerRouter->username ?? '') }}">
                        @if ($errors->has('username'))
                            <span class="text-danger">{{ $errors->first('username') }}</span>
                        @endif
                    </div>
                </div>

                <div class="row align-items-center">
                    <div class="col-md-12 mb-3">
                        <label for="password"><strong>{{ __('customer.label.password-router') }}</strong></label>
                        <input type="text" name="password" class="form-control" placeholder="{{ __('customer.placeholder.password-router') }}" value="{{ old('password', $customerRouter->password ?? '') }}">
                        @if ($errors->has('password'))
                            <span class="text-danger">{{ $errors->first('password') }}</span>
                        @endif
                    </div>
                </div>

                <div class="row align-items-center">
                    <div class="col-md-12 mb-3">
                        <label for="port"><strong>{{ __('customer.label.port-router') }}</strong></label>
                        <input type="text" name="port" class="form-control" placeholder="{{ __('customer.placeholder.port-router') }}" value="{{ old('port', $customerRouter->port ?? '') }}">
                        @if ($errors->has('port'))
                            <span class="text-danger">{{ $errors->first('port') }}</span>
                        @endif
                    </div>
                </div>

                <div class="row align-items-center">
                    <div class="col-md-12 mb-3">
                        <label for="mac_address"><strong>{{ __('customer.label.mac-address') }}</strong></label>
                        <input type="text" name="mac_address" class="form-control" placeholder="{{ __('customer.placeholder.mac-address') }}" value="{{ old('mac_address', $customerRouter->mac_address ?? '') }}">
                        @if ($errors->has('mac_address'))
                            <span class="text-danger">{{ $errors->first('mac_address') }}</span>
                        @endif
                    </div>
                </div>
                <div class="row align-items-center">
                    <div class="col-md-12 mb-3">
                        <label for="description"><strong>{{ __('customer.label.description') }}</strong></label>
                        <input type="text" name="description" class="form-control" placeholder="{{ __('customer.placeholder.description') }}" value="{{ old('description', $customerRouter->description ?? '') }}">
                        @if ($errors->has('description'))
                            <span class="text-danger">{{ $errors->first('description') }}</span>
                        @endif
                    </div>
                </div>
                <div class="row align-items-center">
                    <div class="col-md-12 mb-3">
                        <label for="vlanId"><strong>{{ __('customer.label.vlan_id') }}</strong></label>
                        <input type="number" name="vlan_id" class="form-control" placeholder="{{ __('customer.placeholder.vlan_id') }}" min="1" max="9999" value="{{ old('vlan_id', $customerRouter->vlan_id ?? '') }}">
                        @if ($errors->has('vlanId'))
                            <span class="text-danger">{{ $errors->first('vlanId') }}</span>
                        @endif
                    </div>
                </div>

            </div>

            <div class="card-footer">
                <button type="submit" class="btn btn-sm btn-success">
                    <i class="fas fa-user-check"></i>&nbsp{{ __('customer.button.update-router') }}
                </button>
                <a class="btn btn-sm btn-secondary" href="{{ route('customers.show', $customer->slug ?? '#') }}">
                    <i class="fa fa-arrow-left"></i>&nbsp{{ __('customer.button.back') }}
                </a>
                @can('customer-edit')
                    <a class="btn btn-sm btn-primary float-right" href="{{ route('routers.create') }}">
                        <i class="fa fa-plus"></i>&nbsp{{ __('customer.button.create-router') }}
                    </a>
                @endcan
            </div>

            </form>
        </div>
    </div>

    @section('custom_scripts')
        <script type="text/javascript">
            $(document).ready(function() {
                /*==================================================
                    Type Router Dropdown Change Event
                ====================================================*/
                $('#merk-ont-dropdown').on('change', function() {
                    var merkOnt = this.value;
                    $("#type-ont-dropdown").html(
                        '<option value="">{{ __('customer.placeholder.select-type-router') }}</option>');

                    if (merkOnt) {
                        $.ajax({
                            url: "{{ url('api/fetch-type-routers') }}",
                            type: "POST",
                            data: {
                                merk_ont: merkOnt,
                                _token: '{{ csrf_token() }}'
                            },
                            dataType: 'json',
                            success: function(result_type_ont) {
                                if (result_type_ont.length > 0) {
                                    $.each(result_type_ont, function(key, value) {
                                        $("#type-ont-dropdown").append('<option value="' +
                                            value.name + '">' + value.name + '</option>'
                                        );
                                    });
                                } else {
                                    $("#type-ont-dropdown").append(
                                        '<option value="" disabled>{{ __('customer.placeholder.no-type-available') }}</option>'
                                    );
                                }
                            },
                            error: function(xhr, status, error) {
                                console.error("Error fetching router types:", error);
                                $("#type-ont-dropdown").append(
                                    '<option value="" disabled>{{ __('customer.message.error-loading-types') }}</option>'
                                );
                            }
                        });
                    }
                });
            });
        </script>
    @endsection

</x-layouts.cpanel.customers.app-layout>
