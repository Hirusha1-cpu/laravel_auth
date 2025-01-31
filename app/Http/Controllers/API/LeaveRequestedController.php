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
     * Display a listing of the resource.
     */
    public function index()
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
            'date' => 'sometimes|date', // 'sometimes' means the field is optional
            'reason' => 'sometimes|string',
            'mailed_status' => 'sometimes|boolean',
            'accept_status' => 'sometimes|in:pending,accepted,rejected',
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
