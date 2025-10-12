<?php

namespace App\Services\Networks\Ont;

use App\Models\Network\Ont;
use App\Models\Network\SignalThreshold;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use App\Helpers\OltHelper;

class OntDataService
{
    /**
     * Ambil summary count ONT (total, online, offline, other)
     * Menggunakan join ke OLT yang belum dihapus.
     * Hanya 1 query SQL (super efisien)
     */
    public static function summaryCounts(bool $useCache = true)
    {
        if ($useCache) {
            // Cache hasil summary selama 60 detik
            return Cache::remember('ont-summary', 60, function () {
                return self::generateSummary();
            });
        }

        return self::generateSummary();
    }

    /**
     * Query utama untuk summary ONT
     */
    protected static function generateSummary()
    {
        return Ont::join('olts as o2', 'onts.olt_id', '=', 'o2.id')
            ->whereNull('o2.deleted_at')
            ->selectRaw("
                COUNT(*) as total,
                SUM(CASE WHEN onts.status = 'Up' THEN 1 ELSE 0 END) as online,
                SUM(CASE WHEN onts.status = 'Down' THEN 1 ELSE 0 END) as offline,
                SUM(CASE WHEN onts.status NOT IN ('Up', 'Down') OR onts.status IS NULL THEN 1 ELSE 0 END) as other
            ")
            ->first();
    }

    /**
     * Ambil daftar ONT dengan filter dinamis berdasarkan status, olt_id, dan signal_id.
     *
     * @param int $perPage
     * @param array $data
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public static function fetchWithRelationsPaginated(int $perPage = 25, array $data = [])
    {
        $status   = OltHelper::getStatusFilter($data['status'] ?? null);
        $oltId    = $data['olt_id'] ?? null;
        $signalId = $data['signal_id'] ?? null;
        $search   = $data['search'] ?? null;

        $query = Ont::query()
            ->from('onts as o')
            ->leftJoin('signal_thresholds as s', function ($join) {
                $join->whereColumn('o.rx_power', '>=', 's.min_value')
                    ->whereColumn('o.rx_power', '<=', 's.max_value');
            })
            ->leftJoin('customers as c', function ($join) {
                $join->on('c.mac_address', 'like', DB::raw("CONCAT(LEFT(o.mac_address, 15), '%')"));
            })
            ->join('olts as ol', function ($join) {
                $join->on('o.olt_id', '=', 'ol.id')
                    ->whereNull('ol.deleted_at');
            });

        // === APPLY FILTERS ===
        if (!empty($status)) {
            if (strtolower($status) === 'other') {
                $query->where(function ($q) {
                    $q->whereNotIn('o.status', ['Up', 'Down'])
                        ->orWhereNull('o.status');
                });
            } else {
                $query->where('o.status', '=', $status);
            }
        }

        if (!empty($oltId)) {
            $query->where('o.olt_id', '=', $oltId);
        }

        if (!empty($signalId)) {
            $signal = SignalThreshold::find($signalId);
            if ($signal) {
                $min = min($signal->min_value, $signal->max_value);
                $max = max($signal->min_value, $signal->max_value);
                $query->whereBetween('o.rx_power', [$min, $max]);
            }
        }

        // === SMART SEARCH ===
        if (!empty($search)) {
            $search = trim($search);

            $query->where(function ($q) use ($search) {
                // 1️⃣ Text-based search (ONT & Customer names, MAC, username)
                $q->where('o.name', 'like', "%{$search}%")
                    ->orWhere('c.name', 'like', "%{$search}%")
                    ->orWhere('o.mac_address', 'like', "%{$search}%")
                    ->orWhere('c.mac_address', 'like', "%{$search}%")
                    ->orWhere('c.username', 'like', "%{$search}%")
                    ->orWhere('ol.name', 'like', "%{$search}%");

                // 2️⃣ Status keyword detection (Up/Down/Online/Offline/Other)
                $normalized = strtolower($search);
                $mapped = match ($normalized) {
                    'up', 'online'   => 'Up',
                    'down', 'offline' => 'Down',
                    'other'          => 'Other',
                    default           => null
                };

                if ($mapped) {
                    if ($mapped === 'Other') {
                        $q->orWhere(function ($q2) {
                            $q2->whereNotIn('o.status', ['Up', 'Down'])
                                ->orWhereNull('o.status');
                        });
                    } else {
                        $q->orWhere('o.status', '=', $mapped);
                    }
                }

                // 3️⃣ Numeric search (for rx_power or ont_index)
                if (is_numeric($search)) {
                    $q->orWhere('o.rx_power', '=', $search)
                        ->orWhere('o.ont_index', 'like', "%{$search}%");
                }
            });
        }

        return $query->selectRaw("
                ol.id as olt_id,
                ol.name as olt_name,
                o.ont_index,
                COALESCE(c.name, o.name) AS customer_name,
                o.status AS ont_status,
                o.rx_power AS ont_rx_power,
                o.mac_address AS customer_mac,
                c.id AS customer_id,
                c.slug AS slug,
                s.name as signal_class,
                s.label as signal_label
            ")
            ->orderBy('customer_name', 'asc')
            ->paginate($perPage);
    }
}
