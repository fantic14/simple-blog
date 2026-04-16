<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class CommentController extends Controller
{

    public function indexForPost($postId)
    {
        $comments = Comment::where('post_id', $postId)->with('user')->latest()->get();
        return response()->json([
            'success' => true,
            'message' => 'Comments fetched successfully',
            'data' => $comments
        ]);
    }

    public function store(Request $request, $postId)
    {
        $request->validate([
            'comment' => 'required|string|max:1000',
        ]);

        $userId = auth('sanctum')->id();

        $comment = Comment::create([
            'comment' => $request->comment,
            'post_id' => $postId,
            'user_id' => $userId,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Comment created successfully',
            'data' => $comment->load('user')
        ]);
    }

    public function destroy($id)
    {
        $comment = Comment::find($id);
        if (!$comment) {
            return response()->json([
                'success' => false,
                'message' => 'Comment not found',
            ], 404);
        }

        Gate::authorize('delete', $comment);

        $comment->delete();
        return response()->json([
            'success' => true,
            'message' => 'Comment deleted successfully',
        ]);
    }
}
