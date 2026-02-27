<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            [
                'name' => 'Super Admin',
                'description' => 'Full system access with all permissions',
                'permissions' => json_encode(['*']), // All permissions
                'status' => 'active',
            ],
            [
                'name' => 'Manager',
                'description' => 'Gym manager with administrative access',
                'permissions' => json_encode([
                    'members.view', 'members.create', 'members.edit', 'members.delete',
                    'memberships.view', 'memberships.create', 'memberships.edit',
                    'attendance.view', 'attendance.manage',
                    'payments.view', 'payments.create',
                    'trainers.view', 'trainers.create', 'trainers.edit',
                    'equipment.view', 'equipment.create', 'equipment.edit',  
                    'inventory.view', 'inventory.manage',
                    'expenses.view', 'expenses.create', 'expenses.approve',
                    'reports.view',
                ]),
                'status' => 'active',
            ],
            [
                'name' => 'Receptionist',
                'description' => 'Front desk staff for check-ins and basic operations',
                'permissions' => json_encode([
                    'members.view', 'members.create', 'members.edit',
                    'memberships.view', 'memberships.create',
                    'attendance.view', 'attendance.manage',
                    'payments.view', 'payments.create',
                ]),
                'status' => 'active',
            ],
            [
                'name' => 'Trainer',
                'description' => 'Fitness trainer with limited access',
                'permissions' => json_encode([
                    'members.view',
                    'attendance.view',
                    'classes.view',
                ]),
                'status' => 'active',
            ],
        ];

        foreach ($roles as $role) {
            Role::create($role);
        }
    }
}
