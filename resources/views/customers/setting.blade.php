<x-layouts.cpanel.settings.app-layout title="Customers">

    <div class="card-header py-3">
        <h5 class="m-0 fw-bold text-primary">{{ __('customer.header.export') }}</h5>

    </div>

    <div class="row">
        <div class="col-lg-12">
            <form action="{{ route('customers.export') }}" method="POST">
                @csrf
            <div class="card-body col-lg-8">

                <div class="row align-items-center">
                    <div class="col-md-12 mb-3">
                        <label for="server"><strong>{{ __('customer.label.server') }}</strong></label>

                        <select name="servers" class="form-control" id="server-dropdown">
                            <option value="">Select Server</option>
                            @foreach ($mikrotiks as $id => $name)
                                <option value="{{ $id }}">{{ $name }}</option>
                            @endforeach
                        </select>

                        @if ($errors->has('servers'))
                            <span class="text-danger">{{ $errors->first('servers') }}</span>
                        @endif
                    </div>
                </div>
            </div>
            <div class="card-footer">
                <button type="submit" class="btn btn-success"><i class="fas fa-arrow-alt-circle-right"></i> &nbsp
                    {{ __('customer.button.export') }}</button>
            </div>
            </form>
        </div>
    </div>

    <div class="card-header py-3">
        <h5 class="m-0 fw-bold text-primary">{{ __('customer.header.activation-all') }}</h5>

    </div>

    <div class="row">
        <div class="col-lg-12">
            <form action="{{ route('customers.all.activation') }}" method="POST">
                @csrf
                @method('PATCH')
            <div class="card-body col-lg-8">
                <div class="row align-items-center">
                    <div class="col-md-12 mb-3">
                        <label for="server"><strong>{{ __('customer.label.server') }}</strong></label>

                        <select name="servers_activation" class="form-control" id="server-dropdown">
                            <option value="">Select Server</option>
                            @foreach ($mikrotiks as $id => $name)
                                <option value="{{ $id }}">{{ $name }}</option>
                            @endforeach
                        </select>

                        @if ($errors->has('servers_activation'))
                            <span class="text-danger">{{ $errors->first('servers_activation') }}</span>
                        @endif

                    </div>
                </div>
            </div>
            <div class="card-footer">
                <button type="submit" class="btn btn-success"><i class="fas fa-arrow-alt-circle-right"></i> &nbsp
                    {{ __('customer.button.activation-all') }}</button>
            </div>
            </form>
        </div>
    </div>
</x-layouts.cpanel.settings.app-layout>
