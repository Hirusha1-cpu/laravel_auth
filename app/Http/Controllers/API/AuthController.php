<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\LeaveCalculationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "name" => "required",
            "email" => "required|email",
            "password" => "required",
            "confirm_password" => "required|same:password",
            "role_id" => "required|exists:roles,id",
            "joinned_date" => "required|date",
            "leave_count" => "required|integer",
            "finger_printid" => "nullable|string"
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
            "finger_printid" => $request->finger_printid
        ]);

        $leaves = $this->leaveCalculationService->calculateLeaves(
            $request->joinned_date,
            $request->leave_count
        );

        $response["token"] = $user->createToken("MyApp")->plainTextToken;
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

    public function login(Request $request)
    {
        if (Auth::attempt(["email" => $request->email, "password" => $request->password])) {
            $user = Auth::user();
            
            $leaves = $this->leaveCalculationService->calculateLeaves(
                $user->joinned_date,
                $user->leave_count
            );

            $response["name"] = $user->name;
            $response["email"] = $user->email;
            $response["role"] = $user->role_id;
            $response["joinned_date"] = $user->joinned_date;
            $response["leave_count"] = $user->leave_count;
            $response["leaves"] = $leaves;
            $response["token"] = $user->createToken("MyApp")->plainTextToken;

            return response()->json([
                "status" => 1,
                "message" => "Login successful",
                "data" => $response
            ]);
        }

        return response()->json([
            "status" => 0,
            "message" => "Authentication error",
            "data" => null
        ]);
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
