<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PostController extends Controller
{
    public function index()
    {
        $posts = Post::paginate(5);
        return response()->json([
            "status" => 1,
            "message" => "Posts fetched successfully",
            "data" => $posts
        ]);
    }

    public function show($id)
    {
        $post = Post::find($id);
        
        if (!$post) {
            return response()->json([
                "status" => 0,
                "message" => "Post not found"
            ], 404);
        }

        return response()->json([
            "status" => 1,
            "message" => "Post fetched successfully",
            "data" => $post
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "title" => "required|max:255",
            "body" => "required"
        ]);

        if ($validator->fails()) {
            return response()->json([
                "status" => 0,
                "message" => "Validation error",
                "data" => $validator->errors()->all()
            ], 422);
        }

        $post = Post::create([
            "title" => $request->title,
            "body" => $request->body
        ]);

        return response()->json([
            "status" => 1,
            "message" => "Post created successfully",
            "data" => $post
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $post = Post::find($id);

        if (!$post) {
            return response()->json([
                "status" => 0,
                "message" => "Post not found"
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            "title" => "required|max:255",
            "body" => "required"
        ]);

        if ($validator->fails()) {
            return response()->json([
                "status" => 0,
                "message" => "Validation error",
                "data" => $validator->errors()->all()
            ], 422);
        }

        $post->update([
            "title" => $request->title,
            "body" => $request->body
        ]);

        return response()->json([
            "status" => 1,
            "message" => "Post updated successfully",
            "data" => $post
        ]);
    }

    public function destroy($id)
    {
        $post = Post::find($id);

        if (!$post) {
            return response()->json([
                "status" => 0,
                "message" => "Post not found"
            ], 404);
        }

        $post->delete();

        return response()->json([
            "status" => 1,
            "message" => "Post deleted successfully"
        ]);
    }
}