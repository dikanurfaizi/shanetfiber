@php
    $groupedChannels = $channels->groupBy('group');
@endphp

@foreach (['Virtual Account' => 'Virtual Account', 'Convenience Store' => 'Gerai Offline', 'E-Wallet' => 'Dompet Digital'] as $group => $title)
    @if ($groupedChannels->has($group))
        <x-payment-channel :title="$title" :group="$group" :channels="$groupedChannels[$group]" />
    @endif
@endforeach
