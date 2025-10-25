<x-layouts.app-layout title="Create PPP Profile">
    <div class="card-header py-3">
        <h5 class="m-0 font-weight-bold text-primary">{{ __('package.profile.header.create') }}</h5>
    </div>

    <div class="row">
        <div class="col-lg-12">
            {!! Form::open(['route' => 'ppp-profiles.store', 'method' => 'POST']) !!}

            <div class="card-body col-lg-8">
                @include('packages.profiles.partials.general')
                @include('packages.profiles.partials.limits')
                @include('packages.profiles.partials.queue')
                @include('packages.profiles.partials.advanced')
                @include('packages.profiles.partials.scripts')
                @include('packages.profiles.partials.comment-rate')
            </div>

            <div class="card-footer">
                <button type="submit" class="btn btn-sm btn-success">
                    <i class="fa fa-save"></i> &nbsp {{ __('package.profile.button.save') }}
                </button>
                <a href="{{ route('ppp-profiles.index') }}" class="btn btn-sm btn-secondary">
                    <i class="fa fa-arrow-left"></i> &nbsp {{ __('package.profile.button.back') }}
                </a>
            </div>
            {!! Form::close() !!}
        </div>
    </div>

    @section('custom_scripts')
        <script src="{{ asset('js/rate-limit.js') }}"></script>
    @endsection
</x-layouts.app-layout>
