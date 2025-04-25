<x-layouts.app-layout title="Edit Paket">

    {{-- <div class="card-header py-3">
        <h5 class="m-0 font-weight-bold text-primary"> {{ __('paket.header.edit') }}</h5>
        <span>{{ $packet->name }} on server <span class="badge bg-success text-white">
                <i class="fas fa-server"></i> {{$packet->mikrotik->name}}</span>
        </span>
    </div> --}}

    <div class="row">
        <div class="col-lg-12">

            {!! Form::model($packet, ['method' => 'PATCH', 'route' => ['packets.update', $packet->slug]]) !!}
            <div class="card-body col-lg-8">

                <div class="row align-items-center">
                    <div class="col-md-12 mb-3">
                        <label for="name"><strong>{{ __('paket.label.name') }}</strong></label>
                        {!! Form::text('name', null, ['placeholder' => __('paket.placeholder.name'), 'class' => 'form-control']) !!}
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
                                <option value="{{ $pppprofile['name'] ?? 'no data' }}" @selected($pppprofile['name'] == $packet->profile)>
                                    {{ $pppprofile['name'] ?? 'no data' }}
                                </option>
                            @endforeach
                        </select>

                        @if ($errors->has('profile'))
                            <span class="text-danger">{{ $errors->first('profile') }}</span>
                        @endif
                    </div>
                </div>


                <div class="row align-items-center">
                    <div class="col-md-12 mb-3">
                        <label for="price"><strong>{{ __('paket.label.price') }}</strong></label>

                        {!! Form::text('price', null, ['placeholder' => __('paket.placeholder.price'), 'class' => 'form-control']) !!}

                        @if ($errors->has('price'))
                            <span class="text-danger">{{ $errors->first('price') }}</span>
                        @endif
                    </div>
                </div>

                <div class="row align-items-center">
                    <div class="col-md-12 mb-3">
                        <label for="description"><strong>{{ __('paket.label.description') }}</strong></label>

                        {!! Form::text('description', null, [
                            'placeholder' => __('paket.placeholder.description'),
                            'class' => 'form-control',
                        ]) !!}

                        @if ($errors->has('description'))
                            <span class="text-danger">{{ $errors->first('description') }}</span>
                        @endif
                    </div>
                </div>
            </div>
            <div class="card-footer">
                <button type="submit" class="btn btn-success"><i class="fa fa-save"></i> &nbsp
                    {{ __('paket.button.update') }}</button>
                <a class="btn btn-secondary" href="{{ route('packets.index') }}"><i class="fa fa-arrow-left"></i> &nbsp
                    {{ __('paket.button.cancel') }}</a>
            </div>
            {!! Form::close() !!}
        </div>
    </div>
</x-layouts.app-layout>
