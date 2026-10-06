<?php

namespace App\Services\Enterprise;

/**
 * All features ship unlocked. Kept as a single switch so gating
 * can be reintroduced in one place if it's ever needed.
 */
final class LicenseGuard
{
    public static function check(): bool
    {
        return true;
    }

    public static function hasValidLicense(): bool
    {
        return true;
    }

    public static function getLicenseInfo(): ?array
    {
        return null;
    }
}
