<?php

namespace App\Services\Networks;

use App\Models\Network\Olt;
use App\Models\Network\Ont;
use App\Models\Network\OidMapping;
use App\Models\Network\OltUpdateLog;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Helpers\OltHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OltService
{
    public function getPaginatedOlts(int $entries = 25): LengthAwarePaginator
    {
        return Olt::with('onts', 'latestUpdateLog', 'mapping')
            ->orderBy('id', 'asc')
            ->paginate($entries);
    }

    public function getOltStatistics(): array
    {
        $olts = Olt::with('onts')->get();

        return [
            'olts' => $olts,
            'totalOlts' => Olt::count(),
            'activeOlts' => $olts->where('status', OltHelper::STATUS_ACTIVE)->count(),
            'inActiveOlts' => $olts->where('status', OltHelper::STATUS_INACTIVE)->count(),
            'totalOnts' => Ont::whereHas('olt', fn($q) => $q->whereNull('deleted_at'))->count(),
        ];
    }

    public function findOltWithDetails(int $oltId): Olt
    {
        return Olt::with('onts', 'latestUpdateLog')->findOrFail($oltId);
    }

    public function getOltDetails(Request $request, int $oltId): array
    {
        $perPageOnts = $request->get('perPage', 10); // jumlah ONT per page

        $olt = $this->findOltWithDetails($oltId);

        $selectedPon = $request->get('pon_group', 1); // default ke 1 kalau kosong

        $ontsQuery = DB::table('onts as o')
            ->join('customers as c', function ($join) {
                $join->on('c.mac_address', 'LIKE', DB::raw("CONCAT(LEFT(o.mac_address, 15), '%')"));
            })
            ->join('olts as ol', function ($join) {
                $join->on('o.olt_id', '=', 'ol.id')
                    ->whereNull('ol.deleted_at');
            })
            ->where('o.olt_id', $oltId)
            ->where('o.pon_group', $selectedPon) // filter default ke PON 1
            ->select([
                'o.mac_address as ont_mac',
                DB::raw('LEFT(o.mac_address, 15) as ont_prefix'),
                'o.ont_index',
                'o.pon_group',
                'o.status as ont_status',
                'o.rx_power as ont_rx_power',
                'o.temperature as ont_temp',
                'o.last_updated as ont_last_updated',
                'c.id as customer_id',
                'c.mac_address as customer_mac',
                DB::raw('LEFT(c.mac_address, 15) as customer_prefix'),
                'c.ip_address as customer_ip_address',
                DB::raw('COALESCE(c.name, o.name) as customer_name'),
            ])
            ->orderBy('o.ont_index', 'asc');

        $onts = $ontsQuery->paginate($perPageOnts);

        $histories = OltUpdateLog::where('olt_id', $oltId)
            ->orderBy('updated_at', 'desc')
            ->take(5)
            ->get();

        $ponGroups = Ont::where('olt_id', $oltId)
            ->distinct()
            ->pluck('pon_group');

        $totalOnts = $onts->total();
        $onlineOnts = Ont::where('olt_id', $oltId)->where('status', 'Up')->count();
        $offlineOnts = Ont::where('olt_id', $oltId)->where('status', 'Down')->count();
        $lastUpdateOnts = Ont::where('olt_id', $oltId)->max('last_updated');

        return compact(
            'olt',
            'onts',
            'totalOnts',
            'onlineOnts',
            'offlineOnts',
            'lastUpdateOnts',
            'histories',
            'ponGroups',
            'selectedPon'
        );
    }

    public function createOlt(array $data): Olt
    {
        $data['port'] = $data['port'] ?? 161;
        $data['community'] = $data['community'] ?? 'public';
        $data['version'] = 'v2c';

        $oltVendor = OidMapping::where('vendor', $data['olt_type'])->first();
        if (!$oltVendor) {
            throw new \InvalidArgumentException('Selected OLT type is invalid.');
        }
        $data['vendor'] = $oltVendor->vendor;

        // Check for duplicate IP and port
        if (Olt::where('ip', $data['ip_address'])->where('port', $data['port'])->exists()) {
            throw new \InvalidArgumentException("An OLT with IP {$data['ip_address']} and port {$data['port']} already exists.");
        }

        return Olt::create([
            'name' => $data['olt_name'],
            'ip' => $data['ip_address'],
            'port' => $data['port'],
            'community' => $data['community'],
            'version' => $data['version'],
            'vendor' => $data['vendor'],
        ]);
    }

    public function updateOlt(Olt $olt, array $data): Olt
    {
        $data['port'] = $data['port'] ?? 161;

        // Check for duplicate IP and port (excluding current OLT)
        if (Olt::where('ip', $data['ip_address'])
            ->where('port', $data['port'])
            ->where('id', '!=', $olt->id)
            ->exists()
        ) {
            throw new \InvalidArgumentException("Another OLT with IP {$data['ip_address']} and port {$data['port']} already exists.");
        }

        $olt->update([
            'name' => $data['olt_name'],
            'ip' => $data['ip_address'],
            'port' => $data['port'],
            'community' => $data['community'] ?? 'public',
            'vendor' => $data['olt_type'],
            'latitude' => $data['latitude'],
            'longitude' => $data['longitude'],
        ]);

        return $olt->fresh();
    }

    public function deleteOlt(Olt $olt): void
    {
        $existingOnts = Ont::where('olt_id', $olt->id)->get();

        if ($existingOnts->isNotEmpty()) {
            $archiveData = $existingOnts->map(function ($ont) {
                return [
                    'source_olt_id' => $ont->olt_id,
                    'ont_index'     => $ont->ont_index,
                    'pon_group'     => $ont->pon_group,
                    'name'          => $ont->name,
                    'tx_power'      => $ont->tx_power,
                    'rx_power'      => $ont->rx_power,
                    'status'        => $ont->status,
                    'mac_address'   => $ont->mac_address,
                    'temperature'   => $ont->temperature,
                    'archived_at'   => now(),
                ];
            })->toArray();

            DB::table('ont_archives')->insert($archiveData);
        }
        $olt->delete();
    }

    public function filterOlts(array $filters): LengthAwarePaginator
    {
        $query = Olt::with('onts', 'latestUpdateLog');
        $entries = $filters['entries'] ?? 25;

        if (!empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('name', 'like', "%{$filters['search']}%")
                    ->orWhere('ip', 'like', "%{$filters['search']}%");
            });
        }

        if (!empty($filters['olt_type'])) {
            $vendorName = OidMapping::find($filters['olt_type'])?->vendor;
            if ($vendorName) {
                $query->where('vendor', $vendorName);
            }
        }

        if (!empty($filters['status']) && $filters['status'] != 0) {
            $statusMapping = (new OltHelper)->getStatusValue($filters['status']);
            $query->where('status', $statusMapping);
        }

        return $query->paginate($entries);
    }
}
