<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    /**
     *@return
     * 
     */

    public function register(Request $request){
        $validator = Validator::make($request->all(),[
            "name" => "required",
            "email"=> "required|email",
            "password"=>"required",
            "confirm_password"=>"required|same:password",
            "roles_id" => "required|exists:roles,id"
        ]);
        if($validator->fails()){
            return response()->json([
              "status" => 0,
              "message" => "validation error",
              "data"=> $validator->errors()->all()  
            ]);
        }
        $user = User::create([
            "name"=>$request->name,
            "email"=>$request->email,
            "password"=>bcrypt($request->password),
            "roles_id" => $request->roles_id

        ]);

        $response["token"] = $user -> createToken("MyApp")->plainTextToken;
        $response["name"] = $user -> name;
        $response["email"] = $user -> email;
        $response["roles"] = $user->roles;

        return response()->json([
            "status" => 1,
            "message"=> "User registered",
            "data"=> $response
        ]);
    }

    public function login(Request $request){
        if (Auth::attempt(["email"=>$request->email,"password"=>$request->password])) {
            $user = Auth::user();
            $response["name"] = $user->name;
            $response["email"] = $user->email;
            $response["role"] = $user->role;

            return response()->json([
                "status" => 1,
                "message" => "Login successful",
                "data" => $response
            ]);
        }
        return response()->json([
            "status" => 0,
                "message"=> "Authentication error",
                "data"=> null
        ]);
    }
}
