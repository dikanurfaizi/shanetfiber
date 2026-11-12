<x-layouts.cpanel.settings.app-layout title="Web System">
    <div class="card-header py-3">
        <h6 class="m-0 fw-bold text-primary"> {{ __('websystem.header.customer') }}</h6>
    </div>

    <div class="row">
        <div class="col-lg-12">

            {!! Form::model($websystem, ['method' => 'PATCH', 'route' => ['websystem.update.customer', $websystem->slug]]) !!}
            <div class="card-body col-lg-8">

                <div class="row align-items-center">
                    <div class="col-md-6 mb-3">
                        <label for="customer_code"><strong>{{ __('websystem.label.customer_code') }}</strong></label>
                        {!! Form::text('customer_code', null, [
                            'placeholder' => __('websystem.placeholder.customer_code'),
                            'class' => 'form-control',
                        ]) !!}

                        @if ($errors->has('customer_code'))
                            <span class="text-danger">{{ $errors->first('customer_code') }}</span>
                        @else
                            <span class="text-primary">*required</span></br>
                            <span class="text-primary">*Ex: Customer Management, you can write CM</span>
                        @endif
                    </div>
                </div>
                <div class="row align-items-center">
                    <div class="col-md-6 mb-3">
                        <label
                            for="long_customer_code"><strong>{{ __('websystem.label.long_customer_code') }}</strong></label>
                        {!! Form::text('long_customer_code', null, [
                            'placeholder' => __('websystem.placeholder.long_customer_code'),
                            'class' => 'form-control',
                        ]) !!}
                        @if ($errors->has('long_customer_code'))
                            <span class="text-danger">{{ $errors->first('long_customer_code') }}</span>
                        @else
                            <span class="text-primary">*beetwen 3-25</span>
                        @endif
                    </div>
                </div>
                @php
                    $longCode = '%0' . $websystem->long_customer_code . 'd';
                    $customerId = $websystem->customer_code . sprintf('%02d', 1) . '-' . sprintf($longCode, 1);
                @endphp
                <div class="row align-items-center">
                    <div class="col-md-6 mb-3">
                        <label for="long_customer_code"><strong>{{ __('Format customer id:') }}</strong></label>
                        <span class="badge bg-success text-white">{{ $customerId }}</span></br>
                        <span class="text-primary"><strong>{{ $websystem->customer_code }}</strong></span><span
                            class="text-secondary"> : Identify your company.</span></br>
                        <span class="text-primary"><strong>{{ sprintf('%02d', 1) }}</strong></span><span
                            class="text-secondary"> : Identify your server id.</span></br>
                        <span class="text-primary"><strong>{{ sprintf($longCode, 1) }}</strong></span><span
                            class="text-secondary"> : Identify your customer id.</span></br>
                    </div>
                </div>
                <span class="text-secondary">Update at :
                    {{ Carbon\Carbon::parse($websystem->updated_at)->translatedFormat('d M Y, H:i:s') }}</span></br>
                <div class="card-footer">
                    <button type="submit" class="btn btn-success"><i class="fa fa-save"></i> &nbsp
                        {{ __('websystem.button.update') }}</button>
                </div>
                {!! Form::close() !!}
            </div>
        </div>

    </div>

</x-layouts.cpanel.settings.app-layout>
