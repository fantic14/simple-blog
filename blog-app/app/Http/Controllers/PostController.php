<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class PostController extends Controller
{
    public function index()
    {
        return response()->json([
            'success' => true,
            'message' => 'Posts fetched successfully',
            'data' => Post::with('user')->latest()->get()
        ]);
    }

    // create page for creating a new post
    public function create()
    {
        return response()->json([
            'success' => true,
            'message' => 'Post create page',
        ]);
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'title' => 'required|string|max:60',
            'content' => 'required|string|max:255',
        ]);

        $validatedData['user_id'] = $request->user()->id;

        $post = Post::create($validatedData);

        return response()->json([
            'success' => true,
            'message' => 'Post created successfully',
            'data' => $post->load('user')
        ]);
    }

    public function show($id)
    {
        $post = Post::find($id);
        if (!$post) {
            return response()->json([
                'success' => false,
                'message' => 'Post not found',
            ], 404);
        }
        return response()->json([
            'success' => true,
            'message' => 'Post fetched successfully',
            'data' => $post->load('user')
        ]);
    }

    public function edit($id)
    {
        $post = Post::find($id);
        if (!$post) {
            return response()->json([
                'success' => false,
                'message' => 'Post not found',
            ], 404);
        }

        Gate::authorize('update', $post);

        return response()->json([
            'success' => true,
            'message' => 'Post edit page',
            'data' => $post->load('user')
        ]);
    }

    public function update(Request $request, $id)
    {
        $post = Post::find($id);
        if (!$post) {
            return response()->json([
                'success' => false,
                'message' => 'Post not found',
            ], 404);
        }

        Gate::authorize('update', $post);

        $post->update($request->all());
        return response()->json([
            'success' => true,
            'message' => 'Post updated successfully',
            'data' => $post->load('user')
        ]);
    }

    public function destroy($id)
    {
        $post = Post::find($id);
        if (!$post) {
            return response()->json([
                'success' => false,
                'message' => 'Post not found',
            ], 404);
        }

        Gate::authorize('delete', $post);

        $post->delete();
        return response()->json([
            'success' => true,
            'message' => 'Post deleted successfully',
        ]);
    }
}
