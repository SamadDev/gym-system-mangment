<?php

namespace Database\Seeders;

use App\Models\MembershipPlan;
use Illuminate\Database\Seeder;

class MembershipPlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $plans = [
            [
                'name' => 'Daily Pass',
                'description' => 'Single day gym access',
                'duration_days' => 1,
                'price_male' => 5000,
                'price_female' => 5000,
                'class_limit' => 0,
                'personal_training_included' => false,
                'status' => 'active',
            ],
            [
                'name' => 'Weekly Pass',
                'description' => 'One week gym access',
                'duration_days' => 7,
                'price_male' => 30000,
                'price_female' => 25000,
                'class_limit' => 5,
                'personal_training_included' => false,
                'status' => 'active',
            ],
            [
                'name' => 'Monthly Basic',
                'description' => 'One month gym access with basic facilities',
                'duration_days' => 30,
                'price_male' => 100000,
                'price_female' => 80000,
                'class_limit' => 10,
                'personal_training_included' => false,
                'status' => 'active',
            ],
            [
                'name' => 'Monthly Premium',
                'description' => 'One month gym access with all facilities and classes',
                'duration_days' => 30,
                'price_male' => 150000,
                'price_female' => 120000,
                'class_limit' => null, // unlimited
                'personal_training_included' => true,
                'status' => 'active',
            ],
            [
                'name' => '3-Month Package',
                'description' => 'Three months gym access - Best value',
                'duration_days' => 90,
                'price_male' => 400000,
                'price_female' => 320000,
                'class_limit' => null,
                'personal_training_included' => true,
                'status' => 'active',
            ],
            [
                'name' => '6-Month Package',
                'description' => 'Six months gym access with personal trainer',
                'duration_days' => 180,
                'price_male' => 750000,
                'price_female' => 600000,
                'class_limit' => null,
                'personal_training_included' => true,
                'status' => 'active',
            ],
            [
                'name' => 'Annual VIP',
                'description' => 'One year unlimited access to all facilities',
                'duration_days' => 365,
                'price_male' => 1400000,
                'price_female' => 1100000,
                'class_limit' => null,
                'personal_training_included' => true,
                'status' => 'active',
            ],
        ];

        foreach ($plans as $plan) {
            MembershipPlan::create($plan);
        }
    }
}
