<?php

namespace App\Services\Networks\Olt;

use App\Models\Network\Olt;
use App\Models\Network\Ont;
use App\Models\Network\OidMapping;
use App\Models\Network\OltUpdateLog;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Helpers\OltHelper;
use App\Helpers\LogHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OltDataService
{
    public static function getPaginatedOlts(int $entries = 25, string $traceId): LengthAwarePaginator
    {
        $data = Olt::with('onts', 'latestUpdateLog', 'mapping')
            ->orderBy('id', 'asc')
            ->paginate($entries);

        LogHelper::logInfo(null, [], 200, "Successfully getPaginatedOlts", $traceId);

        return $data;
    }

    public static function getOltStatistics(string $traceId): array
    {
        $olts = Olt::with('onts')->get();

        LogHelper::logInfo(null, [
            'totalOlts' => Olt::count(),
            'activeOlts' => $olts->where('status', OltHelper::STATUS_ACTIVE)->count(),
            'inActiveOlts' => $olts->where('status', OltHelper::STATUS_INACTIVE)->count(),
            'totalOnts' => Ont::whereHas('olt', fn($q) => $q->whereNull('deleted_at'))->count(),
        ], 200, "Successfully getOltStatistics", $traceId);

        return [
            'olts' => $olts,
            'totalOlts' => Olt::count(),
            'activeOlts' => $olts->where('status', OltHelper::STATUS_ACTIVE)->count(),
            'inActiveOlts' => $olts->where('status', OltHelper::STATUS_INACTIVE)->count(),
            'totalOnts' => Ont::whereHas('olt', fn($q) => $q->whereNull('deleted_at'))->count(),
        ];
    }

    public static function findOltWithDetails(int $oltId): Olt
    {
        return Olt::with('onts', 'latestUpdateLog')->findOrFail($oltId);
    }

    public static function getOltDetails(Request $request, int $oltId): array
    {
        $perPageOnts = $request->get('perPage', 64);
        $selectedPon = $request->get('pon_group', 1);

        $olt = self::findOltWithDetails($oltId);

        // ---------- List query (untuk tabel/pagination) ----------
        $listQuery = DB::table('onts as o')
            ->leftJoin('customers as c', function ($join) {
                $join->on('c.mac_address', 'LIKE', DB::raw("CONCAT(LEFT(o.mac_address, 15), '%')"));
            })
            ->join('olts as ol', function ($join) {
                $join->on('o.olt_id', '=', 'ol.id')
                    ->whereNull('ol.deleted_at');
            })
            // ✅ join dinamis ke tabel signal_thresholds berdasarkan range Rx Power
            ->leftJoin('signal_thresholds as s', function ($join) {
                $join->whereColumn('o.rx_power', '>=', 's.min_value')
                    ->whereColumn('o.rx_power', '<=', 's.max_value');
            })
            ->where('o.olt_id', $oltId)
            ->where('o.pon_group', $selectedPon);

        $onts = (clone $listQuery)
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
                'c.slug as slug',
                'c.mac_address as customer_mac',
                DB::raw('LEFT(c.mac_address, 15) as customer_prefix'),
                'c.ip_address as customer_ip_address',
                DB::raw('COALESCE(c.name, o.name) as customer_name'),
                's.name as signal_class',
                's.label as signal_label',
            ])
            ->orderByRaw("
            CAST(SUBSTRING_INDEX(o.ont_index, '.', 1) AS UNSIGNED),
            CAST(SUBSTRING_INDEX(SUBSTRING_INDEX(o.ont_index, '.', 2), '.', -1) AS UNSIGNED),
            CAST(SUBSTRING_INDEX(o.ont_index, '.', -1) AS UNSIGNED)
        ")
            ->paginate($perPageOnts);

        // ---------- Summary cards (global untuk 1 OLT, tidak terpengaruh pagination/PON) ----------
        $baseOnts = Ont::where('olt_id', $oltId);

        $totalOnts   = (clone $baseOnts)->count();                 // total semua ONT di OLT ini
        $onlineOnts  = (clone $baseOnts)->where('status', 'Up')->count();
        $offlineOnts = (clone $baseOnts)->where('status', 'Down')->count();
        $lastUpdateOnts = (clone $baseOnts)->max('last_updated');

        // Jika kamu ingin juga tahu total untuk PON terpilih (opsional):
        // $totalOntsForSelectedPon = (clone $baseOnts)->where('pon_group', $selectedPon)->count();

        $histories = OltUpdateLog::where('olt_id', $oltId)
            ->orderBy('updated_at', 'desc')
            ->take(5)
            ->get();

        $ponGroups = Ont::where('olt_id', $oltId)
            ->distinct()
            ->pluck('pon_group');

        return compact(
            'olt',
            'onts',
            'totalOnts',     // global (1 OLT)
            'onlineOnts',    // global (1 OLT)
            'offlineOnts',   // global (1 OLT)
            'lastUpdateOnts',
            'histories',
            'ponGroups',
            'selectedPon'
            // ,'totalOntsForSelectedPon' // kalau mau dipakai di UI
        );
    }

    public static function createOlt(array $data): Olt
    {
        $data['port'] = $data['port'] ?? 161;
        $data['community'] = $data['community'] ?? 'public';
        $data['version'] = 'v2c';
        $data['oid_mapping_id'] = $data['olt_type'];

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
            'oid_mapping_id' => $data['oid_mapping_id'],
            'status' => 1
        ]);
    }

    public static function updateOlt(Olt $olt, array $data): Olt
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
            'oid_mapping_id' => $data['olt_type'],
            'latitude' => $data['latitude'],
            'longitude' => $data['longitude'],
        ]);

        return $olt->fresh();
    }

    public static function deleteOlt(Olt $olt): void
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

    public static function filterOlts(array $filters): LengthAwarePaginator
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
