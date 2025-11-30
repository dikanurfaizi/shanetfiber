<?php

namespace App\Services\Networks\Ont;

use App\Helpers\OltHelper;
use App\Models\Network\Ont;
use App\Models\Network\SignalThreshold;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

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
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public static function fetchWithRelationsPaginated(int $perPage = 25, array $data = [])
    {
        $status = OltHelper::getStatusFilter($data['status'] ?? null);
        $oltId = $data['olt_id'] ?? null;
        $signalId = $data['signal_id'] ?? null;
        $search = $data['search'] ?? null;

        // ======== BASE CUSTOMER SUBQUERY =========
        $resolvedCustomer = DB::table('customers as c')
            ->select([
                'c.id',
                'c.name',
                'c.username',
                'c.slug',
                'c.mac_address',
                'c.ip_address',
                DB::raw("REPLACE(c.mac_address, ':', '') AS normalized_mac"),
            ]);

        // =====================================================
        $query = Ont::query()
            ->from('onts as o')

            // === SMART RESOLVED MATCHING ===
            ->leftJoinSub($resolvedCustomer, 'c', function ($join) {

                // Conditions:
                // 1️⃣ Exact MAC match
                // 2️⃣ Prefix + matching first-name

                $join->whereRaw("
                REPLACE(c.mac_address, ':', '') = REPLACE(o.mac_address, ':', '')
                OR (
                    c.mac_address LIKE CONCAT(LEFT(o.mac_address, 15), '%')
                    AND LOWER(SUBSTRING_INDEX(c.name, ' ', 1)) = LOWER(SUBSTRING_INDEX(o.name, ' ', 1))
                )
            ");
            })

            // === SIGNAL CLASSIFICATION JOIN ===
            ->leftJoin('signal_thresholds as s', function ($join) {
                $join->whereColumn('o.rx_power', '>=', 's.min_value')
                    ->whereColumn('o.rx_power', '<=', 's.max_value');
            })

            // === VALID OLT ONLY ===
            ->join('olts as ol', function ($join) {
                $join->on('o.olt_id', '=', 'ol.id')
                    ->whereNull('ol.deleted_at');
            });

        // ======================================================
        // === STATUS FILTER
        if (! empty($status)) {
            if (strtolower($status) === 'other') {
                $query->where(function ($q) {
                    $q->whereNotIn('o.status', ['Up', 'Down'])
                        ->orWhereNull('o.status');
                });
            } else {
                $query->where('o.status', '=', $status);
            }
        }

        // === FILTER BY OLT
        if (! empty($oltId)) {
            $query->where('o.olt_id', '=', $oltId);
        }

        // === SIGNAL RANGE FILTER
        if (! empty($signalId)) {
            $signal = SignalThreshold::find($signalId);
            if ($signal) {
                $min = min($signal->min_value, $signal->max_value);
                $max = max($signal->min_value, $signal->max_value);
                $query->whereBetween('o.rx_power', [$min, $max]);
            }
        }

        // ======================================================
        // === SMART SEARCH (LIKE)
        if (! empty($search)) {

            $search = trim($search);

            $query->where(function ($q) use ($search) {

                $q->where('o.name', 'like', "%{$search}%")
                    ->orWhere('c.name', 'like', "%{$search}%")
                    ->orWhere('o.mac_address', 'like', "%{$search}%")
                    ->orWhere('c.mac_address', 'like', "%{$search}%")
                    ->orWhere('c.username', 'like', "%{$search}%")
                    ->orWhere('ol.name', 'like', "%{$search}%");

                $normalized = strtolower($search);

                $mapped = match ($normalized) {
                    'up', 'online' => 'Up',
                    'down', 'offline' => 'Down',
                    'other' => 'Other',
                    default => null
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

                if (is_numeric($search)) {
                    $q->orWhere('o.rx_power', '=', $search)
                        ->orWhere('o.ont_index', 'like', "%{$search}%");
                }
            });
        }

        // ======================================================
        // === FINAL SELECT
        return $query->selectRaw('
        ol.id as olt_id,
        ol.name as olt_name,
        o.ont_index,
        COALESCE(c.name, o.name) AS customer_name,
        o.status AS ont_status,
        o.rx_power AS ont_rx_power,
        c.mac_address AS customer_mac,
        c.id AS customer_id,
        c.slug AS slug,
        s.name as signal_class,
        s.label as signal_label,
        c.ip_address AS customer_ip,
        c.slug AS customer_slug
    ')
            ->orderBy('customer_name', 'asc')
            ->paginate($perPage);
    }
}
