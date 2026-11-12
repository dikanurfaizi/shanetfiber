<x-layouts.app-layout title="Dashboard">

    <div class="card-header py-3">
        <h5 class="m-0 fw-bold text-primary">Edit Profile</h5>
    </div>

    <div class="row">
        <div class="col-lg-12">
            <form action="{{ route('profile.update') }}" method="POST">
                @csrf
                @method('PUT')
                <div class="card-body col-lg-8">

                    @if ($message = Session::get('success'))
                        <div class="alert alert-primary alert-dismissable">
                            <button type="button" class="close" data-bs-dismiss="alert" aria-hidden="true">×</button>
                            {{ $message }}
                        </div>
                    @endif

                    <div class="input-group mb-3">
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                            placeholder="{{ __('Name') }}" value="{{ old('name', auth()->user()->name) }}" required>
                        <div class="input-group-append">
                            <div class="input-group-text">
                                <span class="fas fa-user"></span>
                            </div>
                        </div>
                    </div>
                    @error('name')
                        <div class="text-danger small mt-1">
                            <label class="">{{ $message }}</label>
                        </div>
                    @enderror

                    <div class="input-group mb-3">
                        <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                            placeholder="{{ __('Email') }}" value="{{ old('email', auth()->user()->email) }}"
                            required>
                        <div class="input-group-append">
                            <div class="input-group-text">
                                <span class="fas fa-envelope"></span>
                            </div>
                        </div>
                    </div>
                    @error('email')
                        <div class="text-danger small mt-1">
                            <label class="">{{ $message }}</label>
                        </div>
                    @enderror

                    <div class="input-group mb-3">
                        <input type="password" name="password"
                            class="form-control @error('password') is-invalid @enderror"
                            placeholder="{{ __('New password') }}">
                        <div class="input-group-append">
                            <div class="input-group-text">
                                <span class="fas fa-lock"></span>
                            </div>
                        </div>
                    </div>
                    @error('password')
                        <div class="text-danger small mt-1">
                            <label class="">{{ $message }}</label>
                        </div>
                    @enderror

                    <div class="input-group mb-3">
                        <input type="password" name="password_confirmation"
                            class="form-control @error('password_confirmation') is-invalid @enderror"
                            placeholder="{{ __('New password confirmation') }}" autocomplete="new-password">
                        <div class="input-group-append">
                            <div class="input-group-text">
                                <span class="fas fa-lock"></span>
                            </div>
                        </div>
                    </div>

                </div>

                <div class="card-footer">
                    <button type="submit" class="btn btn-primary">{{ __('Submit') }}</button>
                </div>
            </form>
        </div>
    </div>

</x-layouts.app-layout>
