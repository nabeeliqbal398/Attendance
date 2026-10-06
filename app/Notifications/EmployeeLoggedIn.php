<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Notifications\Notification;

class EmployeeLoggedIn extends Notification
{
    public function __construct(
        public User $employee,
        public array $location,
        public string $deviceSummary,
        public ?string $userAgent
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $locationLabel = $this->location['label'] ?? 'Unknown location';
        $ip = $this->location['ip'] ?? 'unknown IP';

        return [
            'type' => 'employee_login',
            'title' => 'Employee Login: ' . $this->employee->name,
            'message' => "{$this->employee->name} logged in from {$locationLabel} ({$ip}) — {$this->deviceSummary}",
            'user_id' => $this->employee->id,
            'user_name' => $this->employee->name,
            'employee_nip' => $this->employee->nip,
            'ip' => $ip,
            'city' => $this->location['city'] ?? null,
            'region' => $this->location['region'] ?? null,
            'country' => $this->location['country'] ?? null,
            'isp' => $this->location['isp'] ?? null,
            'device' => $this->deviceSummary,
            'user_agent' => $this->userAgent,
            'logged_in_at' => now()->toIso8601String(),
            'url' => route('admin.activity-logs'),
        ];
    }
}
