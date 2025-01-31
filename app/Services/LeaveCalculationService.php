<?php
namespace App\Services;

use Carbon\Carbon;


class LeaveCalculationService {
    public function calculateLeaves($joinDate, $initialLeaveCount, $halfDayCount ) {
        $now = Carbon::now();
        $joinDate = Carbon::parse($joinDate);

        // Calculate years of service based on full years
        $yearsOfService = $now->year - $joinDate->year;

        // If the join date is in the future, return 0 for everything
        if ($joinDate->gt($now)) {
            return [
                'annual_leaves' => 0,
                'casual_leaves' => 0,
                'total_leaves' => 0,
                'remaining_leaves' => 0,
                'no_pay_count' => 0,
                'half_day_count' => 0,
                'total_half_days_allowed' => 0,
                'half_days_taken' => 0,
                'years_of_service' => 0,
                'join_quarter' => 0,
                'initial_month' => $joinDate->month,
                'full_day_equivalent_taken' => 0,
            ];
        }

        // Get join quarter
        $joinQuarter = (int)ceil($joinDate->month / 3);

        // Annual and casual leaves start from the next year
        $annualLeaves = 0;
        $casualLeaves = 0;
        if ($yearsOfService >= 1) {
            $annualLeaves = $this->calculateAnnualLeaves($yearsOfService, $joinQuarter);
            $casualLeaves = 7; // Casual leaves are fixed at 7
        }

        // Calculate half days
        $halfDayAllocation = $this->calculateHalfDays($joinDate, $now, $yearsOfService);

        // Total leaves (annual + casual)
        $totalLeaves = $annualLeaves + $casualLeaves;

        // Convert half days to full day equivalent for remaining calculation
        $fullDayEquivalentTaken = $initialLeaveCount + ($halfDayCount * 0.5);

        // Calculate remaining leaves
        $remainingLeaves = $totalLeaves - $fullDayEquivalentTaken;

        // Calculate no-pay days
        $noPayCount = $fullDayEquivalentTaken > $totalLeaves ? 
            $fullDayEquivalentTaken - $totalLeaves : 0;

        return [
            'annual_leaves' => $annualLeaves,
            'casual_leaves' => $casualLeaves,
            'total_leaves' => $totalLeaves,
            'remaining_leaves' => max(0, $remainingLeaves),
            'no_pay_count' => $noPayCount,
            'half_day_count' => $halfDayAllocation['remaining'],
            'total_half_days_allowed' => $halfDayAllocation['total'],
            'half_days_taken' => $halfDayCount,
            'years_of_service' => $yearsOfService,
            'join_quarter' => $joinQuarter,
            'initial_month' => $joinDate->month,
            'full_day_equivalent_taken' => $fullDayEquivalentTaken,
            '$halfDayCount'=>$halfDayCount
        ];
    }

    private function calculateAnnualLeaves($yearsOfService, $joinQuarter) {
        // For 2+ years of service, always get full 14 annual leaves
        if ($yearsOfService >= 2) {
            return 14;
        }

        // For second year (1-2 years), fixed 10 annual leaves
        if ($yearsOfService >= 1) {
            return 10;
        }

        // First year - no annual leaves
        return 0;
    }

    private function calculateHalfDays($joinDate, $now, $yearsOfService) {
        $totalAllowedHalfDays = 12; // Maximum half days per year

        // For employees who joined this year
        if ($yearsOfService === 0) {
            $remainingMonths = 12 - $joinDate->month + 1;
            $remainingHalfDays = $remainingMonths - ($now->month - $joinDate->month);
            return [
                'total' => $remainingMonths,
                'remaining' => max(0, $remainingHalfDays),
            ];
        }

        // For employees with 1+ years of service
        $remainingHalfDays = $totalAllowedHalfDays - $now->month + 1;
        return [
            'total' => $totalAllowedHalfDays,
            'remaining' => max(0, $remainingHalfDays),
        ];
    }
}
// namespace App\Services;

// use Carbon\Carbon;

// class LeaveCalculationService
// {
//     public function calculateLeaves($joinDate, $initialLeaveCount)
//     {
//         $now = Carbon::now();
//         $joinDate = Carbon::parse($joinDate);
//         $yearsOfService = $now->diffInYears($joinDate);
        
//         // For employees beyond first year
//         if ($yearsOfService >= 1) {
//             return [
//                 'annual_leaves' => 14,
//                 'casual_leaves' => 7,
//                 'total_leaves' => 21,
//                 'remaining_leaves' => 21 - $initialLeaveCount
//             ];
//         }

//         // For first year employees
//         // Cast to integer to avoid float comparison
//         $joinQuarter = (int)ceil($joinDate->month / 3);
        
//         $annualLeaves = match($joinQuarter) {
//             1 => 14, // Jan-Mar
//             2 => 10, // Apr-Jun
//             3 => 7,  // Jul-Sep
//             4 => 4,  // Oct-Dec
//             default => 0 // Add default case for safety
//         };

//         $totalLeaves = $annualLeaves + 7; // Adding casual leaves
//         $remainingLeaves = $totalLeaves - $initialLeaveCount;

//         return [
//             'annual_leaves' => $annualLeaves,
//             'casual_leaves' => 7,
//             'total_leaves' => $totalLeaves,
//             'remaining_leaves' => max(0, $remainingLeaves)
//         ];
//     }
// }