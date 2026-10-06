<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class FetchNationalHolidays extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'holidays:fetch {--year= : Optional year (informational only)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Seed Pakistan national holidays from HolidaySeeder';

    /**
     * Execute the console command.
     *
     * Note: previously this command fetched Indonesian holidays from
     * dayoffapi.vercel.app. The app is now used in Pakistan, so it instead
     * runs the HolidaySeeder which contains 2025 + 2026 Pakistan holidays.
     */
    public function handle()
    {
        $this->info('Seeding Pakistan national holidays...');
        $this->call('db:seed', ['--class' => 'HolidaySeeder', '--force' => true]);
        $this->info('Done. Update database/seeders/HolidaySeeder.php to add more years.');
        return self::SUCCESS;
    }
}
