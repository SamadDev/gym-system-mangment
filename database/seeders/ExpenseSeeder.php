<?php

namespace Database\Seeders;

use App\Models\Expense;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ExpenseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::first();
        
        // First create expense categories
        $categories = [
            ['name' => 'Utilities', 'description' => 'Electricity, water, internet', 'status' => 'active'],
            ['name' => 'Salaries', 'description' => 'Staff salaries and wages', 'status' => 'active'],
            ['name' => 'Maintenance', 'description' => 'Equipment and facility maintenance', 'status' => 'active'],
            ['name' => 'Supplies', 'description' => 'Cleaning and general supplies', 'status' => 'active'],
            ['name' => 'Marketing', 'description' => 'Advertising and promotions', 'status' => 'active'],
            ['name' => 'Rent', 'description' => 'Building rent', 'status' => 'active'],
            ['name' => 'Insurance', 'description' => 'Business insurance', 'status' => 'active'],
        ];
        
        foreach ($categories as $category) {
            DB::table('expense_categories')->insert(array_merge($category, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }
        
        $categoryIds = DB::table('expense_categories')->pluck('id')->toArray();
        
        // Create expenses for the last 3 months
        $expenses = [
            // Recurring monthly expenses
            ['category_id' => 1, 'amount' => 500000, 'description' => 'Monthly electricity bill', 'payment_method' => 'bank_transfer'],
            ['category_id' => 1, 'amount' => 100000, 'description' => 'Monthly water bill', 'payment_method' => 'bank_transfer'],
            ['category_id' => 1, 'amount' => 150000, 'description' => 'Monthly internet service', 'payment_method' => 'bank_transfer'],
            ['category_id' => 2, 'amount' => 8000000, 'description' => 'Monthly staff salaries', 'payment_method' => 'bank_transfer'],
            ['category_id' => 6, 'amount' => 3000000, 'description' => 'Monthly rent payment', 'payment_method' => 'bank_transfer'],
            
            // One-time expenses
            ['category_id' => 3, 'amount' => 450000, 'description' => 'Treadmill repair', 'payment_method' => 'cash'],
            ['category_id' => 3, 'amount' => 300000, 'description' => 'Equipment maintenance', 'payment_method' => 'cash'],
            ['category_id' => 4, 'amount' => 200000, 'description' => 'Cleaning supplies purchase', 'payment_method' => 'card'],
            ['category_id' => 4, 'amount' => 180000, 'description' => 'Paper towels and toiletries', 'payment_method' => 'cash'],
            ['category_id' => 5, 'amount' => 800000, 'description' => 'Social media advertising', 'payment_method' => 'card'],
            ['category_id' => 5, 'amount' => 350000, 'description' => 'Promotional flyers printing', 'payment_method' => 'cash'],
            ['category_id' => 7, 'amount' => 1200000, 'description' => 'Annual business insurance', 'payment_method' => 'bank_transfer'],
        ];
        
        // Create expenses for last 3 months
        for ($month = 2; $month >= 0; $month--) {
            foreach ($expenses as $expense) {
                $expenseDate = Carbon::now()->subMonths($month)->day(rand(1, 28));
                
                Expense::create([
                    'expense_category_id' => $expense['category_id'],
                    'amount' => $expense['amount'],
                    'expense_date' => $expenseDate,
                    'description' => $expense['description'] . ' - ' . $expenseDate->format('F Y'),
                    'payment_method' => $expense['payment_method'],
                    'approved_by' => $user->id,
                    'created_by' => $user->id,
                    'status' => 'approved',
                ]);
            }
        }
    }
}
