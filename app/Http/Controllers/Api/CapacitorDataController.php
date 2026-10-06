<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Barcode;
use App\Models\GeofenceLocation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class CapacitorDataController extends Controller
{
   /**
    * Get current location from device
    * POST /api/device/location
    */
   public function getLocation(Request $request)
   {
      $validated = $request->validate([
         'latitude' => ['required', 'numeric', 'between:-90,90'],
         'longitude' => ['required', 'numeric', 'between:-180,180'],
         'accuracy' => ['nullable', 'numeric'],
      ]);

      try {
         return response()->json([
            'success' => true,
            'data' => [
               'latitude' => $validated['latitude'],
               'longitude' => $validated['longitude'],
               'accuracy' => $validated['accuracy'] ?? null,
               'timestamp' => now()->toIso8601String(),
               'geofence' => $this->geofenceStatus(
                  (float) $validated['latitude'],
                  (float) $validated['longitude']
               ),
            ],
         ]);
      } catch (\Exception $e) {
         return response()->json([
            'success' => false,
            'message' => 'Failed to process location data: ' . $e->getMessage()
         ], 422);
      }
   }

   /**
    * Save barcode scan result
    * POST /api/device/barcode
    */
   public function saveBarcodeData(Request $request)
   {
      $validated = $request->validate([
         'barcode_data' => ['required', 'string'],
         'latitude' => ['nullable', 'numeric', 'between:-90,90'],
         'longitude' => ['nullable', 'numeric', 'between:-180,180'],
         'timestamp' => ['nullable', 'date_format:Y-m-d H:i:s'],
      ]);

      try {
         if (! isset($validated['latitude'], $validated['longitude'])) {
            return response()->json([
               'success' => false,
               'message' => 'GPS location is required for attendance.',
            ], 422);
         }

         $barcode = Barcode::where('value', $validated['barcode_data'])->first();
         if (!$barcode) {
            return response()->json([
               'success' => false,
               'message' => 'Invalid barcode data',
            ], 422);
         }

         $geofence = $this->geofenceStatus(
            (float) $validated['latitude'],
            (float) $validated['longitude']
         );

         if (! $geofence['allowed']) {
            return response()->json([
               'success' => false,
               'message' => $geofence['message'],
               'geofence' => $geofence,
            ], 422);
         }

         $timestamp = $validated['timestamp'] ?? now();
         $attendance = Attendance::firstOrNew([
            'user_id' => Auth::id(),
            'date' => now()->format('Y-m-d'),
         ]);

         if (!$attendance->exists || !$attendance->time_in) {
            $attendance->fill([
               'barcode_id' => $barcode?->id,
               'time_in' => $timestamp,
               'latitude_in' => (float) $validated['latitude'],
               'longitude_in' => (float) $validated['longitude'],
               'status' => 'present',
            ]);
         } else {
            $attendance->fill([
               'barcode_id' => $attendance->barcode_id ?? $barcode?->id,
               'time_out' => $timestamp,
               'latitude_out' => (float) $validated['latitude'],
               'longitude_out' => (float) $validated['longitude'],
            ]);
         }

         $attendance->save();
         if ($request->user()) {
            Attendance::clearUserAttendanceCache($request->user(), now());
         }

         return response()->json([
            'success' => true,
            'message' => 'Barcode data saved successfully',
            'attendance_id' => $attendance->id,
         ]);
      } catch (\Exception $e) {
         return response()->json([
            'success' => false,
            'message' => 'Failed to save barcode data: ' . $e->getMessage()
         ], 422);
      }
   }

   /**
    * Upload camera photo
    * POST /api/device/photo
    */
   public function uploadPhoto(Request $request)
   {
      $validated = $request->validate([
         'photo' => ['required', 'image', 'max:5120'], // 5MB
         'latitude' => ['nullable', 'numeric'],
         'longitude' => ['nullable', 'numeric'],
      ]);

      try {
         $disk = config('jetstream.attachment_disk', 'public');
         $path = $request->file('photo')->storePublicly(
            'attendance-photos',
            ['disk' => $disk]
         );

         $attendance = Attendance::where('user_id', Auth::id())
            ->where('date', now()->format('Y-m-d'))
            ->first();

         if ($attendance) {
            $attachment = $this->mergeAttachment(
               $attendance->attachment,
               $this->photoSlot($attendance),
               $path
            );

            $attendance->update([
               'attachment' => $attachment,
               'latitude_in' => $validated['latitude'] ?? $attendance->latitude_in,
               'longitude_in' => $validated['longitude'] ?? $attendance->longitude_in,
            ]);
         } else {
            // Create new attendance record with photo if doesn't exist
            $attendance = Attendance::create([
               'user_id' => Auth::id(),
               'date' => now()->format('Y-m-d'),
               'attachment' => json_encode(['in' => $path]),
               'latitude_in' => $validated['latitude'] ?? null,
               'longitude_in' => $validated['longitude'] ?? null,
               'status' => 'present',
            ]);
         }
         if ($request->user()) {
            Attendance::clearUserAttendanceCache($request->user(), now());
         }

         return response()->json([
            'success' => true,
            'message' => 'Photo uploaded successfully',
            'path' => Storage::disk($disk)->url($path),
            'attendance_id' => $attendance->id,
         ]);
      } catch (\Exception $e) {
         return response()->json([
            'success' => false,
            'message' => 'Failed to upload photo: ' . $e->getMessage()
         ], 422);
      }
   }

   private function mergeAttachment(?string $currentAttachment, string $slot, string $path): string
   {
      $attachments = [];

      if ($currentAttachment) {
         $decoded = json_decode($currentAttachment, true);
         $attachments = json_last_error() === JSON_ERROR_NONE && is_array($decoded)
            ? $decoded
            : ['in' => $currentAttachment];
      }

      $attachments[$slot] = $path;

      return json_encode($attachments);
   }

   private function photoSlot(Attendance $attendance): string
   {
      return $attendance->time_in && $attendance->time_out ? 'out' : 'in';
   }

   private function geofenceStatus(float $latitude, float $longitude): array
   {
      $nearest = GeofenceLocation::nearestTo($latitude, $longitude);

      if (! $nearest) {
         return [
            'allowed' => false,
            'message' => 'No active geofence locations configured. Contact admin.',
            'nearest' => null,
         ];
      }

      $location = $nearest['location'];
      $message = $nearest['within']
         ? 'Location allowed.'
         : 'Location out of range: ' . $nearest['distance'] . 'm. Max: ' . $location->radius . 'm (' . $location->name . ')';

      return [
         'allowed' => (bool) $nearest['within'],
         'message' => $message,
         'nearest' => [
            'id' => $location->id,
            'name' => $location->name,
            'distance' => (int) $nearest['distance'],
            'radius' => (int) $location->radius,
         ],
      ];
   }

   /**
    * Request device permissions status
    * GET /api/device/permissions
    */
   public function getPermissionsStatus(Request $request)
   {
      return response()->json([
         'success' => true,
         'permissions' => [
            'camera' => [
               'state' => 'prompt', // 'prompt', 'granted', 'denied'
               'description' => 'Camera access for barcode scanning'
            ],
            'geolocation' => [
               'state' => 'prompt',
               'description' => 'Location access for attendance tracking'
            ]
         ]
      ]);
   }
}
