<x-layouts.mikrotiks.app-layout title="Edit PPP Secrets">

    <div class="card-header py-3">
        <h5 class="m-0 font-weight-bold text-primary"> {{ __('pppoe.secret.edit-secret') }}</h5>
    </div>

    <div class="row">
        <div class="col-lg-12">



            @foreach($pppsecrets as $pppsecret)
            <form method="POST" action="{{ route('ppp.secret.update',[$mikrotik, 'id'=> $pppsecret['.id']]) }}">
                @csrf
                @method('PUT')
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
                            <input class="form-control" id="name" name="name" type="text" placeholder="{{ __('hotspot.router-name') }}" value="{{ old('name', $pppsecret['name']) }}">
                            @if ($errors->has('name'))
                            <span class="text-danger">{{ $errors->first('name') }}</span>
                            @endif
                        </div>
                    </div>
                    <div class="row align-items-center">
                        <div class="col-md-12 mb-3">
                            <label for="password">{{ __('pppoe.secret.password') }}</label>
                            <input class="form-control" id="password" name="password" type="password" placeholder=" 12345" value="{{ old('password', $pppsecret['password']) }}">

                            @if ($errors->has('password'))
                            <span class="text-danger">{{ $errors->first('password') }}</span>
                            @endif
                        </div>
                    </div>


                    <div class="row align-items-center">
                        <div class="col-md-12 mb-3">
                            <label for="service">{{ __('pppoe.secret.service') }}</label>
                            <select id="service" name="service" class="form-control">
                                @foreach ($pppservices as $pppservice)
                                <option value="{{ old('service', $pppservice->name ?? 'no service') }}" {{ $pppservice->name == $pppsecret['service'] ? 'selected' : '' }}>
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
                                @foreach ($pppprofiles as $pppprofile)
                                <option value="{{ $pppprofile['name'] ?? 'no data' }}" @selected($pppprofile['name']==$pppsecret['profile'])>
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
                    <button type="submit" id="simpan-router" class="btn btn-sm btn-primary">{{ __('pppoe.secret.button.update') }}</button>
                    <a class="btn btn-sm btn-secondary" href="{{ route('ppp.secrets.index', ['mikrotik'=> $mikrotik]) }}"><i class="fa fa-arrow-left"></i> &nbsp {{ __('pppoe.secret.button.cancel') }}</a>

                </div>
            </form>
            @endforeach
        </div>
    </div>
</x-layouts.mikrotiks.app-layout>