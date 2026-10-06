<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private array $names = [
        'Shift Pagi' => 'Morning Shift',
        'Shift Sore' => 'Afternoon Shift',
        'Shift Malam' => 'Night Shift',
    ];

    public function up(): void
    {
        foreach ($this->names as $old => $new) {
            DB::table('shifts')->where('name', $old)->update(['name' => $new]);
        }
    }

    public function down(): void
    {
        foreach ($this->names as $old => $new) {
            DB::table('shifts')->where('name', $new)->update(['name' => $old]);
        }
    }
};
