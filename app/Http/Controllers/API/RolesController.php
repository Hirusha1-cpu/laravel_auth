<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Roles;
use Illuminate\Support\Str;
use Illuminate\Http\Request;

class RolesController extends Controller
{
    public function index()
    {
        $roles = Roles::all();
        return response()->json([
            'status' => 1,
            'data' => $roles
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'designation' => 'required|string|max:255',
        ]);

        try {
            $role = Roles::create([
                'designation' => $request->designation,
                'slug' => Str::slug($request->designation)
            ]);

            return response()->json([
                'status' => 1,
                'message' => 'Role created successfully',
                'data' => $role
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 0,
                'message' => 'Error creating role',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
