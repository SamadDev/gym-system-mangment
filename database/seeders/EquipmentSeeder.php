<?php

namespace Database\Seeders;

use App\Models\Equipment;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class EquipmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $equipment = [
            // Cardio Equipment
            ['name' => 'Treadmill Pro X1', 'category' => 'Cardio', 'purchase_price' => 2500000, 'condition' => 'excellent'],
            ['name' => 'Treadmill Pro X2', 'category' => 'Cardio', 'purchase_price' => 2500000, 'condition' => 'excellent'],
            ['name' => 'Elliptical Trainer E500', 'category' => 'Cardio', 'purchase_price' => 1800000, 'condition' => 'good'],
            ['name' => 'Stationary Bike B200', 'category' => 'Cardio', 'purchase_price' => 800000, 'condition' => 'excellent'],
            ['name' => 'Rowing Machine R100', 'category' => 'Cardio', 'purchase_price' => 1200000, 'condition' => 'good'],
            
            // Strength Equipment
            ['name' => 'Smith Machine', 'category' => 'Strength', 'purchase_price' => 3500000, 'condition' => 'excellent'],
            ['name' => 'Leg Press Machine', 'category' => 'Strength', 'purchase_price' => 2000000, 'condition' => 'good'],
            ['name' => 'Cable Crossover Station', 'category' => 'Strength', 'purchase_price' => 2800000, 'condition' => 'excellent'],
            ['name' => 'Chest Press Machine', 'category' => 'Strength', 'purchase_price' => 1500000, 'condition' => 'good'],
            ['name' => 'Lat Pulldown Machine', 'category' => 'Strength', 'purchase_price' => 1400000, 'condition' => 'excellent'],
            
            // Free Weights
            ['name' => 'Dumbbell Set (5-50kg)', 'category' => 'Free Weights', 'purchase_price' => 1000000, 'condition' => 'good'],
            ['name' => 'Olympic Barbell 20kg', 'category' => 'Free Weights', 'purchase_price' => 300000, 'condition' => 'excellent'],
            ['name' => 'Olympic Weight Plates Set', 'category' => 'Free Weights', 'purchase_price' => 800000, 'condition' => 'good'],
            ['name' => 'Kettlebell Set', 'category' => 'Free Weights', 'purchase_price' => 400000, 'condition' => 'excellent'],
            
            // Benches & Racks
            ['name' => 'Adjustable Bench AB-300', 'category' => 'Benches', 'purchase_price' => 500000, 'condition' => 'excellent'],
            ['name' => 'Flat Bench FB-200', 'category' => 'Benches', 'purchase_price' => 300000, 'condition' => 'good'],
            ['name' => 'Power Rack PR-500', 'category' => 'Racks', 'purchase_price' => 2500000, 'condition' => 'excellent'],
            ['name' => 'Squat Rack SR-300', 'category' => 'Racks', 'purchase_price' => 1500000, 'condition' => 'good'],
        ];
        
        foreach ($equipment as $index => $item) {
            $purchaseDate = Carbon::now()->subMonths(rand(6, 36));
            $lastMaintenance = Carbon::now()->subMonths(rand(1, 3));
            
            Equipment::create([
                'name' => $item['name'],
                'equipment_code' => 'EQ-2026-' . str_pad($index + 1, 4, '0', STR_PAD_LEFT),
                'category' => $item['category'],
                'purchase_date' => $purchaseDate,
                'purchase_price' => $item['purchase_price'],
                'condition' => $item['condition'],
                'last_maintenance_date' => $lastMaintenance,
                'next_maintenance_date' => $lastMaintenance->copy()->addMonths(3),
                'status' => 'active',
            ]);
        }
    }
}
