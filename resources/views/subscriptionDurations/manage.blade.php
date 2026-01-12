<x-layouts.app-layout title="Manage Subscription Duration - {{ $package->name }}">

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4>{{ __('duration.title.package_durations') }}: {{ $package->name }}</h4>
            <a href="{{ route('subscriptionDurations.create', $package->slug) }}" class="btn btn-success">
                <i class="fa fa-plus"></i> {{ __('duration.button.add_duration') }}
            </a>
        </div>


        <div class="card-body">
            @if ($subscriptionDurations->count())
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>{{ __('duration.table.duration') }}</th>
                            <th>{{ __('duration.table.base_price') }}</th>
                            <th>{{ __('duration.table.discount') }}</th>
                            <th>{{ __('duration.table.final_price') }}</th>
                            <th>{{ __('duration.table.status') }}</th>
                            <th>{{ __('duration.table.action') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($subscriptionDurations as $packageDuration)
                            <tr>
                                {{-- No --}}
                                <td>
                                    <span class="badge rounded-pill bg-secondary text-white">{{ ++$i }}</span>
                                </td>

                                {{-- Nama Durasi --}}
                                <td>
                                    <span class="badge bg-primary text-white">
                                        <i class="fas fa-clock"></i>
                                        {{ $packageDuration->duration->name_en }}
                                        ({{ $packageDuration->duration->duration_months }}
                                        bln)
                                    </span>
                                </td>

                                {{-- Harga Package --}}
                                <td>
                                    <i class="fas fa-tags text-muted"></i>
                                    Rp {{ number_format($packageDuration->package->price, 0, ',', '.') }}
                                </td>

                                {{-- Diskon --}}
                                @if ($packageDuration->discount_type == 'percent')
                                    <td>
                                        <span class="badge bg-info text-white">
                                            <i class="fas fa-percentage"></i>
                                            {{ $packageDuration->discount_value }} %
                                        </span>
                                    </td>
                                @elseif($packageDuration->discount_type == 'amount')
                                    <td>
                                        <span class="badge bg-warning text-white">
                                            <i class="fas fa-minus-circle"></i>
                                            Rp {{ number_format($packageDuration->discount_value, 0, ',', '.') }}
                                        </span>
                                    </td>
                                @else
                                    <td>
                                        <span class="badge bg-secondary text-white">
                                            <i class="fas fa-ban"></i>
                                            Tidak ada
                                        </span>
                                    </td>
                                @endif

                                {{-- Harga Final --}}
                                <td>
                                    <span class="badge bg-success text-white">
                                        <i class="fas fa-check-circle"></i>
                                        Rp {{ number_format($packageDuration->final_price, 0, ',', '.') }}
                                    </span>
                                </td>

                                {{-- Status --}}
                                @if ($packageDuration->is_active)
                                    <td>
                                        <span class="badge bg-success text-white">
                                            <i class="fas fa-toggle-on"></i>
                                            Active
                                        </span>
                                    </td>
                                @else
                                    <td>
                                        <span class="badge bg-danger text-white">
                                            <i class="fas fa-toggle-off"></i>
                                            Inactive
                                        </span>
                                    </td>
                                @endif

                                {{-- Action Buttons --}}
                                <td>
                                    <a href="{{ route('subscriptionDurations.edit', $packageDuration->id) }}"
                                        class="btn btn-warning btn-sm">
                                        <i class="fa fa-edit"></i>
                                    </a>
                                    <form action="{{ route('subscriptionDurations.destroy', $packageDuration->id) }}"
                                        method="POST" style="display:inline-block">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm"
                                            onclick="return confirm('Hapus durasi ini?')">
                                            <i class="fa fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <p class="text-muted">Belum ada durasi untuk paket ini.</p>
            @endif
        </div>
    </div>

</x-layouts.app-layout>
