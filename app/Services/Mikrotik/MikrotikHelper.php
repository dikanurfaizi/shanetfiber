<?php

namespace App\Services\Mikrotik;

class MikrotikHelper
{

    /**
     * Helper privat untuk standarisasi response error
     */
    public function responseError(string $message): array
    {
        return [
            'error'   => true,
            'message' => $message,
        ];
    }

    /**
     * Helper privat untuk standarisasi response success
     */
    public function responseSuccess(string $message): array
    {
        return [
            'success' => true,
            'message' => $message,
        ];
    }

    /**
     * Helper privat untuk mendapatkan HTTP Status Code dari Throwable Exception
     */
    public function getStatusCode(\Throwable $e): int
    {
        $code = $e->getCode();
        return (is_numeric($code) && $code >= 400 && $code <= 599) ? (int) $code : 500;
    }
}
