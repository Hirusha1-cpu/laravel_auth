<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Roles;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DepartmentsController extends Controller
{
    public function getDepartmentManagers(Request $request)
    {
        try {
            // Get the authenticated user
            $user = Auth::user();
            
            if (!$user->department_id) {
                return response()->json([
                    'status' => 0,
                    'message' => 'User is not assigned to any department',
                    'data' => null
                ], 404);
            }

            // Get manager role ID
            $managerRole = Roles::where('designation', 'manager')->first();
            
            if (!$managerRole) {
                return response()->json([
                    'status' => 0,
                    'message' => 'Manager role not found',
                    'data' => null
                ], 404);
            }

            // Get all managers in the user's department
            $departmentManagers = User::where('department_id', $user->department_id)
                ->where('role_id', $managerRole->id)
                ->select([
                    'id',
                    'name',
                    'email',
                    'department_id',
                    'role_id'
                ])
                ->with(['department:id,name', 'role:id,designation'])
                ->get();

            return response()->json([
                'status' => 1,
                'message' => 'Department managers retrieved successfully',
                'data' => [
                    'department' => [
                        'id' => $user->department->id,
                        'name' => $user->department->name
                    ],
                    'managers' => $departmentManagers
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 0,
                'message' => 'Error retrieving department managers',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
