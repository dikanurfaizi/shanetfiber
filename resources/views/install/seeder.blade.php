@extends('layouts.guest')

@section('content')
    <div class="row justify-content-center align-items-center min-vh-100">
        <div class="col-xl-6 col-lg-8 col-md-9">
            <div class="card border-0 shadow-lg my-5"
                style="background: rgba(15, 23, 42, 0.75); backdrop-filter: blur(16px); -webkit-backdrop-filter: blur(16px); border-radius: 1rem; border: 1px solid rgba(255, 255, 255, 0.1);">
                <div class="card-body p-5 text-white">
                    <div class="text-center mb-4">
                        <h1 class="h4 font-weight-bold mb-3" style="color: #f8fafc;">{{ __('install.title.primary') }}</h1>
                    </div>

                    <div class="mb-4 text-center" style="font-size: 0.95rem; line-height: 1.6; color: #cbd5e1;">
                        @if ($status === 'true')
                            {!! __('install.db.success', ['db' => $dbName]) !!}
                        @else
                            {!! __('install.db.error', ['db' => $dbName]) !!}
                        @endif
                    </div>

                    @if ($status === 'true')
                        <form action="{{ route('installCreateSeeder') }}" method="GET">
                            <div class="text-center">
                                <button type="submit"
                                    class="btn btn-primary btn-user w-100 py-2 font-weight-bold shadow-sm"
                                    style="background-color: #2563eb; border: none; border-radius: 0.5rem;">
                                    {{ __('install.button.continue') }}
                                </button>
                            </div>
                        </form>
                    @else
                        <form action="{{ route('install') }}" method="GET">
                            <div class="text-center">
                                <button type="submit" class="btn btn-danger btn-user w-100 py-2 font-weight-bold shadow-sm"
                                    style="border-radius: 0.5rem;">
                                    {{ __('install.button.restart') }}
                                </button>
                            </div>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
