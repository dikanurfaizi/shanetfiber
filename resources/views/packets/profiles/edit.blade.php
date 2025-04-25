<x-layouts.app-layout title="Edit PPP Profile">
    <div class="card-header py-3">
        <h5 class="m-0 font-weight-bold text-primary">{{ __('paket.profile.header.edit') }}</h5>
    </div>

    <div class="row">
        <div class="col-lg-12">
            {!! Form::model($profile, ['route' => ['ppp-profiles.update', $profile->id], 'method' => 'PUT']) !!}

            <div class="card-body col-lg-8">
                @include('packets.profiles.partials.general')
                @include('packets.profiles.partials.limits')
                @include('packets.profiles.partials.queue')
                @include('packets.profiles.partials.advanced')
                @include('packets.profiles.partials.scripts')
                @include('packets.profiles.partials.comment-rate')
            </div>

            <div class="card-footer">
                <button type="submit" class="btn btn-sm btn-primary">
                    <i class="fa fa-save"></i> &nbsp {{ __('paket.profile.button.update') }}
                </button>
            </div>
            {!! Form::close() !!}
        </div>
    </div>
</x-layouts.app-layout>
