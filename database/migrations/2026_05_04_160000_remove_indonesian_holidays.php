<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Removes legacy Indonesian holidays seeded by the original app and replaces
 * them with the Pakistan calendar from HolidaySeeder.
 *
 * The HolidaySeeder uses updateOrCreate keyed on date — so old dates that
 * don't appear in the Pakistan list were never overwritten and stuck around.
 * This migration deletes anything that isn't a Pakistan holiday for 2025/2026
 * and then re-runs the seeder to make sure the Pakistan dates are present.
 */
return new class extends Migration {
    public function up(): void
    {
        $pakistanDates = [
            // 2025
            '2025-02-05', '2025-03-23', '2025-03-31', '2025-04-01', '2025-04-02',
            '2025-05-01', '2025-06-07', '2025-06-08', '2025-06-09', '2025-07-06',
            '2025-07-07', '2025-08-14', '2025-09-05', '2025-11-09', '2025-12-25',
            // 2026
            '2026-02-05', '2026-03-20', '2026-03-21', '2026-03-22', '2026-03-23',
            '2026-05-01', '2026-05-27', '2026-05-28', '2026-05-29', '2026-06-24',
            '2026-06-25', '2026-08-14', '2026-08-26', '2026-11-09', '2026-12-25',
        ];

        // Wipe any holiday whose date isn't in the Pakistan calendar.
        DB::table('holidays')->whereNotIn('date', $pakistanDates)->delete();

        // Backfill the Pakistan calendar (idempotent — uses updateOrCreate).
        if (class_exists(\Database\Seeders\HolidaySeeder::class)) {
            (new \Database\Seeders\HolidaySeeder())->run();
        }
    }

    public function down(): void
    {
        // No-op: we don't restore Indonesian holidays.
    }
};
