<x-layouts.mikrotiks.app-layout title="Crate PPP Secrets">
    <div class="card-header py-3">
        <h5 class="m-0 fw-bold text-primary"> {{ __('pppoe.secret.add-secret') }}</h5>
    </div>

    <div class="row">
        <div class="col-lg-12">

            {!! Form::open(['route' => ['ppp.secret.store', 'mikrotik' => $mikrotik], 'method' => 'POST']) !!}
            <div class="card-body col-lg-8">
                @if (count($errors) > 0)
                    <div class="col-md-12 mb-3">
                        <div class="alert alert-danger">
                            <strong>{{ __('roles.info.whoops') }}&nbsp</strong>{{ __('roles.info.there_were_some_problems_with_your_input') }}<br><br>
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                @endif



                <div class="row align-items-center">
                    <div class="col-md-12 mb-3">
                        <label for="name">{{ __('pppoe.secret.name') }}</label>
                        {!! Form::text('name', null, ['placeholder' => __('pppoe.secret.name'), 'class' => 'form-control']) !!}
                        @if ($errors->has('name'))
                            <span class="text-danger">{{ $errors->first('name') }}</span>
                        @endif
                    </div>
                </div>
                <div class="row align-items-center">
                    <div class="col-md-12 mb-3">
                        <label for="password">{{ __('pppoe.secret.password') }}</label>
                        <div class="input-group">
                            {!! Form::password('password', ['placeholder' => __('user.placeholder.password'), 'class' => 'form-control']) !!}
                        </div>
                        @if ($errors->has('password'))
                            <span class="text-danger">{{ $errors->first('password') }}</span>
                        @endif
                    </div>
                </div>

                <div class="row align-items-center">
                    <div class="col-md-12 mb-3">
                        <label for="service">{{ __('pppoe.secret.service') }}</label>
                        <select id="service" name="service" class="form-control">
                            <option value="">-- Select Service --</option>

                            @foreach ($pppservices as $pppservice)
                                <option value="{{ $pppservice->name ?? 'no data' }}">
                                    {{ $pppservice->name ?? 'no data' }}
                                </option>
                            @endforeach
                        </select>

                        @if ($errors->has('service'))
                            <span class="text-danger">{{ $errors->first('service') }}</span>
                        @endif
                    </div>
                </div>


                <div class="row align-items-center">
                    <div class="col-md-12 mb-3">
                        <label for="profile">{{ __('pppoe.secret.profile') }}</label>
                        <select id="profile" name="profile" class="form-control">
                            <option value="">-- Select Profile --</option>
                            @foreach ($pppprofiles as $pppprofile)
                                <option value="{{ $pppprofile['name'] ?? 'no data' }}">
                                    {{ $pppprofile['name'] ?? 'no data' }}
                                </option>
                            @endforeach
                        </select>
                        @if ($errors->has('profile'))
                            <span class="text-danger">{{ $errors->first('profile') }}</span>
                        @endif
                    </div>
                </div>
            </div>
            <div class="card-footer">
                <button type="submit" id="simpan-router" class="btn btn-sm btn-success"><i class="fa fa-save"></i>
                    &nbsp{{ __('pppoe.secret.button.save') }}</button>
                <a class="btn btn-sm btn-secondary"
                    href="{{ route('ppp.secrets.index', ['mikrotik' => $mikrotik]) }}"><i class="fa fa-arrow-left"></i>
                    &nbsp {{ __('pppoe.secret.button.cancel') }}</a>
            </div>
            {!! Form::close() !!}
        </div>
    </div>
</x-layouts.mikrotiks.app-layout>
