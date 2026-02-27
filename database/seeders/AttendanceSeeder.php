<?php

namespace Database\Seeders;

use App\Models\Attendance;
use App\Models\Member;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class AttendanceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $members = Member::where('status', 'active')->get();
        
        // Generate attendance for the last 30 days
        for ($day = 29; $day >= 0; $day--) {
            $date = Carbon::now()->subDays($day);
            
            // Random members attend each day (max 80% of active members)
            $maxAttendees = min(rand(15, 30), $members->count());
            $attendingMembers = $members->random($maxAttendees);
            
            foreach ($attendingMembers as $member) {
                $checkIn = $date->copy()->setTime(rand(6, 20), rand(0, 59), 0);
                $checkOut = $checkIn->copy()->addHours(rand(1, 3))->addMinutes(rand(0, 59));
                
                Attendance::create([
                    'member_id' => $member->id,
                    'check_in_time' => $checkIn,
                    'check_out_time' => rand(0, 10) > 1 ? $checkOut : null, // 90% have check-out
                    'entry_method' => collect(['manual', 'card', 'qr'])->random(),
                    'created_at' => $checkIn,
                ]);
            }
        }
        
        // Add some check-ins for today without check-out
        $maxToday = min(rand(5, 10), $members->count());
        $todayMembers = $members->random($maxToday);
        foreach ($todayMembers as $member) {
            $checkIn = Carbon::now()->subHours(rand(1, 4))->subMinutes(rand(0, 59));
            
            Attendance::create([
                'member_id' => $member->id,
                'check_in_time' => $checkIn,
                'check_out_time' => null,
                'entry_method' => collect(['manual', 'card', 'qr'])->random(),
                'created_at' => $checkIn,
            ]);
        }
    }
}
