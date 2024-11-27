<x-layouts.cpanel.settings.app-layout title="{{ __('whatsapp.number.page-title-show') }}">

    <div class="card-header py-3">
        <h5 class="m-0 font-weight-bold text-primary">{{ __('whatsapp.number.page-title-show') }}</h5>
    </div>

    <div class="card-body col-lg-8">
        <div class="row">
            <div class="col-sm-5 col-6 text-larger">
                <strong>
                    {{ trans('whatsapp.number.label.name') }}
                </strong>
            </div>
            <div class="col-sm-7 ">
                : {{ $data->name }}
            </div>
        </div>
        <div class="row">
            <div class="col-sm-5 col-6 text-larger">
                <strong>
                    {{ trans('whatsapp.number.label.number') }}
                </strong>
            </div>
            <div class="col-sm-7">
                : {{ $data->number }}
            </div>
        </div>
        <div class="row">
            <div class="col-sm-5 col-6 text-larger">
                <strong>
                    {{ trans('whatsapp.number.label.status') }}
                </strong>
            </div>
            <div class="col-sm-7 ">
                : @if ($data->disabled == false)
                    <span class="badge text-white bg-success">
                        <i class="fas fa-lock-open"></i> {{ __('paket.info.enabled') }}
                    </span>
                @else
                    <span class="badge text-white bg-secondary">
                        <i class="fas fa-lock"></i> {{ __('paket.info.disabled') }}
                    </span>
                @endif
            </div>
        </div>
        <div class="row mb-2">
            <div class="col-sm-5 col-6 text-larger">
                <strong>
                    {{ trans('whatsapp.number.label.description') }}
                </strong>
            </div>
            <div class="col-sm-7">
                : {{ $data->description }}
            </div>
        </div>
        <div class="clearfix"></div>
        <div class="border-bottom"></div>
        <div class="form-group row">
            <div class="col-md-5">
                <strong>
                    {!! trans('whatsapp.number.label.your-secret') !!}
                </strong>
            </div>
        </div>
        <div class="form-group row mb-1">
            <div class="col-md-12">
                <textarea class="form-control your-secret bg-warning" style="--bs-bg-opacity: .25;" id="your-secret" name="your-secret"
                    type="text" placeholder="{{ trans('whatsapp.number.ph.your-secret') }}"readonly>{{ $secret }}</textarea>
            </div>
        </div>
        <div class="clearfix"></div>
        <div class="border-bottom"></div>

    </div>
    <div class="card-footer">
        <a class="btn btn-sm btn-success" href="{{ route('whatsappnumber.edit', $data->slug) }}"><i
                class="fa fa-edit"></i>
            Edit</a>

        <a class="btn btn-sm btn-primary" href="{{ route('whatsappnumber.index') }}"><i class="fa fa-arrow-left"></i>
            Back</a>
    </div>

</x-layouts.cpanel.settings.app-layout>
