<?php

namespace Database\Seeders;

use App\Models\Payment;
use App\Models\Membership;
use App\Models\User;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class PaymentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $memberships = Membership::with('member')->get();
        $user = User::first();
        
        foreach ($memberships as $index => $membership) {
            $paymentDate = Carbon::parse($membership->start_date)->addHours(rand(1, 12));
            
            Payment::create([
                'member_id' => $membership->member_id,
                'membership_id' => $membership->id,
                'payment_type' => 'membership',
                'amount' => $membership->amount_paid,
                'payment_method' => collect(['cash', 'card', 'bank_transfer'])->random(),
                'payment_date' => $paymentDate,
                'reference_number' => 'PAY-' . $paymentDate->format('Y-m') . '-' . str_pad($index + 1, 4, '0', STR_PAD_LEFT),
                'notes' => 'Membership payment',
                'status' => 'completed',
                'created_by' => $user->id,
            ]);
        }
        
        // Add some market sales
        $members = $memberships->pluck('member')->unique();
        foreach ($members->random(20) as $index => $member) {
            $paymentDate = Carbon::now()->subDays(rand(1, 30));
            
            Payment::create([
                'member_id' => $member->id,
                'membership_id' => null,
                'payment_type' => 'market_sale',
                'amount' => rand(5000, 50000),
                'payment_method' => collect(['cash', 'card'])->random(),
                'payment_date' => $paymentDate,
                'reference_number' => 'PAY-' . $paymentDate->format('Y-m') . '-' . str_pad(100 + $index, 4, '0', STR_PAD_LEFT),
                'notes' => 'Market purchase',
                'status' => 'completed',
                'created_by' => $user->id,
            ]);
        }
    }
}
