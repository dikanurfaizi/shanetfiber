@php
    $isEdit = request('edit') == 1;
@endphp

@if ($isEdit)
    @include('customers.partials.info-edit')
@else
    @include('customers.partials.info-view')
@endif
