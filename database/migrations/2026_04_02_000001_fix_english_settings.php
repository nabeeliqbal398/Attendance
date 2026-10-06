<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $fixes = [
            'leave.annual_quota' => 'Annual Leave Quota (days)',
            'leave.sick_quota' => 'Sick Leave Quota per Year (days)',
            'leave.require_attachment' => 'Require Attachment for Leave/Sick Requests',
            'leave.auto_approve_days' => 'Auto-Approve if not processed within X days (0 = disabled)',
            'notif.admin_email' => 'Admin Email for Notifications (leave empty if none)',
            'attendance.work_hours_per_day' => 'Work Hours per Day',
            'attendance.grace_period' => 'Late Grace Period (minutes)',
            'security.rate_limit_global' => 'Global API rate limit per minute',
            'security.rate_limit_login' => 'Login rate limit per minute',
            'feature.require_photo' => 'Require Photo for Attendance',
            'app.maintenance_mode' => 'Enable Maintenance Mode',
            'app.time_format' => 'Time Format (12h/24h)',
            'app.show_seconds' => 'Show Seconds in Time Display',
            'app.name' => 'Application Name',
            'app.company_name' => 'Company Name for Reports',
            'app.support_contact' => 'Support Email/Phone',
            'app.company_address' => 'Company Address',
            'enterprise_license_key' => 'Enterprise License Key',
        ];

        foreach ($fixes as $key => $description) {
            DB::table('settings')->where('key', $key)->update(['description' => $description]);
        }

        // Set company name to Innovexify
        DB::table('settings')->where('key', 'app.company_name')->update(['value' => 'Innovexify']);
        DB::table('settings')->where('key', 'app.name')->update(['value' => 'Innovexify']);

        // Force all users to English
        DB::table('users')->update(['language' => 'en']);
    }

    public function down(): void {}
};
