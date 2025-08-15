<x-layouts.app-layout title="Manage Durasi - {{ $packet->name }}">

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4>Durasi Paket: {{ $packet->name }}</h4>
            {{-- <a href="{{ route('packets.durations.create', $packet->slug) }}" class="btn btn-success">
                <i class="fa fa-plus"></i> Tambah Durasi
            </a> --}}
        </div>

        <div class="card-body">
            @if ($durations->count())
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Durasi (Bulan)</th>
                            <th>Harga</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($durations as $duration)
                            <tr>
                                <td>{{ $duration->duration_months }} bulan</td>
                                <td>Rp {{ number_format($duration->price, 0, ',', '.') }}</td>
                                <td>
                                    <a href="{{ route('packets.durations.edit', [$packet->slug, $duration->id]) }}"
                                        class="btn btn-warning btn-sm">
                                        <i class="fa fa-edit"></i>
                                    </a>
                                    <form
                                        action="{{ route('packets.durations.destroy', [$packet->slug, $duration->id]) }}"
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
