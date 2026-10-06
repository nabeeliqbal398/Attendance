<?php

namespace App\Livewire;

use App\Models\Attendance;
use App\Models\GeofenceLocation;
use App\Models\Overtime;
use App\Models\Shift;
use App\Models\Schedule;
use App\Models\Setting;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class HomeAttendanceStatus extends Component
{
    public $hasCheckedIn = false;
    public $hasCheckedOut = false;
    public $attendance = null;
    public $approvedAbsence = null;
    public $requiresFaceEnrollment = false;
    public $overtime = null;
    public bool $isProcessing = false;

    protected $listeners = ['attendance-recorded' => 'checkAttendanceStatus'];

    public function mount()
    {
        $this->checkAttendanceStatus();
    }

    public function checkAttendanceStatus()
    {
        $user = Auth::user();
        $today = now()->format('Y-m-d');

        $service = app(\App\Contracts\AttendanceServiceInterface::class);
        $requirePhoto = $service->shouldEnforceFaceEnrollment();

        if ($requirePhoto && !$user->hasFaceRegistered()) {
            $this->requiresFaceEnrollment = true;
        }

        $this->attendance = Attendance::with(['shift', 'barcode'])
            ->where('user_id', $user->id)
            ->where('date', $today)
            ->first();

        if ($this->attendance) {
            $this->hasCheckedIn = !is_null($this->attendance->time_in);
            $this->hasCheckedOut = !is_null($this->attendance->time_out);

            if (in_array($this->attendance->status, ['sick', 'excused', 'permission', 'leave']) &&
                $this->attendance->approval_status === Attendance::STATUS_APPROVED
            ) {
                $this->approvedAbsence = $this->attendance;
            }
        }

        $this->overtime = Overtime::where('user_id', $user->id)
            ->where('date', $today)
            ->first();
    }

    /**
     * Quick Check In — GPS only, no QR scan required
     */
    public function quickCheckIn(float $lat, float $lng, float $accuracy, float $variance): bool|string
    {
        if ($this->isProcessing) return __('Please wait...');
        $this->isProcessing = true;

        try {
            $user = Auth::user();
            $today = now()->format('Y-m-d');

            // Check for approved absence
            $existing = Attendance::where('user_id', $user->id)->where('date', $today)->first();
            if ($existing && in_array($existing->status, ['sick', 'excused', 'permission', 'leave'])
                && $existing->approval_status === Attendance::STATUS_APPROVED) {
                return __('You cannot check in because you are on Leave/Permission/Sick leave.');
            }

            // Already checked in
            if ($existing && $existing->time_in && !in_array($existing->status, ['rejected', 'absent'])) {
                return __('You have already checked in today.');
            }

            // Find nearest allowed geofence location
            $nearest = $this->validateGeofence($lat, $lng);
            if (is_string($nearest)) {
                return $nearest;
            }

            $location = $nearest['location'];
            $distance = $nearest['distance'];

            // Resolve shift
            $shiftId = $this->resolveShiftId();
            if (!$shiftId) {
                return __('No shifts configured. Contact admin.');
            }

            $shift = Shift::find($shiftId);
            $now = Carbon::now();

            // Determine status (present vs late)
            $gracePeriod = (int) Setting::getValue('attendance.grace_period', 0);
            $shiftStart = Carbon::parse($shift->start_time);
            $shiftStart->setDate($now->year, $now->month, $now->day);
            $lateThreshold = $shiftStart->copy()->addMinutes($gracePeriod);
            $status = $now->gt($lateThreshold) ? 'late' : 'present';

            // Fake GPS detection
            $suspicious = $this->buildSuspiciousFlags($accuracy, $variance);

            // Check for overrideable record (rejected/absent)
            $overrideable = Attendance::where('user_id', $user->id)
                ->where('date', $today)
                ->where(function($q) {
                    $q->whereIn('status', ['rejected', 'absent', 'sick', 'excused'])
                      ->orWhere('approval_status', Attendance::STATUS_REJECTED);
                })->first();

            if ($overrideable) {
                $overrideable->update([
                    'barcode_id' => null,
                    'time_in' => $now,
                    'time_out' => null,
                    'shift_id' => $shift->id,
                    'latitude_in' => doubleval($lat),
                    'longitude_in' => doubleval($lng),
                    'accuracy_in' => $accuracy,
                    'gps_variance_in' => $variance,
                    'status' => $status,
                    'note' => null,
                    'attachment' => null,
                    'rejection_note' => null,
                    'approval_status' => Attendance::STATUS_APPROVED,
                    'is_suspicious' => $suspicious['is_suspicious'],
                    'suspicious_reason' => $suspicious['reason'],
                ]);
            } else {
                Attendance::create([
                    'user_id' => $user->id,
                    'barcode_id' => null,
                    'date' => $today,
                    'time_in' => $now,
                    'time_out' => null,
                    'shift_id' => $shift->id,
                    'latitude_in' => doubleval($lat),
                    'longitude_in' => doubleval($lng),
                    'accuracy_in' => $accuracy,
                    'gps_variance_in' => $variance,
                    'status' => $status,
                    'is_suspicious' => $suspicious['is_suspicious'],
                    'suspicious_reason' => $suspicious['reason'],
                ]);
            }

            \App\Models\ActivityLog::record('Quick Check In', "Checked in at {$location->name} ({$distance}m away)");
            Attendance::clearUserAttendanceCache($user, Carbon::parse($today));
            $this->dispatch('attendance-recorded');
            session()->flash('success', __('Check In Successful!'));
            $this->checkAttendanceStatus();
            return true;

        } finally {
            $this->isProcessing = false;
        }
    }

    /**
     * Quick Check Out — GPS only
     */
    public function quickCheckOut(float $lat, float $lng, float $accuracy, float $variance): bool|string
    {
        if ($this->isProcessing) return __('Please wait...');
        $this->isProcessing = true;

        try {
            $user = Auth::user();
            $today = now()->format('Y-m-d');

            $attendance = Attendance::where('user_id', $user->id)->where('date', $today)->first();
            if (!$attendance || !$attendance->time_in) {
                return __('You have not checked in yet.');
            }
            if ($attendance->time_out) {
                return __('You have already checked out today.');
            }

            // Find nearest allowed geofence and validate distance
            $nearest = $this->validateGeofence($lat, $lng);
            if (is_string($nearest)) {
                return $nearest;
            }

            // Fake GPS detection for checkout
            $suspicious = $this->buildSuspiciousFlags($accuracy, $variance, 'Checkout');
            $isSuspicious = $attendance->is_suspicious || $suspicious['is_suspicious'];
            $reasons = $attendance->suspicious_reason ? explode('; ', $attendance->suspicious_reason) : [];
            if ($suspicious['reason']) $reasons[] = $suspicious['reason'];

            $attendance->update([
                'time_out' => Carbon::now(),
                'latitude_out' => doubleval($lat),
                'longitude_out' => doubleval($lng),
                'accuracy_out' => $accuracy,
                'gps_variance_out' => $variance,
                'is_suspicious' => $isSuspicious,
                'suspicious_reason' => $isSuspicious ? implode('; ', array_unique($reasons)) : null,
            ]);

            \App\Models\ActivityLog::record('Quick Check Out', 'Checked out via dashboard at ' . $nearest['location']->name . '.');
            Attendance::clearUserAttendanceCache($user, Carbon::parse($today));
            $this->dispatch('attendance-recorded');
            session()->flash('success', __('Check Out Successful!'));
            $this->checkAttendanceStatus();
            return true;

        } finally {
            $this->isProcessing = false;
        }
    }

    /**
     * Find the nearest active geofence within its radius
     */
    private function validateGeofence(float $lat, float $lng): array|string
    {
        $nearest = GeofenceLocation::nearestTo($lat, $lng);

        if (! $nearest) {
            return __('No active geofence locations configured. Contact admin.');
        }

        if (! $nearest['within']) {
            return __('Location out of range') . ': ' . $nearest['distance'] . 'm. '
                . __('Max') . ': ' . $nearest['location']->radius . 'm'
                . ' (' . $nearest['location']->name . ')';
        }

        return $nearest;
    }

    /**
     * Auto-detect the correct shift for today
     */
    private function resolveShiftId(): ?int
    {
        $user = Auth::user();

        // Priority 1: Manual schedule for today
        $schedule = Schedule::where('user_id', $user->id)->where('date', date('Y-m-d'))->first();
        if ($schedule && $schedule->shift_id) {
            return $schedule->shift_id;
        }

        // Priority 2: Closest shift by time
        $shifts = Shift::all();
        if ($shifts->isEmpty()) return null;

        $now = Carbon::now();
        $closest = null;
        $minDiff = PHP_INT_MAX;

        foreach ($shifts as $shift) {
            $shiftTime = Carbon::parse($shift->start_time)->setDate($now->year, $now->month, $now->day);
            $diff = abs($now->diffInMinutes($shiftTime));
            if ($diff < $minDiff) {
                $minDiff = $diff;
                $closest = $shift;
            }
        }

        return $closest?->id;
    }

    /**
     * Check for fake GPS indicators
     */
    private function buildSuspiciousFlags(float $accuracy, float $variance, string $prefix = ''): array
    {
        $isSuspicious = false;
        $reasons = [];
        $p = $prefix ? "{$prefix} " : '';

        if ($accuracy < 5) {
            $isSuspicious = true;
            $reasons[] = "{$p}Accuracy too perfect: {$accuracy}m";
        }
        if ($variance == 0) {
            $isSuspicious = true;
            $reasons[] = "{$p}Zero GPS variance (static location)";
        }

        return [
            'is_suspicious' => $isSuspicious,
            'reason' => $isSuspicious ? implode('; ', $reasons) : null,
        ];
    }

    public function render()
    {
        return view('livewire.home-attendance-status');
    }
}
