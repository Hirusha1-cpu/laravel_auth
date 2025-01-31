<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Services\LeaveCalculationService;
use App\Models\User;
use Illuminate\Http\Request;

class LeaveDetailsController extends Controller
{
    protected $leaveCalculationService;

    public function __construct(LeaveCalculationService $leaveCalculationService)
    {
        $this->leaveCalculationService = $leaveCalculationService;
    }

    public function getUserLeaveDetails(Request $request)
    {
        // Get user from token
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'status' => 0,
                'message' => 'User not found'
            ], 404);
        }

        $leaveDetails = $this->leaveCalculationService->calculateLeaves(
            $user->joinned_date,
            $user->leave_count,
            $user->half_day_count ?? 0
        );

        return response()->json([
            'status' => 1,
            'message' => 'Leave details fetched successfully',
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'joined_date' => $user->joinned_date,
                ],
                'leave_details' => $leaveDetails
            ]
        ]);
    }

    public function getAllUsersLeaveDetails()
    {
        // Keep this method the same if admin needs to see all users
        $users = User::all();
        $usersWithLeaveDetails = [];

        foreach ($users as $user) {
            $leaveDetails = $this->leaveCalculationService->calculateLeaves(
                $user->joinned_date,
                $user->leave_count,
                $user->half_day_count ?? 0
            );

            $usersWithLeaveDetails[] = [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'joined_date' => $user->joinned_date,
                ],
                'leave_details' => $leaveDetails
            ];
        }

        return response()->json([
            'status' => 1,
            'message' => 'All users leave details fetched successfully',
            'data' => $usersWithLeaveDetails
        ]);
    }
}