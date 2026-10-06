<?php

namespace App\Livewire;

use App\ExtendedCarbon;
use App\Models\Attendance;
use App\Models\Barcode;
use App\Models\GeofenceLocation;
use App\Models\Shift;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Illuminate\Support\Carbon;

class ScanComponent extends Component
{
    public ?Attendance $attendance = null;
    public $shift_id = null;
    public $shifts = null;
    public ?array $currentLiveCoords = null;
    public string $successMsg = '';
    public bool $isAbsence = false;

    // Settings Cache
    public $gracePeriod = 0;
    public $photo = null;
    public $timeSettings = [];

    // Face Recognition
    public ?array $userFaceDescriptor = null;
    public ?Attendance $approvedAbsence = null;
    public bool $requiresFaceVerification = false;

    // GPS Accuracy for Fake GPS Detection
    public ?float $gpsAccuracy = null;
    public ?float $gpsVariance = null;

    public function validateBarcode(string $barcode, ?float $lat = null, ?float $lng = null)
    {
        // Update coordinates if provided
        if ($lat !== null && $lng !== null) {
            $this->currentLiveCoords = [$lat, $lng];
        }

        if (is_null($this->currentLiveCoords)) {
            return __('Invalid location');
        } else if (is_null($this->shift_id)) {
            return __('Invalid shift');
        }

        /** @var Attendance */
        $attendanceForDay = Attendance::where('user_id', Auth::user()->id)
            ->where('date', date('Y-m-d'))
            ->first();

        if ($attendanceForDay && 
            in_array($attendanceForDay->status, ['sick', 'excused', 'permission', 'leave']) && 
            $attendanceForDay->approval_status === Attendance::STATUS_APPROVED 
        ) {
            return __('You cannot check in because you are on Leave/Permission/Sick leave.');
        }

        /** @var Barcode */
        $barcodeModel = Barcode::firstWhere('value', $barcode);
        if (!Auth::check() || !$barcodeModel) {
            return __('Invalid barcode');
        }

        $geofenceResult = $this->validateCurrentGeofence();
        if ($geofenceResult !== true) {
            return $geofenceResult;
        }

        return true;
    }



    public function scan(string $barcode, ?float $lat = null, ?float $lng = null, ?string $photo = null, ?string $note = null)
    {
        $this->photo = $photo;
        
        // Update coordinates if provided
        if ($lat !== null && $lng !== null) {
            $this->currentLiveCoords = [$lat, $lng];
        }

        if (is_null($this->currentLiveCoords)) {
            return __('Invalid location');
        } else if (is_null($this->shift_id)) {
            return __('Invalid shift');
        }

        /** @var Attendance */
        $attendanceForDay = Attendance::where('user_id', Auth::user()->id)
            ->where('date', date('Y-m-d'))
            ->first();

        if ($attendanceForDay && 
            in_array($attendanceForDay->status, ['sick', 'excused', 'permission', 'leave']) && 
            $attendanceForDay->approval_status === Attendance::STATUS_APPROVED // Only block if explicitly Approved
        ) {
            return __('You cannot check in because you are on Leave/Permission/Sick leave.');
        }

        /** @var Barcode */
        $barcode = Barcode::firstWhere('value', $barcode);
        if (!Auth::check() || !$barcode) {
            return 'Invalid barcode';
        }

        if ((\App\Models\Setting::getValue('feature.require_photo', 1) == 1) && empty($this->photo)) {
             return 'Photo required';
        }

        $geofenceResult = $this->validateCurrentGeofence();
        if ($geofenceResult !== true) {
            return $geofenceResult;
        }



        /** @var Attendance|null */
        $existingAttendance = Attendance::where('user_id', Auth::user()->id)
            ->where('date', date('Y-m-d'))
            ->whereNotNull('time_in')
            ->whereNull('time_out')
            ->first();

        if (! $existingAttendance) {
            $existingAttendance = Attendance::where('user_id', Auth::user()->id)
                ->where('date', date('Y-m-d'))
                ->where('barcode_id', $barcode->id)
                ->first();
        }

        if (!$existingAttendance) {
            // Check In
            $attendance = $this->createAttendance($barcode, $this->photo);
            $this->successMsg = __('Attendance In Successful');
            \App\Models\ActivityLog::record('Check In', 'User checked in via barcode: ' . $barcode->name);
        } else {
            // Check Out
            // Handle legacy string vs new JSON array
            $attendance = $existingAttendance;
            $existingAttachment = $existingAttendance->attachment;
            $attachments = [];

            if ($existingAttachment) {
                $decoded = json_decode($existingAttachment, true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                     $attachments = $decoded;
                } else {
                     // Legacy string
                     $attachments = ['in' => $existingAttachment];
                }
            }

            if ($this->photo) {
                $attachments['out'] = $this->savePhoto($this->photo);
            }

            $updateData = [
                'time_out' => Carbon::now(),
                'latitude_out' => doubleval($this->currentLiveCoords[0]),
                'longitude_out' => doubleval($this->currentLiveCoords[1]),
                'accuracy_out' => $this->gpsAccuracy,
                'gps_variance_out' => $this->gpsVariance,
                'attachment' => json_encode($attachments),
            ];

            // Fake GPS Detection for Check Out
            $isSuspicious = $attendance->is_suspicious ?? false;
            $suspiciousReasons = $attendance->suspicious_reason ? explode('; ', $attendance->suspicious_reason) : [];
            
            if ($this->gpsAccuracy !== null && $this->gpsAccuracy < 5) {
                $isSuspicious = true;
                $suspiciousReasons[] = 'Checkout accuracy too perfect: ' . $this->gpsAccuracy . 'm';
            }
            if ($this->gpsVariance !== null && $this->gpsVariance == 0) {
                $isSuspicious = true;
                $suspiciousReasons[] = 'Checkout zero GPS variance';
            }
            
            $updateData['is_suspicious'] = $isSuspicious;
            $updateData['suspicious_reason'] = $isSuspicious ? implode('; ', array_unique($suspiciousReasons)) : null;

            // If note is provided (e.g. early checkout reasoning), save it
            if ($note) {
                $updateData['note'] = $note;
            }

            $attendance->update($updateData);
            $this->successMsg = __('Attendance Out Successful');
            \App\Models\ActivityLog::record('Check Out', 'User checked out.');
        }

        if ($attendance) {
            $this->setAttendance($attendance->fresh());
            Attendance::clearUserAttendanceCache(Auth::user(), Carbon::parse($attendance->date));
            $this->dispatch('attendance-recorded'); // Trigger update for other components
            
            // Flash success to session for the next page load (Home)
            session()->flash('success', $this->successMsg);
            
            // Return true to allow frontend to handle smooth transition & redirect
            return true;
        }
    }

    private function validateCurrentGeofence(): bool|string
    {
        if (is_null($this->currentLiveCoords)) {
            return __('Invalid location');
        }

        $nearest = GeofenceLocation::nearestTo(
            (float) $this->currentLiveCoords[0],
            (float) $this->currentLiveCoords[1]
        );

        if (! $nearest) {
            return __('No active geofence locations configured. Contact admin.');
        }

        if (! $nearest['within']) {
            return __('Location out of range') . ': ' . $nearest['distance'] . 'm. '
                . __('Max') . ': ' . $nearest['location']->radius . 'm'
                . ' (' . $nearest['location']->name . ')';
        }

        return true;
    }

    /** @return Attendance */
    public function createAttendance(Barcode $barcode, ?string $photoParam = null)
    {
        $now = Carbon::now();
        $date = $now->format('Y-m-d');
        $timeIn = $now;

        /** @var Shift */
        /** @var Shift */
        $shift = Shift::find($this->shift_id);
        
        $shiftStart = Carbon::parse($shift->start_time); // Assumes generic date, correct with time
        $shiftStart->setDate($now->year, $now->month, $now->day);
        
        // Apply Grace Period
        $lateThreshold = $shiftStart->copy()->addMinutes($this->gracePeriod);
        
        $status = $now->gt($lateThreshold) ? 'late' : 'present';

    
        $attachmentPath = $this->savePhoto($photoParam);

        return $this->saveAttendanceRequest($barcode, $date, $timeIn, $status, $attachmentPath, $shift);
    }

    private function savePhoto(?string $photoParam): ?string
    {
        if (!$photoParam) return null;
        
        $imageName = Auth::user()->id . '_' . time() . '.jpg';
        
        // Open Core: Delegate Storage to Service (Secure vs Public)
        $service = app(\App\Contracts\AttendanceServiceInterface::class);
        return $service->storeAttendancePhoto($photoParam, $imageName);
    }

    private function saveAttendanceRequest($barcode, $date, $timeIn, $status, $attachmentPath, $shift) {
        // Fake GPS Detection
        $isSuspicious = false;
        $suspiciousReasons = [];
        
        // Check 1: Accuracy too perfect (< 5 meters is suspicious for GPS)
        if ($this->gpsAccuracy !== null && $this->gpsAccuracy < 5) {
            $isSuspicious = true;
            $suspiciousReasons[] = 'Accuracy too perfect: ' . $this->gpsAccuracy . 'm';
        }
        
        // Check 2: Zero variance across samples (fake GPS is static)
        if ($this->gpsVariance !== null && $this->gpsVariance == 0) {
            $isSuspicious = true;
            $suspiciousReasons[] = 'Zero GPS variance (static location)';
        }

        // Check if there is an existing record to override (Rejected, Absent, or Pending Sick/Excused)
        $overrideable = Attendance::where('user_id', Auth::user()->id)
            ->where('date', $date)
            ->where(function($q) {
                $q->whereIn('status', ['rejected', 'absent', 'sick', 'excused'])
                  ->orWhere('approval_status', Attendance::STATUS_REJECTED);
            })
            ->first();

        if ($overrideable) {
            $overrideable->update([
                'barcode_id' => $barcode->id,
                'time_in' => $timeIn,
                'time_out' => null,
                'shift_id' => $shift->id,
                'latitude_in' => doubleval($this->currentLiveCoords[0]),
                'longitude_in' => doubleval($this->currentLiveCoords[1]),
                'accuracy_in' => $this->gpsAccuracy,
                'gps_variance_in' => $this->gpsVariance,

                'status' => $status,
                'note' => null, 
                'attachment' => $attachmentPath ? json_encode(['in' => $attachmentPath]) : null,
                'rejection_note' => null,
                'approval_status' => Attendance::STATUS_APPROVED, // Auto-approve presence
                'is_suspicious' => $isSuspicious,
                'suspicious_reason' => $isSuspicious ? implode('; ', $suspiciousReasons) : null,
            ]);
            return $overrideable;
        }

        return Attendance::create([
            'user_id' => Auth::user()->id,
            'barcode_id' => $barcode->id,
            'date' => $date,
            'time_in' => $timeIn,
            'time_out' => null,
            'shift_id' => $shift->id,

            // New: Separate location for check in with accuracy
            'latitude_in' => doubleval($this->currentLiveCoords[0]),
            'longitude_in' => doubleval($this->currentLiveCoords[1]),
            'accuracy_in' => $this->gpsAccuracy,
            'gps_variance_in' => $this->gpsVariance,

            'status' => $status,
            'note' => null,
            'attachment' => $attachmentPath ? json_encode(['in' => $attachmentPath]) : null,
            
            // Fake GPS Detection
            'is_suspicious' => $isSuspicious,
            'suspicious_reason' => $isSuspicious ? implode('; ', $suspiciousReasons) : null,
        ]);
    }

    protected function setAttendance(Attendance $attendance)
    {
        $this->attendance = $attendance;
        $this->shift_id = $attendance->shift_id;
        $this->isAbsence = in_array($attendance->status, ['sick', 'excused']) && $attendance->approval_status === Attendance::STATUS_APPROVED;
    }

    public function getAttendance()
    {
        if (is_null($this->attendance)) {
            return null;
        }
        return [
            'time_in' => $this->attendance?->time_in,
            'time_out' => $this->attendance?->time_out,
            'latitude_in' => $this->attendance?->latitude_in,
            'longitude_in' => $this->attendance?->longitude_in,
            'latitude_out' => $this->attendance?->latitude_out,
            'longitude_out' => $this->attendance?->longitude_out,
            'shift_end_time' => $this->attendance?->shift?->end_time,
        ];
    }

    public function mount()
    {
        $this->shifts = Shift::all();

        /** @var Attendance */
        $attendance = Attendance::where('user_id', Auth::user()->id)
            ->where('date', date('Y-m-d'))->first();

        if ($attendance) {
            $this->setAttendance($attendance);
        } 
        
        // Fallback: If no shift_id (e.g. from rejected leave), try auto-detect
        if (is_null($this->shift_id)) {
            // Priority 1: Check Manual Schedule
            /** @var \App\Models\Schedule */
            $schedule = \App\Models\Schedule::where('user_id', Auth::user()->id)
                ->where('date', date('Y-m-d'))
                ->first();

            if ($schedule && $schedule->shift_id) {
                // Use Scheduled Shift
                $this->shift_id = $schedule->shift_id;
            } else {
                // Priority 2: Auto-detect closest shift (Fallback)
                // get closest shift from current time
                // get closest shift from current time
                $shiftTimes = $this->shifts->pluck('start_time')->toArray();
                if (empty($shiftTimes)) {
                     // No shifts available
                     $this->shift_id = null;
                } else {
                    $closest = ExtendedCarbon::now()->closestFromDateArray($shiftTimes);
                    
                    if ($closest) {
                         $matched = $this->shifts
                            ->where(fn(Shift $shift) => $shift->start_time == $closest->format('H:i:s'))
                            ->first();
                         $this->shift_id = $matched ? $matched->id : null;
                    }
                }
            }
        }

        // Load Settings
        $this->gracePeriod = (int) \App\Models\Setting::getValue('attendance.grace_period', 0);
        
        $this->timeSettings = [
            'format' => \App\Models\Setting::getValue('app.time_format', '24'),
            'show_seconds' => (bool) \App\Models\Setting::getValue('app.show_seconds', false),
        ];

        // Load Face Recognition settings
        $user = Auth::user();
        
        // Check if Face ID is mandatory (Open Core Logic)
        $service = app(\App\Contracts\AttendanceServiceInterface::class);
        $requirePhoto = $service->shouldEnforceFaceEnrollment();

        if ($requirePhoto && !$user->hasFaceRegistered()) {
            return redirect()->route('face.enrollment');
        }

        if ($user->hasFaceRegistered()) {
            $this->userFaceDescriptor = $user->faceDescriptor->descriptor;
            $this->requiresFaceVerification = (bool) \App\Models\Setting::getValue('attendance.require_face_verification', true);
        }

        // Check for approved absence logic
        $today = date('Y-m-d');
        $attendance = Attendance::where('user_id', Auth::user()->id)
            ->where('date', $today)
            ->first();

        if ($attendance && 
            in_array($attendance->status, ['sick', 'excused', 'permission', 'leave']) &&
            $attendance->approval_status === Attendance::STATUS_APPROVED
        ) {
            $this->approvedAbsence = $attendance;
        }
    }

    public function render()
    {
        return view('livewire.scan');
    }
}
