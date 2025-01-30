<?php
namespace App\Services;

use Carbon\Carbon;

class LeaveCalculationService
{
    public function calculateLeaves($joinDate, $initialLeaveCount)
    {
        $now = Carbon::now();
        $joinDate = Carbon::parse($joinDate);
        $yearsOfService = $now->diffInYears($joinDate);
        
        // For employees beyond first year
        if ($yearsOfService >= 1) {
            return [
                'annual_leaves' => 14,
                'casual_leaves' => 7,
                'total_leaves' => 21,
                'remaining_leaves' => 21 - $initialLeaveCount
            ];
        }

        // For first year employees
        // Cast to integer to avoid float comparison
        $joinQuarter = (int)ceil($joinDate->month / 3);
        
        $annualLeaves = match($joinQuarter) {
            1 => 14, // Jan-Mar
            2 => 10, // Apr-Jun
            3 => 7,  // Jul-Sep
            4 => 4,  // Oct-Dec
            default => 0 // Add default case for safety
        };

        $totalLeaves = $annualLeaves + 7; // Adding casual leaves
        $remainingLeaves = $totalLeaves - $initialLeaveCount;

        return [
            'annual_leaves' => $annualLeaves,
            'casual_leaves' => 7,
            'total_leaves' => $totalLeaves,
            'remaining_leaves' => max(0, $remainingLeaves)
        ];
    }
}