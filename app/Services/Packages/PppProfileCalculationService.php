<?php

namespace App\Services\Packages;

class PppProfileCalculationService
{
    public function calculate(
        array $inputs
    ): array {
        $maxUp  = $this->normalize($inputs['max_upload'], false);
        $maxDn  = $this->normalize($inputs['max_download'], false);

        $burstUp = $this->normalize($inputs['burst_upload']);
        $burstDn = $this->normalize($inputs['burst_download']);

        $thresUp = $this->normalize($inputs['threshold_upload']);
        $thresDn = $this->normalize($inputs['threshold_download']);

        $limUp   = $this->normalize($inputs['limit_at_upload']);
        $limDn   = $this->normalize($inputs['limit_at_download']);

        $btUp = $inputs['burst_time_upload'] !== null ? trim($inputs['burst_time_upload']) : null;
        $btDn = $inputs['burst_time_download'] !== null ? trim($inputs['burst_time_download']) : null;

        // ── Bantu susun pasangan ──
        $pair = fn($a, $b) => ($a && $b) ? "$a/$b" : null;

        // Ambil string satuan (M/k)
        [$maxUpStr, $maxDnStr]     = [$maxUp[1], $maxDn[1]];
        [$burstUpStr, $burstDnStr] = [$burstUp[1] ?? null, $burstDn[1] ?? null];
        [$thresUpStr, $thresDnStr] = [$thresUp[1] ?? null, $thresDn[1] ?? null];
        [$limUpStr, $limDnStr]     = [$limUp[1] ?? null, $limDn[1] ?? null];

        $burst   = $pair($burstUpStr, $burstDnStr);
        $thres   = $pair($thresUpStr, $thresDnStr);
        $burstT  = $pair($btUp, $btDn);
        $limitAt = $pair($limUpStr, $limDnStr);
        $max     = $pair($maxUpStr, $maxDnStr);

        // Cek apakah hanya max yang valid
        $hasPartialOther = $burst && $thres && $burstT && $limitAt;
        $onlyMaxFilled = $max && !$hasPartialOther;

        // Susun final segments
        $segments = [];
        if ($max) $segments[] = $max;

        if (!$onlyMaxFilled) {
            if ($burst)   $segments[] = $burst;
            if ($thres)   $segments[] = $thres;
            if ($burstT)  $segments[] = $burstT;
            $segments[] = $priority ?? 8;
            if ($limitAt) $segments[] = $limitAt;
        }

        $rateLimit = implode(' ', $segments);

        return [
            'maxLimitUpload'      => $maxUpStr,
            'maxLimitDownload'    => $maxDnStr,
            'minLimitUpload'      => $limUpStr,
            'minLimitDownload'    => $limDnStr,
            'limitAtUpload'       => $limUpStr,
            'limitAtDownload'     => $limDnStr,
            'burstLimitUpload'    => $burstUpStr,
            'burstLimitDownload'  => $burstDnStr,
            'thresholdUpload'     => $thresUpStr,
            'thresholdDownload'   => $thresDnStr,
            'burstTimeUpload'     => $btUp,
            'burstTimeDownload'   => $btDn,
            'priority'            => $priority ?? 8,
            'rasioCir'            => $inputs['rasio_cir'],
            'rateLimitMikrotik'   => $rateLimit,
        ];
    }

    private function normalize(?string $raw, bool $allowEmpty = true)
    {

        if ($raw === null || trim($raw) === '') {
            return $allowEmpty ? [null, null] : throw new \Exception('Input wajib diisi.');
        }

        $clean = str_replace(',', '.', trim($raw));
        $unit = strtolower(preg_replace('/[0-9.\s]/', '', $clean)) ?: 'k'; // Default ke K jika hanya angka

        if (!in_array($unit, ['k', 'm'])) {
            throw new \Exception("Satuan harus k atau M (input: $raw)");
        }

        $value = floatval(preg_replace('/[^0-9.]/', '', $clean));
        return [$value, $value . strtoupper($unit)];
    }
}
