<?php

namespace App\Support;

use Ramsey\Uuid\Uuid;

trait GeneratesUUID
{
    /**
     * Generate a unique ID with a given prefix using UUID v4
     */
    public static function generateID(string $prefix): string
    {
        return $prefix . '-' . Uuid::uuid4()->toString();
    }
}
