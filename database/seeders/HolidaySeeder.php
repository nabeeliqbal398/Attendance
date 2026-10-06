<?php

namespace Database\Seeders;

use App\Models\Holiday;
use Illuminate\Database\Seeder;

class HolidaySeeder extends Seeder
{
    /**
     * Pakistan national holidays for 2025 and 2026.
     * Religious dates (Eid, Ashura, Milad) depend on local moon sighting and may shift by 1 day.
     * Admins should adjust as needed via the Holiday Manager.
     */
    public function run(): void
    {
        $holidays = [
            // 2025
            ['date' => '2025-02-05', 'name' => 'Kashmir Day',                   'description' => 'National Holiday', 'is_recurring' => 0],
            ['date' => '2025-03-23', 'name' => 'Pakistan Day',                  'description' => 'National Holiday', 'is_recurring' => 0],
            ['date' => '2025-03-31', 'name' => 'Eid ul-Fitr (Day 1)',           'description' => 'National Holiday', 'is_recurring' => 0],
            ['date' => '2025-04-01', 'name' => 'Eid ul-Fitr (Day 2)',           'description' => 'National Holiday', 'is_recurring' => 0],
            ['date' => '2025-04-02', 'name' => 'Eid ul-Fitr (Day 3)',           'description' => 'National Holiday', 'is_recurring' => 0],
            ['date' => '2025-05-01', 'name' => 'Labour Day',                    'description' => 'National Holiday', 'is_recurring' => 0],
            ['date' => '2025-06-07', 'name' => 'Eid ul-Adha (Day 1)',           'description' => 'National Holiday', 'is_recurring' => 0],
            ['date' => '2025-06-08', 'name' => 'Eid ul-Adha (Day 2)',           'description' => 'National Holiday', 'is_recurring' => 0],
            ['date' => '2025-06-09', 'name' => 'Eid ul-Adha (Day 3)',           'description' => 'National Holiday', 'is_recurring' => 0],
            ['date' => '2025-07-06', 'name' => 'Ashura (9 Muharram)',           'description' => 'National Holiday', 'is_recurring' => 0],
            ['date' => '2025-07-07', 'name' => 'Ashura (10 Muharram)',          'description' => 'National Holiday', 'is_recurring' => 0],
            ['date' => '2025-08-14', 'name' => 'Independence Day',              'description' => 'National Holiday', 'is_recurring' => 0],
            ['date' => '2025-09-05', 'name' => 'Eid Milad un-Nabi',             'description' => 'National Holiday', 'is_recurring' => 0],
            ['date' => '2025-11-09', 'name' => 'Iqbal Day',                     'description' => 'National Holiday', 'is_recurring' => 0],
            ['date' => '2025-12-25', 'name' => 'Quaid-e-Azam Day / Christmas',  'description' => 'National Holiday', 'is_recurring' => 0],

            // 2026
            ['date' => '2026-02-05', 'name' => 'Kashmir Day',                   'description' => 'National Holiday', 'is_recurring' => 0],
            ['date' => '2026-03-20', 'name' => 'Eid ul-Fitr (Day 1)',           'description' => 'National Holiday', 'is_recurring' => 0],
            ['date' => '2026-03-21', 'name' => 'Eid ul-Fitr (Day 2)',           'description' => 'National Holiday', 'is_recurring' => 0],
            ['date' => '2026-03-22', 'name' => 'Eid ul-Fitr (Day 3)',           'description' => 'National Holiday', 'is_recurring' => 0],
            ['date' => '2026-03-23', 'name' => 'Pakistan Day',                  'description' => 'National Holiday', 'is_recurring' => 0],
            ['date' => '2026-05-01', 'name' => 'Labour Day',                    'description' => 'National Holiday', 'is_recurring' => 0],
            ['date' => '2026-05-27', 'name' => 'Eid ul-Adha (Day 1)',           'description' => 'National Holiday', 'is_recurring' => 0],
            ['date' => '2026-05-28', 'name' => 'Eid ul-Adha (Day 2)',           'description' => 'National Holiday', 'is_recurring' => 0],
            ['date' => '2026-05-29', 'name' => 'Eid ul-Adha (Day 3)',           'description' => 'National Holiday', 'is_recurring' => 0],
            ['date' => '2026-06-24', 'name' => 'Ashura (9 Muharram)',           'description' => 'National Holiday', 'is_recurring' => 0],
            ['date' => '2026-06-25', 'name' => 'Ashura (10 Muharram)',          'description' => 'National Holiday', 'is_recurring' => 0],
            ['date' => '2026-08-14', 'name' => 'Independence Day',              'description' => 'National Holiday', 'is_recurring' => 0],
            ['date' => '2026-08-26', 'name' => 'Eid Milad un-Nabi',             'description' => 'National Holiday', 'is_recurring' => 0],
            ['date' => '2026-11-09', 'name' => 'Iqbal Day',                     'description' => 'National Holiday', 'is_recurring' => 0],
            ['date' => '2026-12-25', 'name' => 'Quaid-e-Azam Day / Christmas',  'description' => 'National Holiday', 'is_recurring' => 0],
        ];

        foreach ($holidays as $holiday) {
            Holiday::updateOrCreate(
                ['date' => $holiday['date']],
                $holiday
            );
        }
    }
}
