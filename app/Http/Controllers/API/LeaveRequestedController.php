<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\LeaveRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class LeaveRequestedController extends Controller
{
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
        // Validate the request
        $validator = Validator::make($request->all(), [
            'date' => 'required|date',
            'reason' => 'required|string',
            'users_id' => 'required|exists:users,id',
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

        // Create the leave request
        $leaveRequest = LeaveRequest::create($request->all());

        return response()->json([
            "status" => 1,
            "message" => "Leave request created successfully",
            "data" => $leaveRequest
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
      
        $leaveRequest = LeaveRequest::find($id);

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
    public function update(Request $request, $id)
    {
        $leaveRequest = LeaveRequest::find($id);
        if (!$leaveRequest) {
            return response()->json([
                "status" => 0,
                "message" => "Leave request not found"
            ], 404);
        }
    
        $validator = Validator::make($request->all(), [
            'date' => 'required|date',
            'reason' => 'required|string',
            'users_id' => 'required|exists:users,id',
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
    
        // Update the leave request
        $leaveRequest->update($request->all());  // Simplified update
    
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
        $leaveRequest = LeaveRequest::find($id);

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
