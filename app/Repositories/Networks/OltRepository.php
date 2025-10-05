<?php

namespace App\Repositories\Networks;

use App\Models\Network\Olt;
use App\Models\Network\Ont;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class OltRepository
{
    public function find(int $id): ?Olt
    {
        return Olt::find($id);
    }

    public function findOrFail(int $id): Olt
    {
        return Olt::findOrFail($id);
    }

    public function getAll(): Collection
    {
        return Olt::all();
    }

    public function getWithRelations(array $relations = []): Collection
    {
        return Olt::with($relations)->get();
    }

    public function paginateWithRelations(array $relations = [], int $perPage = 25): LengthAwarePaginator
    {
        return Olt::with($relations)->paginate($perPage);
    }

    public function create(array $data): Olt
    {
        return Olt::create($data);
    }

    public function update(Olt $olt, array $data): bool
    {
        return $olt->update($data);
    }

    public function delete(Olt $olt): bool
    {
        return $olt->delete();
    }

    public function existsByIpAndPort(string $ip, int $port, ?int $excludeId = null): bool
    {
        $query = Olt::where('ip', $ip)->where('port', $port);

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->exists();
    }
}
