<?php

namespace Database\Seeders;

use App\Models\InventoryItem;
use Illuminate\Database\Seeder;

class InventoryItemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $items = [
            // Supplements
            ['name' => 'Whey Protein 2kg', 'category' => 'Supplements', 'purchase_price' => 80000, 'selling_price' => 120000, 'quantity' => 25, 'reorder_level' => 10],
            ['name' => 'Creatine Monohydrate 500g', 'category' => 'Supplements', 'purchase_price' => 40000, 'selling_price' => 60000, 'quantity' => 30, 'reorder_level' => 15],
            ['name' => 'BCAA Powder 300g', 'category' => 'Supplements', 'purchase_price' => 35000, 'selling_price' => 55000, 'quantity' => 20, 'reorder_level' => 10],
            ['name' => 'Pre-Workout 300g', 'category' => 'Supplements', 'purchase_price' => 50000, 'selling_price' => 75000, 'quantity' => 15, 'reorder_level' => 8],
            ['name' => 'Multivitamin 60 tablets', 'category' => 'Supplements', 'purchase_price' => 20000, 'selling_price' => 35000, 'quantity' => 40, 'reorder_level' => 20],
            
            // Drinks
            ['name' => 'Energy Drink', 'category' => 'Drinks', 'purchase_price' => 1500, 'selling_price' => 3000, 'quantity' => 100, 'reorder_level' => 30],
            ['name' => 'Protein Shake Ready-to-Drink', 'category' => 'Drinks', 'purchase_price' => 3000, 'selling_price' => 5000, 'quantity' => 50, 'reorder_level' => 20],
            ['name' => 'Mineral Water', 'category' => 'Drinks', 'purchase_price' => 500, 'selling_price' => 1000, 'quantity' => 200, 'reorder_level' => 50],
            ['name' => 'Sports Drink', 'category' => 'Drinks', 'purchase_price' => 2000, 'selling_price' => 3500, 'quantity' => 80, 'reorder_level' => 30],
            
            // Accessories
            ['name' => 'Gym Gloves', 'category' => 'Accessories', 'purchase_price' => 15000, 'selling_price' => 25000, 'quantity' => 20, 'reorder_level' => 10],
            ['name' => 'Wrist Wraps', 'category' => 'Accessories', 'purchase_price' => 8000, 'selling_price' => 15000, 'quantity' => 25, 'reorder_level' => 10],
            ['name' => 'Lifting Belt', 'category' => 'Accessories', 'purchase_price' => 30000, 'selling_price' => 50000, 'quantity' => 15, 'reorder_level' => 5],
            ['name' => 'Shaker Bottle', 'category' => 'Accessories', 'purchase_price' => 5000, 'selling_price' => 10000, 'quantity' => 50, 'reorder_level' => 20],
            ['name' => 'Resistance Bands Set', 'category' => 'Accessories', 'purchase_price' => 20000, 'selling_price' => 35000, 'quantity' => 10, 'reorder_level' => 5],
            
            // Apparel
            ['name' => 'Gym T-Shirt (M)', 'category' => 'Apparel', 'purchase_price' => 15000, 'selling_price' => 30000, 'quantity' => 30, 'reorder_level' => 15],
            ['name' => 'Gym T-Shirt (L)', 'category' => 'Apparel', 'purchase_price' => 15000, 'selling_price' => 30000, 'quantity' => 35, 'reorder_level' => 15],
            ['name' => 'Gym Shorts (M)', 'category' => 'Apparel', 'purchase_price' => 18000, 'selling_price' => 35000, 'quantity' => 25, 'reorder_level' => 10],
            ['name' => 'Gym Shorts (L)', 'category' => 'Apparel', 'purchase_price' => 18000, 'selling_price' => 35000, 'quantity' => 30, 'reorder_level' => 10],
        ];
        
        foreach ($items as $index => $item) {
            InventoryItem::create([
                'supplier_id' => null,
                'name' => $item['name'],
                'sku' => 'SKU-' . str_pad($index + 1, 5, '0', STR_PAD_LEFT),
                'category' => $item['category'],
                'quantity' => $item['quantity'],
                'purchase_price' => $item['purchase_price'],
                'selling_price' => $item['selling_price'],
                'reorder_level' => $item['reorder_level'],
                'status' => 'active',
            ]);
        }
    }
}
