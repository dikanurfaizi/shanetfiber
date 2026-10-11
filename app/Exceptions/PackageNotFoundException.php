<?php

namespace App\Exceptions;

use Exception;
use Throwable;

class PackageNotFoundException extends Exception implements Throwable
{
    /**
     * Exception khusus jika paket atau tagihan customer tidak ditemukan.
     */
}
