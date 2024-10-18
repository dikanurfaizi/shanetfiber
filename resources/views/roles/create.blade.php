<x-layouts.cpanel.settings.app-layout title="Create Role">

    <div class="card-header py-3">
        <h5 class="m-0 font-weight-bold text-primary">{{ __('roles.header.create') }}</h5>
    </div>

    <div class="row">
        <div class="col-lg-12">

            {!! Form::open(array('route' => 'roles.store','method'=>'POST')) !!}
            <div class="card-body col-lg-8">

                <div class="row align-items-center">
                    <div class="col-md-12 mb-3">
                        <label for="name"><strong>{{ __('roles.label.name') }}</strong></label>

                        <div class="input-group">
                            {!! Form::text('name', null, array('placeholder' => 'Name','class' => 'form-control')) !!}
                        </div>
                        @if ($errors->has('name'))
                        <span class="text-danger">{{ $errors->first('name') }}</span>
                        @endif
                    </div>
                </div>


                <div class="row">
                    <label for="permission"><strong>{{ __('roles.label.permissions') }}</strong></label>
                    @if ($errors->has('permission'))
                    <span class="text-danger">{{ $errors->first('permission') }}</span>
                    @endif
                    <div class="input-group">
                        @foreach($permissions as $permission)
                        <div class="col-xl-6 col-md-6 mb-4">

                            <div class="card border-start-lg border-start-primary h-100">
                                <div class="card-body">

                                    <strong>{{ $permission->name }}</strong>
                                    <br>
                                    @foreach($allPermissions->where('parent_id', $permission->id) as $allPermission)

                                    <label>{{ Form::checkbox('permission[]', $allPermission->id, false, array('class' => 'name')) }}
                                        {{ $allPermission->title }}
                                    </label>
                                    <br />
                                    @endforeach
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>

                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-success"><i class="fa fa-save"></i> &nbsp {{ __('roles.button.create') }}</button>
                    <a class="btn btn-primary" href="{{ route('roles.index') }}"><i class="fa fa-arrow-left"></i> &nbsp {{ __('roles.button.back') }}</a>
                </div>

            </div>
        </div>
    </div>

</x-layouts.cpanel.settings.app-layout>