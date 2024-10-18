<x-layouts.cpanel.settings.app-layout title="{{ __('user.title.edit') }}">

    <div class="card-header py-3">
        <h5 class="m-0 font-weight-bold text-primary">{{ __('user.header.edit') }}</h5>
    </div>

    <div class="row">
        <div class="col-lg-12">


            {!! Form::model($user, ['method' => 'PATCH','route' => ['users.update', $user->id]]) !!}
            <div class="card-body col-lg-8">

                <div class="row align-items-center">
                    <div class="col-md-12 mb-2">
                        <label for="name"><strong>{{ __('user.label.name') }}</strong></label>
                        <div class="input-group mb-1">
                            {!! Form::text('name', null, array('placeholder' => __('user.placeholder.name'),'class' => 'form-control')) !!}
                            <div class="input-group-append">
                                <div class="input-group-text">
                                    <span class="fas fa-user"></span>
                                </div>
                            </div>
                        </div>
                        @if ($errors->has('name'))
                        <span class="text-danger">{{ $errors->first('name') }}</span>
                        @endif
                    </div>
                </div>

                <div class="row align-items-center">
                    <div class="col-md-12 mb-2">
                        <label for="email"><strong>{{ __('user.label.email') }}</strong></label>
                        <div class="input-group mb-1">
                            {!! Form::text('email', null, array('placeholder' => __('user.placeholder.email'),'class' => 'form-control')) !!}
                            <div class="input-group-append">
                                <div class="input-group-text">
                                    <span class="fas fa-envelope"></span>
                                </div>
                            </div>
                        </div>
                        @if ($errors->has('email'))
                        <span class="text-danger">{{ $errors->first('email') }}</span>
                        @endif
                    </div>
                </div>

                <div class="row align-items-center">
                    <div class="col-md-12 mb-2">
                        <label for="password"><strong>{{ __('user.label.password') }}</strong></label>
                        <div class="input-group mb-1">
                            {!! Form::password('password', array('placeholder' => __('user.placeholder.password'),'class' => 'form-control')) !!}
                            <div class="input-group-append">
                                <div class="input-group-text">
                                    <span class="fas fa-lock"></span>
                                </div>
                            </div>
                        </div>
                        @if ($errors->has('password'))
                        <span class="text-danger">{{ $errors->first('password') }}</span>
                        @endif
                    </div>
                </div>

                <div class="row align-items-center">
                    <div class="col-md-12 mb-2">
                        <label for="confirm-password"><strong>{{ __('user.label.confirm-password') }}</strong></label>
                        <div class="input-group mb-1">
                            {!! Form::password('confirm-password', array('placeholder' => __('user.placeholder.confirm-password'),'class' => 'form-control')) !!}
                            <div class="input-group-append">
                                <div class="input-group-text">
                                    <span class="fas fa-lock"></span>
                                </div>
                            </div>
                        </div>
                        @if ($errors->has('confirm-password'))
                        <span class="text-danger">{{ $errors->first('confirm-password') }}</span>
                        @endif
                    </div>
                </div>


                <div class="row align-items-center">
                    <div class="col-md-12 mb-2">
                        <label for="roles"><strong>{{ __('user.label.roles') }}</strong></label>
                        <div class="input-group">
                            {!! Form::select('roles[]', $roles,$userRole, array('class' => 'form-control','multiple')) !!}
                        </div>
                        @if ($errors->has('roles'))
                        <span class="text-danger">{{ $errors->first('roles') }}</span>
                        @endif
                    </div>
                </div>
            </div>
            <div class="card-footer">
                <button type="submit" class="btn btn-sm btn-success"><i class="fas fa-user-check"></i> &nbsp {{ __('user.button.update') }}</button>
                <a class="btn btn-sm btn-secondary" href="{{ route('users.index') }}"><i class="fa fa-arrow-left"></i> &nbsp {{ __('user.button.cancel') }}</a>
            </div>

            {!! Form::close() !!}
        </div>
    </div>
</x-layouts.cpanel.settings.app-layout>