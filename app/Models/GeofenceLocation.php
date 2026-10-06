<?php

namespace App\Models;

use App\Services\GeolocationService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GeofenceLocation extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'address',
        'latitude',
        'longitude',
        'radius',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'latitude' => 'float',
            'longitude' => 'float',
            'radius' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public static function nearestTo(float $latitude, float $longitude, bool $activeOnly = true): ?array
    {
        $query = static::query();

        if ($activeOnly) {
            $query->active();
        }

        $nearest = null;
        $minDistance = PHP_INT_MAX;

        foreach ($query->get() as $location) {
            $distance = GeolocationService::calculateDistance(
                $latitude,
                $longitude,
                $location->latitude,
                $location->longitude
            );

            if ($distance < $minDistance) {
                $minDistance = $distance;
                $nearest = [
                    'location' => $location,
                    'distance' => $distance,
                    'within' => $distance <= $location->radius,
                ];
            }
        }

        return $nearest;
    }
}
