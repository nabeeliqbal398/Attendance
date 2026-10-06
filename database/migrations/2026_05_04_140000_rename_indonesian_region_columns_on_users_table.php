<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Rename Indonesian region columns to English equivalents.
 * - provinsi_kode    → province_code     (province)
 * - kabupaten_kode   → district_code     (regency / district)
 * - kecamatan_kode   → subdistrict_code  (sub-district)
 * - kelurahan_kode   → village_code      (village / urban-village)
 *
 * Existing data is preserved by Schema::renameColumn (in-place rename).
 * Laravel 11 supports renameColumn natively — no doctrine/dbal required.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'provinsi_kode')) {
                $table->renameColumn('provinsi_kode', 'province_code');
            }
            if (Schema::hasColumn('users', 'kabupaten_kode')) {
                $table->renameColumn('kabupaten_kode', 'district_code');
            }
            if (Schema::hasColumn('users', 'kecamatan_kode')) {
                $table->renameColumn('kecamatan_kode', 'subdistrict_code');
            }
            if (Schema::hasColumn('users', 'kelurahan_kode')) {
                $table->renameColumn('kelurahan_kode', 'village_code');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'province_code')) {
                $table->renameColumn('province_code', 'provinsi_kode');
            }
            if (Schema::hasColumn('users', 'district_code')) {
                $table->renameColumn('district_code', 'kabupaten_kode');
            }
            if (Schema::hasColumn('users', 'subdistrict_code')) {
                $table->renameColumn('subdistrict_code', 'kecamatan_kode');
            }
            if (Schema::hasColumn('users', 'village_code')) {
                $table->renameColumn('village_code', 'kelurahan_kode');
            }
        });
    }
};
