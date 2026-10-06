<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('geofence_locations', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('address')->nullable();
            $table->double('latitude');
            $table->double('longitude');
            $table->unsignedInteger('radius')->default(100);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('is_active');
        });

        if (Schema::hasTable('barcodes')) {
            $now = now();
            $locations = DB::table('barcodes')
                ->whereNotNull('latitude')
                ->whereNotNull('longitude')
                ->where(function ($query) {
                    $query->where('latitude', '!=', 0)
                        ->orWhere('longitude', '!=', 0);
                })
                ->get()
                ->map(fn ($barcode) => [
                    'name' => $barcode->name ?: 'Office location',
                    'address' => null,
                    'latitude' => $barcode->latitude,
                    'longitude' => $barcode->longitude,
                    'radius' => max(1, (int) ($barcode->radius ?: 100)),
                    'is_active' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ])
                ->values()
                ->all();

            if (! empty($locations)) {
                DB::table('geofence_locations')->insert($locations);
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('geofence_locations');
    }
};
