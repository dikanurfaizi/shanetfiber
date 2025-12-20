@php
    $isEdit = request('edit') == 1;
@endphp

@if ($isEdit)
    @include('customers.partials.billing-edit')
@else
    @include('customers.partials.billing-view')
@endif
