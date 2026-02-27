<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            PermissionSeeder::class,
            UserSeeder::class,
            MembershipPlanSeeder::class,
            MemberSeeder::class,
            MembershipSeeder::class,
            TrainerSeeder::class,
            AttendanceSeeder::class,
            PaymentSeeder::class,
            InvoiceSeeder::class,
            EquipmentSeeder::class,
            InventoryItemSeeder::class,
            ExpenseSeeder::class,
        ]);
    }
}
