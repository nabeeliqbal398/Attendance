<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

/**
 * Seed the `regions` table from a gzipped SQL dump.
 *
 * The dump was originally generated against the legacy Indonesian schema
 * (`wilayah`, `kode`, `nama`). The seeder rewrites those identifiers on the
 * fly so the same dump still loads into the renamed schema (`regions`,
 * `code`, `name`) without re-zipping the file.
 */
class RegionSeeder extends Seeder
{
    public function run(): void
    {
        $path = database_path('data/regions.sql.gz');

        if (!File::exists($path)) {
            $this->command->error("Region data file not found at {$path}");
            return;
        }

        $this->command->info('Extracting and executing region data...');

        $sql = gzdecode(File::get($path));

        if ($sql === false) {
            $this->command->error('Failed to extract gzip file.');
            return;
        }

        // Translate legacy Indonesian identifiers in the dumped SQL.
        $sql = strtr($sql, [
            '`wilayah`' => '`regions`',
            'INSERT INTO wilayah' => 'INSERT INTO regions',
            'CREATE TABLE `wilayah`' => 'CREATE TABLE `regions`',
            'CREATE TABLE wilayah' => 'CREATE TABLE regions',
            '`kode`' => '`code`',
            '`nama`' => '`name`',
        ]);

        DB::unprepared($sql);

        $this->command->info('Regions table seeded successfully.');
    }
}
