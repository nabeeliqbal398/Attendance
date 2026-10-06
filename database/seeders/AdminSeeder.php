<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        // Set SEED_ADMIN_PASSWORD in .env. Without it, seeding is only allowed locally.
        $password = env('SEED_ADMIN_PASSWORD');
        if (!$password) {
            if (!app()->environment('local', 'testing')) {
                $this->command?->warn('SEED_ADMIN_PASSWORD not set; skipping admin seed.');
                return;
            }
            $password = 'password';
        }

        $this->upsertAdmin('superadmin@example.com', 'Super Admin', 'superadmin', $password);
        $this->upsertAdmin('admin@example.com', 'Admin', 'admin', $password);
    }

    private function upsertAdmin(string $email, string $name, string $group, string $password): void
    {
        User::updateOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                'nip' => '0000000000000000',
                'phone' => '00000000000',
                'gender' => 'male',
                'address' => '',
                'birth_date' => null,
                'birth_place' => null,
                'group' => $group,
                'password' => Hash::make($password),
                'email_verified_at' => now(),
            ]
        );
    }
}
