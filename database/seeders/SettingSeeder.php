<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = [
            [
                'key' => 'security.rate_limit_global',
                'value' => '1000',
                'group' => 'security',
                'type' => 'number',
                'description' => 'Global API rate limit per minute',
            ],
            [
                'key' => 'security.rate_limit_login',
                'value' => '5',
                'group' => 'security',
                'type' => 'number',
                'description' => 'Login rate limit per minute',
            ],
            [
                'key' => 'attendance.grace_period',
                'value' => '10',
                'group' => 'attendance',
                'type' => 'number',
                'description' => 'Late Grace Period (minutes)',
            ],
            [
                'key' => 'app.name',
                'value' => env('APP_NAME', 'Innovexify'),
                'group' => 'identity',
                'type' => 'text',
                'description' => 'Application Name',
            ],
            [
                'key' => 'app.company_name',
                'value' => 'Innovexify',
                'group' => 'identity',
                'type' => 'text',
                'description' => 'Company Name for Reports',
            ],
            [
                'key' => 'app.support_contact',
                'value' => 'example@gmail.com',
                'group' => 'identity',
                'type' => 'text',
                'description' => 'Support Email/Phone',
            ],
            [
                'key' => 'feature.require_photo',
                'value' => '1',
                'group' => 'features',
                'type' => 'boolean',
                'description' => 'Require Photo for Attendance',
            ],
            [
                'key' => 'app.maintenance_mode',
                'value' => '0',
                'group' => 'features',
                'type' => 'boolean',
                'description' => 'Enable Maintenance Mode',
            ],
            [
                'key' => 'app.time_format',
                'value' => '24',
                'group' => 'general',
                'type' => 'select',
                'description' => 'Time Format (12h/24h)',
            ],
            [
                'key' => 'app.show_seconds',
                'value' => '0',
                'group' => 'general',
                'type' => 'boolean',
                'description' => 'Show Seconds in Time Display',
            ],
            [
                'key' => 'leave.annual_quota',
                'value' => '12',
                'group' => 'leave',
                'type' => 'number',
                'description' => 'Annual Leave Quota (days)',
            ],
            [
                'key' => 'leave.sick_quota',
                'value' => '14',
                'group' => 'leave',
                'type' => 'number',
                'description' => 'Sick Leave Quota per Year (days)',
            ],
            [
                'key' => 'leave.require_attachment',
                'value' => '0',
                'group' => 'leave',
                'type' => 'boolean',
                'description' => 'Require Attachment for Leave/Sick Requests',
            ],
            [
                'key' => 'leave.auto_approve_days',
                'value' => '3',
                'group' => 'leave',
                'type' => 'number',
                'description' => 'Auto-Approve if not processed within X days (0 = disabled)',
            ],
            [
                'key' => 'notif.admin_email',
                'value' => 'example@gmail.com',
                'group' => 'notification',
                'type' => 'text',
                'description' => 'Admin Email for Notifications (leave empty if none)',
            ],
            [
                'key' => 'attendance.work_hours_per_day',
                'value' => '8',
                'group' => 'attendance',
                'type' => 'number',
                'description' => 'Work Hours per Day',
            ],
            [
                'key' => 'app.company_address',
                'value' => '',
                'group' => 'identity',
                'type' => 'textarea',
                'description' => 'Company Address',
            ],
            [
                'key' => 'enterprise_license_key',
                'value' => '',
                'group' => 'enterprise',
                'type' => 'textarea',
                'description' => 'Enterprise License Key',
            ],
        ];

        foreach ($settings as $setting) {
            Setting::updateOrCreate(
                ['key' => $setting['key']],
                $setting
            );
        }
    }
}
