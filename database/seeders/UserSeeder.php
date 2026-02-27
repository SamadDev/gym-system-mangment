<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $superAdminRole = Role::where('name', 'Super Admin')->first();
        $managerRole = Role::where('name', 'Manager')->first();
        $receptionistRole = Role::where('name', 'Receptionist')->first();

        $users = [
            [
                'name' => 'Admin User',
                'email' => 'admin@gym.com',
                'password' => Hash::make('password123'),
                'role_id' => $superAdminRole?->id,
                'phone' => '+9647501234567',
                'status' => 'active',
            ],
            [
                'name' => 'Manager User',
                'email' => 'manager@gym.com',
                'password' => Hash::make('password123'),
                'role_id' => $managerRole?->id,
                'phone' => '+9647501234568',
                'status' => 'active',
            ],
            [
                'name' => 'Receptionist User',
                'email' => 'receptionist@gym.com',
                'password' => Hash::make('password123'),
                'role_id' => $receptionistRole?->id,
                'phone' => '+9647501234569',
                'status' => 'active',
            ],
        ];

        foreach ($users as $user) {
            User::create($user);
        }
    }
}
