<?php

namespace App\Helpers;

use App\Contracts\AttendanceServiceInterface;
use App\Contracts\PayrollServiceInterface;
use App\Contracts\ReportingServiceInterface;
use App\Contracts\AuditServiceInterface;
use App\Services\Attendance\CommunityService;
use App\Services\Payroll\CommunityPayrollService;
use App\Services\Reporting\CommunityReportingService;
use App\Services\Audit\CommunityAuditService;

class Editions
{
    /**
     * Check if a specific feature service is running in Community Mode (Locked).
     */
    /**
     * Check if a specific feature service is running in Community Mode (Locked).
     */
    public static function isLocked(string $contractClass): bool
    {
        // For now, we only check for a valid license to unlock features.
        // We assume the necessary code is present or will be handled by the controller.
        return !\App\Services\Enterprise\LicenseGuard::hasValidLicense();
    }

    public static function payrollLocked(): bool
    {
        return self::isLocked(PayrollServiceInterface::class);
    }

    public static function reportingLocked(): bool
    {
        return self::isLocked(ReportingServiceInterface::class);
    }
    
    public static function auditLocked(): bool
    {
        return self::isLocked(AuditServiceInterface::class);
    }
    
    public static function attendanceLocked(): bool
    {
        return self::isLocked(AttendanceServiceInterface::class);
    }
}
