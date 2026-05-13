<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UmkmAuthSeeder extends Seeder
{
    /**
     * Seed demo users for UMKM auth and role access.
     */
    public function run(): void
    {
        $users = [
            [
                'name' => 'Admin UMKM',
                'username' => 'admin',
                'email' => 'admin@umkm.local',
                'employee_id' => 'ADM001',
                'role' => 'admin',
                'password' => 'admin123',
                'is_active' => true,
            ],
            [
                'name' => 'Petugas Verifikasi 1',
                'username' => 'verifikator1',
                'email' => 'verifikator1@umkm.local',
                'employee_id' => 'VER001',
                'role' => 'verifikator',
                'password' => 'verif123',
                'is_active' => true,
            ],
            [
                'name' => 'Viewer UMKM',
                'username' => 'viewer1',
                'email' => 'viewer1@umkm.local',
                'employee_id' => 'VIEW001',
                'role' => 'viewer',
                'password' => 'view123',
                'is_active' => true,
            ],
        ];

        foreach ($users as $payload) {
            User::query()->updateOrCreate(
                ['username' => $payload['username']],
                [
                    'name' => $payload['name'],
                    'email' => $payload['email'],
                    'employee_id' => $payload['employee_id'],
                    'role' => $payload['role'],
                    'is_active' => $payload['is_active'],
                    'password' => Hash::make($payload['password']),
                ]
            );
        }
    }
}
