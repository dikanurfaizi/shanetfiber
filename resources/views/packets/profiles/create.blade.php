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
        <script src="{{ asset('js/rate-limit.js') }}"></script>
    @endsection
</x-layouts.app-layout>
