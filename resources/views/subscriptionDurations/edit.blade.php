<x-layouts.app-layout title="Manage Subscription Duration - {{ $package->name }}">

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4>Package Duration: {{ $package->name }}</h4>
            {{-- <a href="{{ route('packets.durations.create', $package->slug) }}" class="btn btn-success">
                <i class="fa fa-plus"></i> Tambah Durasi
            </a> --}}
        </div>

        <div class="card-body">
            @if ($subscriptionDurations->count() > 0)
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Duration (in Months)</th>
                            <th>Price</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($subscriptionDurations as $duration)
                            <tr>
                                <td>{{ $duration->duration_months }} bulan</td>
                                <td>Rp {{ number_format($duration->price, 0, ',', '.') }}</td>
                                <td>
                                    {{-- <a href="{{ route('packages.durations.edit', [$package->slug, $duration->id]) }}" --}}
                                        <a href=""
                                        class="btn btn-warning btn-sm">
                                        <i class="fa fa-edit"></i>
                                    </a>
                                    <form
                                        {{-- action="{{ route('packages.durations.destroy', [$package->slug, $duration->id]) }}" --}}
                                        action=""
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
