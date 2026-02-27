<?php

namespace Database\Seeders;

use App\Models\Membership;
use App\Models\Member;
use App\Models\MembershipPlan;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class MembershipSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $members = Member::all();
        $plans = MembershipPlan::all();
        
        foreach ($members->take(40) as $member) { // Give 40 out of 50 members a membership
            $plan = $plans->random();
            $startDate = Carbon::now()->subDays(rand(0, 60));
            $endDate = $startDate->copy()->addDays($plan->duration_days);
            
            // Calculate price based on gender
            $price = $member->gender === 'male' ? $plan->price_male : $plan->price_female;
            
            Membership::create([
                'member_id' => $member->id,
                'membership_plan_id' => $plan->id,
                'start_date' => $startDate->format('Y-m-d'),
                'end_date' => $endDate->format('Y-m-d'),
                'amount_paid' => $price,
                'status' => $endDate->isFuture() ? 'active' : 'expired',
            ]);
        }
    }
}
