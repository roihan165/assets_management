<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $accounts = [
            ['name' => 'Super Admin', 'email' => 'admin@example.com', 'role' => 'admin'],
            ['name' => 'SPV Test', 'email' => 'spv@example.com', 'role' => 'atasan'],
            ['name' => 'Staff Test', 'email' => 'staff@example.com', 'role' => 'staff'],
        ];

        foreach ($accounts as $account) {
            $user = User::updateOrCreate(
                ['email' => $account['email']],
                [
                    'name' => $account['name'],
                    'password' => Hash::make('password123'),
                    'email_verified_at' => now(),
                ]
            );

            $user->syncRoles([$account['role']]);
        }
    }
}
