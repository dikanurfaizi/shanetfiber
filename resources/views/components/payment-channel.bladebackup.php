@props(['title', 'group', 'description', 'channels'])

<div class="col-12">
    <div class="card shadow mb-3">
        <div class="card-header bg-white py-2 d-flex justify-content-between align-items-center">
            <h5 class="m-0 font-weight-bold">{{ $title }}</h5>
        </div>
    </div>
    <div class="row mb-4">
        @foreach ($channels as $channel)
            @if ($channel['group'] === $group)
                <div class="col-12 col-md-4 col-lg-3 d-flex align-items-stretch mb-3">
                    <div class="card w-100">
                        <div class="card-body shadow" style="min-height: 236px; padding: 1rem;">
                            <div class="d-flex mb-2">
                                <div class="mr-auto text-left pr-2 payment-name">
                                    {{ $channel['name'] === 'Other Bank Virtual Account' ? 'Virtual Account Bank Lain' : $channel['name'] }}
                                </div>
                                <div class="ml-auto text-right">
                                    <span class="status-indicator {{ $channel['active'] ? 'on' : 'off' }}">
                                        {{ $channel['active'] ? 'ON' : 'OFF' }}
                                    </span>
                                </div>
                            </div>
                            <div class="text-center">
                                <img src="{{ $channel['icon_url'] }}" alt="" class="img-fluid"
                                    style="height: 35px;">
                            </div>
                            <p class="biaya mt-3 mb-0">
                                Biaya:
                                @if ($channel['fee_customer']['flat'] > 0)
                                    Rp {{ number_format($channel['fee_customer']['flat'], 0, ',', '.') }}
                                    @if ($channel['fee_customer']['percent'] > 0)
                                        + {{ number_format($channel['fee_customer']['percent'], 2, ',', '.') }}%
                                    @endif
                                @elseif ($channel['fee_customer']['percent'] > 0)
                                    {{ number_format($channel['fee_customer']['percent'], 2, ',', '.') }}%
                                @else
                                    Gratis
                                @endif
                            </p>
                            <div class="mt-2 quote">
                                {{ $description }}
                                {{ str_replace(['Virtual Account', 'Other Bank'], ['', 'Bank Lain'], $channel['name']) }}
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        @endforeach
    </div>
</div>
