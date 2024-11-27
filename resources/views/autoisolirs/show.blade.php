<x-layouts.cpanel.settings.app-layout title="{{ __('autoisolir.title.show') }}">

    <div class="card-header py-3">
        <h5 class="m-0 font-weight-bold text-primary">{{ __('autoisolir.header.show') }}</h5>
    </div>

    <div class="card-body col-lg-8">
        <div class="col-xs-12 col-sm-12 col-md-12">
            <div class="form-group">
                <strong>{{ __('autoisolir.label.server') }}</strong>
                <i class="fas fa-id-card-alt"></i> {{ $autoisolir->mikrotik->name ?? 'no data' }}
            </div>
        </div>
        <div class="col-xs-12 col-sm-12 col-md-12">
            <div class="form-group">
                <strong>{{ __('autoisolir.label.profile') }}</strong>
                {{ $autoisolir->profile_id ?? 'no data' }}
            </div>
        </div>
        <div class="col-xs-12 col-sm-12 col-md-12">
            <div class="form-group">
                <strong>{{ __('autoisolir.label.script') }}</strong>
                <span class="badge bg-success-soft text-primary">
                    {{ $autoisolir->name ?? 'no data' }} (ID: {{ $autoisolir->script_id ?? 'no data' }})
                </span>

            </div>
        </div>
        <div class="col-xs-12 col-sm-12 col-md-12">
            <div class="form-group">
                <strong>{{ __('autoisolir.label.schedule') }}</strong>
                <span class="badge bg-success-soft text-primary">
                    {{ $autoisolir->name ?? 'no data' }} (ID: {{ $autoisolir->schedule_id ?? 'no data' }})
                </span>
            </div>
        </div>

        <div class="col-xs-12 col-sm-12 col-md-12">
            <div class="form-group">
                <strong>{{ __('autoisolir.label.comment-payment') }}</strong>
                {{ $autoisolir->comment_payment ?? 'no data' }}
            </div>
        </div>
        <div class="col-xs-12 col-sm-12 col-md-12">
            <div class="form-group">
                <strong>{{ __('autoisolir.label.comment-unpayment') }}</strong>
                {{ $autoisolir->comment_unpayment ?? 'no data' }}
            </div>
        </div>
        <div class="col-xs-12 col-sm-12 col-md-12 mb-3">
            <div class="form-group">
                <strong>{{ __('autoisolir.label.on-event') }}</strong>
                {{ $schedule['on-event'] ?? 'no data' }}
            </div>
        </div>
        <div class="col-xs-12 col-sm-12 col-md-12 mb-3">
            <div class="form-group">
                <strong>{{ __('autoisolir.label.schedule-run-count') }}</strong>
                {{ __('autoisolir.label.executed', ['run-count' => $schedule['run-count'] ?? 'no data']) }}
            </div>
        </div>
        <div class="col-xs-12 col-sm-12 col-md-12 mb-3">
            <div class="form-group">
                <strong>{{ __('autoisolir.label.script-run-count') }}</strong>
                {{ __('autoisolir.label.executed', ['run-count' => $script['run-count'] ?? 'no data']) }}
            </div>
        </div>
    </div>
    <div class="card-footer">
        <a class="btn btn-sm btn-success" href="{{ route('autoisolir.edit', $autoisolir->id) }}"><i
                class="fa fa-edit"></i> {{ __('autoisolir.button.edit') }}</a>
        <a class="btn btn-sm btn-primary" href="{{ route('autoisolir.index') }}"><i class="fa fa-arrow-left"></i>
            {{ __('autoisolir.button.back') }}</a>
    </div>

</x-layouts.cpanel.settings.app-layout>