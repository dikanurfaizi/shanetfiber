<x-layouts.app-layout title="Edit Paket">

    <div class="card-header py-3">
        <h5 class="m-0 font-weight-bold text-primary"> {{ __('paket.header.edit') }}</h5>
        <span>{{ $packet->name }} on server <span class="badge bg-success text-white">
                <i class="fas fa-server"></i> {{$packet->mikrotik->name}}</span>
        </span>
    </div>

    <div class="row">
        <div class="col-lg-12">

            {!! Form::model($packet, ['method' => 'PATCH','route' => ['packets.update', $packet->slug]]) !!}
            <div class="card-body col-lg-8">

                <div class="row align-items-center">
                    <div class="col-md-12 mb-3">
                        <label for="name"><strong>{{ __('paket.label.name') }}</strong></label>
                        {!! Form::text('name', null, array('placeholder' => __('paket.placeholder.name'),'class' => 'form-control')) !!}
                        @if ($errors->has('name'))
                        <span class="text-danger">{{ $errors->first('name') }}</span>
                        @endif
                    </div>
                </div>

                <div class="row align-items-center">
                    <div class="col-md-12 mb-3">
                        <label for="profile"><strong>{{ __('paket.label.profile') }}</strong></label>

                        <select id="profile-paket-dropdown" name="profile" class="form-control">
                            @foreach ($pppprofiles as $pppprofile)
                            <option value="{{ $pppprofile['name'] ?? 'no data' }}" @selected($pppprofile['name']==$packet->profile)>
                                {{ $pppprofile['name'] ?? 'no data' }}
                            </option>
                            @endforeach
                        </select>

                        @if ($errors->has('profile'))
                        <span class="text-danger">{{ $errors->first('profile') }}</span>
                        @endif
                    </div>
                </div>

                <div class="form-group row mb-0">
                    <div class="col-sm-6 mb-0 mb-sm-0">
                        <label for="max_limit"><strong>{{ __('paket.label.max_limit') }}</strong></label>
                    </div>
                    <div class="col-sm-2">
                        <label for="max_limit"><strong>{{ __('paket.label.cir') }}</strong></label>
                    </div>
                </div>
                <div class="form-group row">
                    <div class="col-sm-2 mb-3 mb-sm-0">
                        <input type="text" id="max-limit-upload" name="max_limit_upload" class="form-control" placeholder="Upload" value="{{$rateLimit['max_limit_upload']}}"></input>
                    </div>
                    <div class="col-sm-2">
                        <input type="text" name="max_limit_download" class="form-control" placeholder="Download" value="{{$rateLimit['max_limit_download']}}"></input>
                    </div>
                    <div class="col-sm-2">
                        <select id="unit" name="unit_max_limit" class="form-control">
                            <option value="mb">Mb</option>
                            <option value="kb" selected>kb</option>
                        </select>
                    </div>
                    @error('max_limit_upload')
                    <div class="form-group custom-control">
                        <label class="">{{ $message }}</label>
                    </div>
                    @enderror
                    @error('max_limit_download')
                    <div class="form-group custom-control">
                        <label class="">{{ $message }}</label>
                    </div>
                    @enderror

                    <div class="col-sm-2">
                        <select name="rasio_cir" class="form-control">
                            <option value="1" @selected($packet->rasio_cir==1)>1:1</option>
                            <option value="2" @selected($packet->rasio_cir==2)>1:2</option>
                            <option value="3" @selected($packet->rasio_cir==3)>1:3</option>
                            <option value="4" @selected($packet->rasio_cir==4)>1:4</option>
                            <option value="5" @selected($packet->rasio_cir==5)>1:5</option>
                            <option value="6" @selected($packet->rasio_cir==6)>1:6</option>
                            <option value="7" @selected($packet->rasio_cir==7)>1:7</option>
                            <option value="8" @selected($packet->rasio_cir==8)>1:8</option>
                        </select>
                    </div>
                </div>
                <div class="row align-items-center">
                    <div class="col-md-12 mb-3">
                        <label for="priority"><strong>{{ __('paket.label.priority') }}</strong></label>
                        <input type="text" name="priority" class="form-control" placeholder="Priority" value="{{$rateLimit['priority']}}"></input>
                        @if ($errors->has('profile'))
                        <span class="text-danger">{{ $errors->first('profile') }}</span>
                        @endif
                    </div>
                </div>




                <div class="row align-items-center">
                    <div class="col-md-12 mb-3">
                        <label for="price"><strong>{{ __('paket.label.price') }}</strong></label>

                        {!! Form::text('price', null, array('placeholder' => __('paket.placeholder.price'),'class' => 'form-control')) !!}

                        @if ($errors->has('price'))
                        <span class="text-danger">{{ $errors->first('price') }}</span>
                        @endif
                    </div>
                </div>

                <div class="row align-items-center">
                    <div class="col-md-12 mb-3">
                        <label for="description"><strong>{{ __('paket.label.description') }}</strong></label>

                        {!! Form::text('description', null, array('placeholder' => __('paket.placeholder.description'),'class' => 'form-control')) !!}

                        @if ($errors->has('description'))
                        <span class="text-danger">{{ $errors->first('description') }}</span>
                        @endif
                    </div>
                </div>
            </div>
            <div class="card-footer">
                <button type="submit" class="btn btn-success"><i class="fa fa-save"></i> &nbsp {{ __('paket.button.update') }}</button>
                <a class="btn btn-secondary" href="{{ route('packets.index') }}"><i class="fa fa-arrow-left"></i> &nbsp {{ __('paket.button.cancel') }}</a>
            </div>
            {!! Form::close() !!}
        </div>
    </div>
</x-layouts.app-layout>