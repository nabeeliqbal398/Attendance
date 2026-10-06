<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Rename Indonesian region table + columns to English equivalents:
 *   table:  wilayah → regions
 *   column: kode    → code
 *   column: nama    → name
 *
 * Idempotent — uses Schema::has* guards.
 * Existing rows preserved via in-place rename (no data movement).
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('wilayah') && !Schema::hasTable('regions')) {
            Schema::rename('wilayah', 'regions');
        }

        if (Schema::hasTable('regions')) {
            Schema::table('regions', function (Blueprint $table) {
                if (Schema::hasColumn('regions', 'kode')) {
                    $table->renameColumn('kode', 'code');
                }
                if (Schema::hasColumn('regions', 'nama')) {
                    $table->renameColumn('nama', 'name');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('regions')) {
            Schema::table('regions', function (Blueprint $table) {
                if (Schema::hasColumn('regions', 'code')) {
                    $table->renameColumn('code', 'kode');
                }
                if (Schema::hasColumn('regions', 'name')) {
                    $table->renameColumn('name', 'nama');
                }
            });
        }

        if (Schema::hasTable('regions') && !Schema::hasTable('wilayah')) {
            Schema::rename('regions', 'wilayah');
        }
    }
};
