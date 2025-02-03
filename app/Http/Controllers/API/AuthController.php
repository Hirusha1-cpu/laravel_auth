<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Roles;
use App\Models\User;
use App\Services\LeaveCalculationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    /**
     *@return
     * 
     */
    protected $leaveCalculationService;

    public function __construct(LeaveCalculationService $leaveCalculationService)
    {
        $this->leaveCalculationService = $leaveCalculationService;
    }

    public function register12(Request $request)
    {
        Log::info($request->all());

        $validator = Validator::make($request->all(), [
            "name" => "required|string",
            "email" => "required|email|unique:users,email",
            "password" => "required|min:8",
            "confirm_password" => "required|same:password",
            "role_id" => "required|exists:roles,id",
            "joinned_date" => "required|date|before:today",
            "leave_count" => "nullable|integer",
            "finger_printid" => "nullable|string",
            "half_day_count" => "nullable|integer",
            "isManager" => "required|boolean",
            "selectedManager" => "nullable|exists:users,id"
        ]);

        if ($validator->fails()) {
            return response()->json([
                "status" => 0,
                "message" => "Validation error",
                "data" => $validator->errors()->all()
            ], 422);
        }

        try {
            DB::beginTransaction();
            $roleCheck = Roles::where('designation', 'CEO')->first();
            Log::info('Role Check:', ['role' => $roleCheck]);
            // Find CEO using role relationship
            if ($request->isManager) {
                // $ceo = User::whereHas('role', function ($query) {
                //     $query->where('designation', 'CEO');
                // })->first();
                $ceo = Roles::where('designation', 'CEO')->first();
                Log::info('CEO found:', ['ceo' => $ceo]);
                $assignedManager = $ceo ? $ceo->id : null;
            } else {
                $assignedManager = $request->selectedManager;
            }

            Log::info('Assigned Manager:', ['manager_id' => $assignedManager]);

            $user = User::create([
                "name" => $request->name,
                "email" => $request->email,
                "password" => bcrypt($request->password),
                "role_id" => $request->role_id,
                "joinned_date" => $request->joinned_date,
                "leave_count" => $request->leave_count,
                "finger_printid" => $request->finger_printid,
                "half_day_count" => $request->half_day_count,
                "assigned_manager" => $assignedManager,
                "account_status" => "Pending"
            ]);

            // Generate token for the new user
            $token = $user->createToken('auth-token')->plainTextToken;

            DB::commit();



            // return response()->json([
            //     "status" => 1,
            //     "message" => "User registered wait for approval",
            //     "data" => $user,
            //     "token" => $token
            // ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Registration error:', ['error' => $e->getMessage()]);

            return response()->json([
                "status" => 0,
                "message" => "Registration failed",
                "error" => $e->getMessage()
            ], 500);
        }
    }
    public function register(Request $request)
    {
        Log::info($request);
        $validator = Validator::make($request->all(), [
            "name" => "required|string",
            "email" => "required|email|unique:users,email",
            "password" => "required|min:8",
            "confirm_password" => "required|same:password",
            "role_id" => "nullable|exists:roles,id",
            "department_id" => "nullable|exists:departments,id",  // Add this
            "joinned_date" => "required|date|before:today",
            "leave_count" => "nullable|integer",
            "finger_printid" => "nullable|string",
            "half_day_count" => "nullable|integer",
            "isManager" => "required|boolean",
            "selectedManager" => "nullable|exists:users,id"
        ]);
        if ($validator->fails()) {
            return response()->json([
                "status" => 0,
                "message" => "Validation error",
                "data" => $validator->errors()->all()
            ], 422);
        }

        Log::info($request->selectedManager);

        if ($request->isManager) {
            // $ceo = User::whereHas('role', function ($query) {
            //     $query->where('designation', 'CEO');
            // })->first();
            $ceo = Roles::where('designation', 'ceo')->first();
            Log::info('CEO found:', ['ceo' => $ceo]);
            $assignedManager = $ceo ? $ceo->id : null;
        } else {
            $assignedManager = $request->selectedManager;
        }

        Log::info($assignedManager);
        $user = User::create([
            "name" => $request->name,
            "email" => $request->email,
            "password" => bcrypt($request->password),
            "role_id" => $request->role_id,
            "department_id" => $request->department_id,  // Add this
            "joinned_date" => $request->joinned_date,
            "leave_count" => $request->leave_count,
            "finger_printid" => $request->finger_printid,
            "half_day_count" => $request->half_day_count,
            "assigned_manager" => $assignedManager,
            "account_status" => "Pending"
        ]);


        $leaves = $this->leaveCalculationService->calculateLeaves(
            $request->joinned_date,
            $request->leave_count,
            $user->half_day_count ?? 0
        );

        $response = [
            "user_id" => $user->id,
            "name" => $user->name,
            "email" => $user->email,
            "role" => [
                "id" => $user->role_id,
                "designation" => $user->role->designation,
            ],
            "department" => $user->department ? [  
                "id" => $user->department->id,
                "name" => $user->department->name,
            ] : null,
            "joinned_date" => $user->joinned_date,
            "leave_count" => $user->leave_count,
            "leaves" => $leaves,
            "assigned_manager" => $user->assigned_manager,
            "account_status" => "Pending"
        ];

        return response()->json([
            "status" => 1,
            "message" => "User registered wait for approval",
            "data" => $response
        ], 201);
    }

    public function approveUser(Request $request, $id)
    {
        // Get the authenticated manager
        $manager = Auth::user();

        // Find the user by ID
        $user = User::find($id);

        // Check if user exists and is pending approval
        if (!$user || $user->account_status !== "Pending") {
            return response()->json([
                "status" => 0,
                "message" => "User not found or already approved/rejected"
            ], 404);
        }

        // Check if the manager is authorized to approve this user
        if ($user->assigned_manager !== $manager->id) {
            return response()->json([
                "status" => 0,
                "message" => "Unauthorized. You are not assigned to approve this user."
            ], 403);
        }

        // Update the account status to "Approved"
        $user->update(["account_status" => "Approved"]);

        return response()->json([
            "status" => 1,
            "message" => "User approved successfully",
            "data" => [
                "id" => $user->id,
                "name" => $user->name,
                "email" => $user->email,
                "account_status" => $user->account_status
            ]
        ]);
    }

    public function register122(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "name" => "required|string",
            "email" => "required|email|unique:users,email", // Ensures email is valid & unique
            "password" => "required|min:8", // Minimum 8 characters
            "confirm_password" => "required|same:password",
            "role_id" => "required|exists:roles,id",
            "joinned_date" => "required|date|before:today", // Ensures it's a past date
            "leave_count" => "nullable|integer",
            "finger_printid" => "nullable|string",
            "half_day_count" => "nullable|integer"
        ]);

        if ($validator->fails()) {
            return response()->json([
                "status" => 0,
                "message" => "validation error",
                "data" => $validator->errors()->all()
            ]);
        }

        $user = User::create([
            "name" => $request->name,
            "email" => $request->email,
            "password" => bcrypt($request->password),
            "role_id" => $request->role_id,
            "joinned_date" => $request->joinned_date,
            "leave_count" => $request->leave_count,
            "finger_printid" => $request->finger_printid,
            "half_day_count" => $request->half_day_count
        ]);

        $leaves = $this->leaveCalculationService->calculateLeaves(
            $request->joinned_date,
            $request->leave_count,
            $user->half_day_count ?? 0  // Add this to your users table
        );

        // $response["token"] = $user->createToken("MyApp")->plainTextToken;
        $response["name"] = $user->name;
        $response["email"] = $user->email;
        $response["roles"] = $user->role_id;
        $response["leaves"] = $leaves;

        return response()->json([
            "status" => 1,
            "message" => "User registered",
            "data" => $response
        ]);
    }

    public function getManagersDetails()
    {

        $user = Auth::user();
        $token = $user->tokens()->first()->token;
        Log::info($token);

        // Fetch all users whose role's designation is "Manager"
        $managers = User::whereHas('role', function ($query) {
            $query->where('designation', 'Manager');
        })->with('role')->get();;

        if ($managers->isEmpty()) {
            return response()->json([
                "status" => 0,
                "message" => "No managers found",
                "manager_details" => []
            ], 404);
        }

        return response()->json([
            "status" => 1,
            "message" => "Managers fetched successfully",
            "manager_details" => $managers
        ]);
    }

    public function login(Request $request)
    {
        Log::info($request);
        if (Auth::attempt(["email" => $request->email, "password" => $request->password])) {
            $user = Auth::user();

            // Check if the user's account status is "Approved"
            if ($user->account_status !== 'Approved') {
                return response()->json([
                    "status" => 0,
                    "message" => "Account not approved. Please contact the administrator.",
                    "data" => null,
                ], 403); // Forbidden status
            }

            $leaves = $this->leaveCalculationService->calculateLeaves(
                $user->joinned_date,
                $user->leave_count,
                $user->half_day_count ?? 0
            );

            $response["id"] = $user->id;
            $response["name"] = $user->name;
            $response["email"] = $user->email;
            $response["role"] = [
                'id' => $user->role_id,
                'designation' => $user->role->designation
            ];
            $response["joinned_date"] = $user->joinned_date;
            $response["leave_count"] = $user->leave_count;
            $response["account_status"] = $user->account_status;
            $response["leaves"] = $leaves;
            $response["token"] = $user->createToken("MyApp")->plainTextToken;
            Log::info($response);

            return response()->json([
                "status" => 1,
                "message" => "Login successful",
                "user" => $response
            ]);
        }

        return response()->json([
            "status" => 0,
            "message" => "Authentication error",
            "data" => null,
        ], 401);
    }

    public function login1(Request $request)
    {
        if (Auth::attempt(["email" => $request->email, "password" => $request->password])) {
            $user = Auth::user();

            $leaves = $this->leaveCalculationService->calculateLeaves(
                $user->joinned_date,
                $user->leave_count,
                $user->half_day_count ?? 0  // Add this to your users table
            );

            $response["name"] = $user->name;
            $response["email"] = $user->email;
            $response["role"] = [
                'id' => $user->role_id,
                'designation' => $user->role->designation,
                'description' => $user->role->slug,
            ];
            $response["joinned_date"] = $user->joinned_date;
            $response["leave_count"] = $user->leave_count;
            // $response["leaves"] = $leaves;
            $response["token"] = $user->createToken("MyApp")->plainTextToken;

            return response()->json([
                "status" => 1,
                "message" => "Login successful",
                "user" => $response
            ]);
        }

        return response()->json([
            "status" => 0,
            "message" => "Authentication error",
            "data" => null,
        ], 401);
    }


    // public function register(Request $request){
    //     $validator = Validator::make($request->all(),[
    //         "name" => "required",
    //         "email"=> "required|email",
    //         "password"=>"required",
    //         "confirm_password"=>"required|same:password",
    //         "role_id" => "required|exists:roles,id",
    //         "joinned_date" => "required|date",
    //         "leave_count" => "required|integer",
    //         "finger_printid" => "nullable|string"
    //     ]);
    //     if($validator->fails()){
    //         return response()->json([
    //           "status" => 0,
    //           "message" => "validation error",
    //           "data"=> $validator->errors()->all()  
    //         ]);
    //     }
    //     $user = User::create([
    //         "name"=>$request->name,
    //         "email"=>$request->email,
    //         "password"=>bcrypt($request->password),
    //         "role_id" => $request->role_id

    //     ]);

    //     $response["token"] = $user -> createToken("MyApp")->plainTextToken;
    //     $response["name"] = $user -> name;
    //     $response["email"] = $user -> email;
    //     $response["roles"] = $user->role_id;

    //     return response()->json([
    //         "status" => 1,
    //         "message"=> "User registered",
    //         "data"=> $response
    //     ]);
    // }

    // public function login(Request $request){
    //     if (Auth::attempt(["email"=>$request->email,"password"=>$request->password])) {
    //         $user = Auth::user();
    //         $response["name"] = $user->name;
    //         $response["email"] = $user->email;
    //         $response["role"] = $user->role_id;

    //         return response()->json([
    //             "status" => 1,
    //             "message" => "Login successful",
    //             "data" => $response
    //         ]);
    //     }
    //     return response()->json([
    //         "status" => 0,
    //             "message"=> "Authentication error",
    //             "data"=> null
    //     ]);
    // }
}
