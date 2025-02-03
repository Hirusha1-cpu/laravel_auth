<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Mail\LeaveRequestMail;
use App\Models\LeaveRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class LeaveRequestedController extends Controller
{
    private function sendLeaveRequestEmail($leaveRequest)
    {
        // Get user details
        $user = User::find($leaveRequest->users_id);

        $leaveData = [
            'user_name' => $user->name,
            'date' => $leaveRequest->date,
            'reason' => $leaveRequest->reason,
            'status' => $leaveRequest->accept_status
        ];

        Mail::to('hirushafernando121@gmail.com')
            ->send(new LeaveRequestMail($leaveData));
    }
      /**
     * Get leave history for all users with filtering options
     */
    public function getAllUsersLeaveHistory(Request $request)
    {
        // Initialize query builder
        $query = LeaveRequest::with(['user:id,name,email', 'updatedUser:id,name']);

        // Apply date range filter if provided
        if ($request->has('start_date') && $request->has('end_date')) {
            $query->whereBetween('date', [$request->start_date, $request->end_date]);
        }

        // Apply status filter if provided
        if ($request->has('status') && in_array($request->status, ['pending', 'accepted', 'rejected'])) {
            $query->where('accept_status', $request->status);
        }

        // Get all leave requests ordered by date
        $leaveHistory = $query->orderBy('date', 'desc')
            ->get()
            ->groupBy('users_id')
            ->map(function ($userLeaves) {
                $user = $userLeaves->first()->user;
                return [
                    'user_info' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email
                    ],
                    'leave_count' => [
                        'total' => $userLeaves->count(),
                        'accepted' => $userLeaves->where('accept_status', 'accepted')->count(),
                        'rejected' => $userLeaves->where('accept_status', 'rejected')->count(),
                        'pending' => $userLeaves->where('accept_status', 'pending')->count()
                    ],
                    'leave_requests' => $userLeaves->map(function ($leave) {
                        return [
                            'id' => $leave->id,
                            'date' => $leave->date,
                            'reason' => $leave->reason,
                            'status' => $leave->accept_status,
                            'not_accept_reason' => $leave->not_accept_reason,
                            'updated_by' => $leave->updatedUser ? $leave->updatedUser->name : null,
                            'created_at' => $leave->created_at,
                            'updated_at' => $leave->updated_at
                        ];
                    })->toArray()
                ];
            })->values();

        // Calculate overall statistics
        $totalStats = [
            'total_users' => $leaveHistory->count(),
            'total_leaves' => $leaveHistory->sum('leave_count.total'),
            'total_accepted' => $leaveHistory->sum('leave_count.accepted'),
            'total_rejected' => $leaveHistory->sum('leave_count.rejected'),
            'total_pending' => $leaveHistory->sum('leave_count.pending')
        ];

        return response()->json([
            "status" => 1,
            "message" => "All users leave history fetched successfully",
            "summary" => $totalStats,
            "data" => $leaveHistory
        ]);
    }
    /**
     * Get leave history for a specific user
     */
    public function getUserLeaveHistory($userId)
    {
        // Check if user exists
        $user = User::find($userId);
        
        if (!$user) {
            return response()->json([
                "status" => 0,
                "message" => "User not found"
            ], 404);
        }

        // Get leave requests for the user with user details and updated user details
        $leaveHistory = LeaveRequest::where('users_id', $userId)
            ->with(['user:id,name,email', 'updatedUser:id,name'])
            ->orderBy('date', 'desc')
            ->get()
            ->map(function ($leave) {
                return [
                    'id' => $leave->id,
                    'date' => $leave->date,
                    'reason' => $leave->reason,
                    'status' => $leave->accept_status,
                    'not_accept_reason' => $leave->not_accept_reason,
                    'user_name' => $leave->user->name,
                    'user_email' => $leave->user->email,
                    'updated_by' => $leave->updatedUser ? $leave->updatedUser->name : null,
                    'created_at' => $leave->created_at,
                    'updated_at' => $leave->updated_at
                ];
            });

        return response()->json([
            "status" => 1,
            "message" => "User leave history fetched successfully",
            "data" => [
                "user" => [
                    "id" => $user->id,
                    "name" => $user->name,
                    "email" => $user->email
                ],
                "leave_history" => $leaveHistory
            ]
        ]);
    }

    /**
     * Get leave history statistics for a specific user
     */
    public function getUserLeaveStats($userId)
    {
        // Check if user exists
        $user = User::find($userId);
        
        if (!$user) {
            return response()->json([
                "status" => 0,
                "message" => "User not found"
            ], 404);
        }

        // Get statistics
        $stats = [
            'total_leaves' => LeaveRequest::where('users_id', $userId)->count(),
            'accepted_leaves' => LeaveRequest::where('users_id', $userId)
                ->where('accept_status', 'accepted')
                ->count(),
            'rejected_leaves' => LeaveRequest::where('users_id', $userId)
                ->where('accept_status', 'rejected')
                ->count(),
            'pending_leaves' => LeaveRequest::where('users_id', $userId)
                ->where('accept_status', 'pending')
                ->count(),
        ];

        return response()->json([
            "status" => 1,
            "message" => "User leave statistics fetched successfully",
            "data" => $stats
        ]);
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = LeaveRequest::with(['user:id,name,email', 'managers:id,name,email']);

        // Apply date range filter if provided
        if ($request->has('start_date') && $request->has('end_date')) {
            $query->whereBetween('start_date', [$request->start_date, $request->end_date]);
        }

        // Apply leave type filter if provided
        if ($request->has('leave_type')) {
            $query->where('leave_type', $request->leave_type);
        }

        $leaveRequests = $query->get();

        return response()->json([
            "status" => 1,
            "message" => "Leave requests fetched successfully",
            "data" => $leaveRequests
        ]);
    }
    public function index1()
    {
        $leaveRequests = LeaveRequest::all();

        return response()->json([
            "status" => 1,
            "message" => "Leave requests fetched successfully",
            "data" => $leaveRequests
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Get the authenticated user's ID
        $userId = Auth::id();

        // Validate the request
        $validator = Validator::make($request->all(), [
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'leave_type' => 'required|in:annual,casual',
            'reason' => 'required|string',
            'mailed_status' => 'boolean',
            'accept_status' => 'in:pending,accepted,rejected',
            'not_accept_reason' => 'nullable|string',
            'updated_user_id' => 'nullable|exists:users,id',
            'manager_ids' => 'required|array',
            'manager_ids.*' => 'exists:users,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                "status" => 0,
                "message" => "Validation error",
                "data" => $validator->errors()->all()
            ], 422);
        }

        // Create the leave request
        $leaveRequest = LeaveRequest::create([
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'leave_type' => $request->leave_type,
            'day_type' => $request->day_type,
            'reason' => $request->reason,
            'users_id' => $userId,
            'mailed_status' => $request->mailed_status ?? false,
            'accept_status' => $request->accept_status ?? 'pending',
            'not_accept_reason' => $request->not_accept_reason,
            'updated_user_id' => $request->updated_user_id
        ]);

        // Attach managers
        $leaveRequest->managers()->attach($request->manager_ids);

        // Load the managers relation for the response
        $leaveRequest->load('managers:id,name,email');

        return response()->json([
            "status" => 1,
            "message" => "Leave request created successfully",
            "data" => $leaveRequest
        ], 201);
    }
    public function store1(Request $request)
    {
        // Get the authenticated user's ID
        $userId = Auth::id();

        // Validate the request
        $validator = Validator::make($request->all(), [
            'date' => 'required|date',
            'reason' => 'required|string',
            'mailed_status' => 'boolean',
            'accept_status' => 'in:pending,accepted,rejected',
            'not_accept_reason' => 'nullable|string',
            'updated_user_id' => 'nullable|exists:users,id',
        ]);

        // If validation fails, return error response
        if ($validator->fails()) {
            return response()->json([
                "status" => 0,
                "message" => "Validation error",
                "data" => $validator->errors()->all()
            ], 422);
        }

        // Create the leave request with the authenticated user's ID
        $leaveRequest = LeaveRequest::create([
            ...$request->all(),
            'users_id' => $userId
        ]);

        return response()->json([
            "status" => 1,
            "message" => "Leave request created successfully",
            "data" => $leaveRequest
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show()
    {

        // Get the authenticated user's ID
        $userId = Auth::id();
    
        // Find the leave request
        // $leaveRequest = LeaveRequest::find($id);
        $leaveRequest = LeaveRequest::where('users_id', $userId)->get();

        if (!$leaveRequest) {
            return response()->json([
                "status" => 0,
                "message" => "Leave request not found"
            ], 404);
        }

        return response()->json([
            "status" => 1,
            "message" => "Leave request fetched successfully",
            "data" => $leaveRequest
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request,$id)
    {
        // Get the authenticated user's ID
        $userId = Auth::id();
    
        // Find the leave request
        // $leaveRequest = LeaveRequest::find($id);
        // $leaveRequest = LeaveRequest::where('users_id', $userId)->get();
        $leaveRequest = LeaveRequest::where('users_id', $userId)->where('id', $id)->first();

    
        // If leave request not found, return error response
        if (!$leaveRequest) {
            return response()->json([
                "status" => 0,
                "message" => "Leave request not found"
            ], 404);
        }
    
        // Validate the request
        $validator = Validator::make($request->all(), [
            'date' => 'nullable|date', // 'sometimes' means the field is optional
            'reason' => 'nullable|string',
            'mailed_status' => 'nullable|boolean',
            'accept_status' => 'nullable|in:pending,accepted,rejected',
            'not_accept_reason' => 'nullable|string',
        ]);
    
        // If validation fails, return error response
        if ($validator->fails()) {
            return response()->json([
                "status" => 0,
                "message" => "Validation error",
                "data" => $validator->errors()->all()
            ], 422);
        }
    
        // Update the leave request
        $leaveRequest->update([
            'date' => $request->input('date', $leaveRequest->date), // Use existing value if not provided
            'reason' => $request->input('reason', $leaveRequest->reason),
            'mailed_status' => $request->input('mailed_status', $leaveRequest->mailed_status),
            'accept_status' => $request->input('accept_status', $leaveRequest->accept_status),
            'not_accept_reason' => $request->input('not_accept_reason', $leaveRequest->not_accept_reason),
            'updated_user_id' => $userId, // Set the updated_user_id to the authenticated user's ID
        ]);
    
        return response()->json([
            "status" => 1,
            "message" => "Leave request updated successfully",
            "data" => $leaveRequest
        ]);
    }
    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $userId = Auth::id();
        // $leaveRequest = LeaveRequest::find($id);
        $leaveRequest = LeaveRequest::where('users_id', $userId)->where('id', $id)->first();


        // If leave request not found, return error response
        if (!$leaveRequest) {
            return response()->json([
                "status" => 0,
                "message" => "Leave request not found"
            ], 404);
        }

        // Delete the leave request
        $leaveRequest->delete();

        return response()->json([
            "status" => 1,
            "message" => "Leave request deleted successfully"
        ]);
    }
}
