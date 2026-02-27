<?php

namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            // Dashboard
            ['name' => 'view_dashboard', 'module' => 'Dashboard', 'description' => 'View dashboard'],
            
            // Members
            ['name' => 'members.view', 'module' => 'Members', 'description' => 'View members list'],
            ['name' => 'members.create', 'module' => 'Members', 'description' => 'Create new members'],
            ['name' => 'members.edit', 'module' => 'Members', 'description' => 'Edit member details'],
            ['name' => 'members.delete', 'module' => 'Members', 'description' => 'Delete members'],
            
            // Memberships
            ['name' => 'memberships.view', 'module' => 'Memberships', 'description' => 'View memberships'],
            ['name' => 'memberships.create', 'module' => 'Memberships', 'description' => 'Create memberships'],
            ['name' => 'memberships.edit', 'module' => 'Memberships', 'description' => 'Edit memberships'],
            ['name' => 'memberships.cancel', 'module' => 'Memberships', 'description' => 'Cancel memberships'],
            
            // Attendance
            ['name' => 'attendance.view', 'module' => 'Attendance', 'description' => 'View attendance records'],
            ['name' => 'attendance.manage', 'module' => 'Attendance', 'description' => 'Manage check-in/check-out'],
            
            // Payments
            ['name' => 'payments.view', 'module' => 'Payments', 'description' => 'View payments'],
            ['name' => 'payments.create', 'module' => 'Payments', 'description' => 'Create payments'],
            
            // Trainers
            ['name' => 'trainers.view', 'module' => 'Trainers', 'description' => 'View trainers'],
            ['name' => 'trainers.create', 'module' => 'Trainers', 'description' => 'Create trainers'],
            ['name' => 'trainers.edit', 'module' => 'Trainers', 'description' => 'Edit trainers'],
            ['name' => 'trainers.delete', 'module' => 'Trainers', 'description' => 'Delete trainers'],
            
            // Classes
            ['name' => 'classes.view', 'module' => 'Classes', 'description' => 'View classes'],
            ['name' => 'classes.create', 'module' => 'Classes', 'description' => 'Create classes'],
            ['name' => 'classes.edit', 'module' => 'Classes', 'description' => 'Edit classes'],
            ['name' => 'classes.delete', 'module' => 'Classes', 'description' => 'Delete classes'],
            
            // Equipment
            ['name' => 'equipment.view', 'module' => 'Equipment', 'description' => 'View equipment'],
            ['name' => 'equipment.create', 'module' => 'Equipment', 'description' => 'Create equipment'],
            ['name' => 'equipment.edit', 'module' => 'Equipment', 'description' => 'Edit equipment'],
            ['name' => 'equipment.delete', 'module' => 'Equipment', 'description' => 'Delete equipment'],
            
            // Inventory
            ['name' => 'inventory.view', 'module' => 'Inventory', 'description' => 'View inventory'],
            ['name' => 'inventory.manage', 'module' => 'Inventory', 'description' => 'Manage inventory'],
            
            // Expenses
            ['name' => 'expenses.view', 'module' => 'Expenses', 'description' => 'View expenses'],
            ['name' => 'expenses.create', 'module' => 'Expenses', 'description' => 'Create expenses'],
            ['name' => 'expenses.approve', 'module' => 'Expenses', 'description' => 'Approve expenses'],
            
            // Reports
            ['name' => 'reports.view', 'module' => 'Reports', 'description' => 'View reports'],
            
            // Users
            ['name' => 'users.view', 'module' => 'Users', 'description' => 'View users'],
            ['name' => 'users.create', 'module' => 'Users', 'description' => 'Create users'],
            ['name' => 'users.edit', 'module' => 'Users', 'description' => 'Edit users'],
            ['name' => 'users.delete', 'module' => 'Users', 'description' => 'Delete users'],
            
            // Roles
            ['name' => 'roles.view', 'module' => 'Roles', 'description' => 'View roles'],
            ['name' => 'roles.create', 'module' => 'Roles', 'description' => 'Create roles'],
            ['name' => 'roles.edit', 'module' => 'Roles', 'description' => 'Edit roles'],
            ['name' => 'roles.delete', 'module' => 'Roles', 'description' => 'Delete roles'],
        ];

        foreach ($permissions as $permission) {
            Permission::create($permission);
        }
    }
}
