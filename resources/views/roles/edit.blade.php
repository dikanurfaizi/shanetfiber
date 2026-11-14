<x-layouts.cpanel.settings.app-layout title="Edit Role">

    <div class="card-header py-3">
        <h5 class="m-0 fw-bold text-primary">{{ __('roles.header.edit') }}</h5>
    </div>

    <div class="row">
        <div class="col-lg-12">
            <form action="{{ route('roles.update', $role->id) }}" method="POST">
                @csrf
                @method('PATCH')

                <div class="card-body col-lg-8">

                    <div class="row align-items-center">
                        <div class="col-md-12 mb-3">
                            <label for="name"><strong>{{ __('roles.label.name') }}</strong></label>
                            <div class="input-group">
                                <input type="text" name="name" id="name" class="form-control"
                                    placeholder="Name" value="{{ old('name', $role->name ?? '') }}">
                            </div>
                            @error('name')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="row">
                        <label><strong>{{ __('roles.label.permissions') }}</strong></label>
                        <div class="input-group">
                            @foreach ($permissions as $permission)
                                <div class="col-xl-6 col-md-6 mb-4">
                                    <div class="card border-start-lg border-start-primary h-100">
                                        <div class="card-body">
                                            <strong>{{ $permission->name }}</strong>
                                            <br />
                                            @foreach ($allPermissions->where('parent_id', $permission->id) as $allPermission)
                                                <label>
                                                    <input type="checkbox" name="permission[]"
                                                        value="{{ $allPermission->id }}" class="name"
                                                        @checked(in_array($allPermission->id, old('permission', $rolePermissions ?? [])))>
                                                    {{ $allPermission->title }}
                                                </label>
                                                <br />
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            @endforeach

                            @error('permission')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="card-footer">
                        <button type="submit" class="btn btn-success">
                            <i class="fa fa-save"></i> &nbsp; {{ __('roles.button.update') }}
                        </button>
                        <a class="btn btn-secondary" href="{{ route('roles.index') }}">
                            <i class="fa fa-arrow-left"></i> &nbsp; {{ __('roles.button.cancel') }}
                        </a>
                    </div>

                </div>
            </form>
        </div>
    </div>

</x-layouts.cpanel.settings.app-layout>
