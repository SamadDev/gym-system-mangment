<?php

namespace Database\Seeders;

use App\Models\Trainer;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;

class TrainerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create();
        
        $certifications = [
            'NASM Certified Personal Trainer',
            'ACE Fitness Instructor',
            'CrossFit Level 1 Trainer',
            'Yoga Alliance RYT-200',
            'First Aid & CPR Certified',
        ];
        
        for ($i = 1; $i <= 10; $i++) {
            $gender = $faker->randomElement(['male', 'female']);
            $selectedCerts = implode(', ', $faker->randomElements($certifications, rand(2, 4)));
            
            Trainer::create([
                'first_name' => $faker->firstName($gender),
                'last_name' => $faker->lastName,
                'phone' => '+9647' . $faker->numerify('########'),
                'email' => $faker->unique()->safeEmail,
                'certifications' => $selectedCerts,
                'hire_date' => $faker->dateTimeBetween('-5 years', '-6 months')->format('Y-m-d'),
                'salary' => $faker->randomFloat(2, 500000, 1500000),
                'commission_rate' => $faker->randomFloat(2, 5, 15),
                'status' => 'active',
            ]);
        }
    }
}
