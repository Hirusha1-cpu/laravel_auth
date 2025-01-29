<?php

namespace App\Http\Controllers;

use App\Models\LeaveRequest;
use Illuminate\Http\Request;

class LeaveRequestController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $leaveRequests = LeaveRequest::all();
        return response()->json($leaveRequests);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'date' => 'required|date',
            'reason' => 'required|string',
            'users_id' => 'required|exists:users,id',
            'mailed_status' => 'boolean',
            'accept_status' => 'in:pending,accepted,rejected',
            'not_accept_reason' => 'nullable|string',
            'updated_user_id' => 'nullable|exists:users,id',
        ]);

        $leaveRequest = LeaveRequest::create($validatedData);
        return response()->json($leaveRequest, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(LeaveRequest $leaveRequest)
    {
        return response()->json($leaveRequest);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, LeaveRequest $leaveRequest)
    {
        $validatedData = $request->validate([
            'date' => 'required|date',
            'reason' => 'required|string',
            'users_id' => 'required|exists:users,id',
            'mailed_status' => 'required|boolean',
            'accept_status' => 'required|in:pending,accepted,rejected',
            'not_accept_reason' => 'nullable|string',
            'updated_user_id' => 'nullable|exists:users,id',
        ]);

        $leaveRequest->update($validatedData);
        return response()->json($leaveRequest);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(LeaveRequest $leaveRequest)
    {
        $leaveRequest->delete();
        return response()->json(null, 204);
    }
}