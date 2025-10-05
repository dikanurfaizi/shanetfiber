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
        $perPageOnts = $request->get('perPageOnts', 10); // jumlah ONT per page

        $olt = $this->findOltWithDetails($oltId);

        $onts = DB::table('customers as c')
            ->rightJoin('onts as o', DB::raw('LEFT(c.mac_address, 14)'), '=', DB::raw('LEFT(o.mac_address, 14)'))
            ->where('o.olt_id', $oltId)
            ->select([
                'c.id as customer_id',
                'c.mac_address as customer_mac',
                'c.ip_address as customer_ip_address',
                'o.id as ont_id',
                'o.mac_address as ont_mac',
                'o.ont_index as ont_index',
                'c.name as customer_name',
                'o.status as ont_status',
                'o.rx_power as ont_rx_power',
                'o.temperature as ont_temp',
                'o.last_updated as ont_last_updated',
            ])
            ->paginate($perPageOnts);

        $histories = OltUpdateLog::where('olt_id', $oltId)
            ->orderBy('updated_at', 'desc')
            ->take(5)
            ->get();

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
            'histories'
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
