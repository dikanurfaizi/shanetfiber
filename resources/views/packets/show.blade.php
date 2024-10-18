<x-layouts.app-layout title="Packet Show">

    <div class="card-header py-3">
        <h5 class="m-0 font-weight-bold text-primary">{{ __('Paket Detail') }}</h5>
    </div>

    <div class="card-body col-lg-8">
        <div class="col-xs-12 col-sm-12 col-md-12">
            <div class="form-group">
                <strong>Name:</strong>
                {{ $packet->name ?? 'no data' }}
            </div>
        </div>
        <div class="col-xs-12 col-sm-12 col-md-12">
            <div class="form-group">
                <strong>Price:</strong>
                @moneyIDR($packet->price ?? '0')
            </div>
        </div>
        <div class="col-xs-12 col-sm-12 col-md-12">
            <div class="form-group">
                <strong>Location on server:</strong>
                <span class="badge bg-danger-soft text-danger">
                    <i class="fas fa-id-card-alt"></i> {{ $packet->mikrotik->name ?? 'no data' }}
                </span>

            </div>
        </div>
        <div class="col-xs-12 col-sm-12 col-md-12">
            <div class="form-group">
                <strong>Profile:</strong>
                {{ $packet->profile ?? 'no data' }}
            </div>
        </div>

        <div class="col-xs-12 col-sm-12 col-md-12">
            <div class="form-group">
                <strong>Description:</strong>
                {{ $packet->description ?? 'no data' }}
            </div>
        </div>
        <div class="col-xs-12 col-sm-12 col-md-12">
            <div class="form-group">
                <strong>Create at:</strong>
                {{ $packet->created_at ?? 'no data' }}
            </div>
        </div>
        <div class="col-xs-12 col-sm-12 col-md-12 mb-3">
            <div class="form-group">
                <strong>Updated at:</strong>
                {{ $packet->updated_at ?? 'no data' }}
            </div>
        </div>
    </div>
    <div class="card-footer">
        <a class="btn btn-sm btn-success" href="{{ route('packets.edit',$packet->slug) }}"><i class="fa fa-edit"></i> Edit</a>
        <a class="btn btn-sm btn-primary" href="{{ route('packets.index') }}"><i class="fa fa-arrow-left"></i> Back</a>
    </div>

</x-layouts.app-layout>