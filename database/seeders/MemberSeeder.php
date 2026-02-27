<?php

namespace Database\Seeders;

use App\Models\Member;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;

class MemberSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create();
        
        $bloodTypes = ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'];
        $genders = ['male', 'female'];
        
        for ($i = 1; $i <= 50; $i++) {
            $gender = $faker->randomElement($genders);
            
            Member::create([
                'member_code' => 'MEM-2026-' . str_pad($i, 4, '0', STR_PAD_LEFT),
                'first_name' => $faker->firstName($gender),
                'last_name' => $faker->lastName,
                'phone' => '+9647' . $faker->numerify('########'),
                'email' => $faker->unique()->safeEmail,
                'address' => $faker->address,
                'date_of_birth' => $faker->dateTimeBetween('-50 years', '-18 years')->format('Y-m-d'),
                'gender' => $gender,
                'emergency_contact' => $faker->name,
                'emergency_phone' => '+9647' . $faker->numerify('########'),
                'blood_type' => $faker->randomElement($bloodTypes),
                'status' => $faker->randomElement(['active', 'active', 'active', 'inactive']), // 75% active
            ]);
        }
    }
}
