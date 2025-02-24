<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Comment\CommentRequest;
use App\Models\Post;
use App\Models\Comment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use App\Http\Resources\Comment\CommentResource;
use App\Http\Resources\Comment\CommentCollection;

class CommentController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum')->except(['index']);
    }

    public function index(Post $post)
    {
        $cacheKey = 'post_comments_' . $post->id;
        
        $comments = Cache::remember($cacheKey, 300, function () use ($post) {
            return $post->comments()
                ->with(['user:id,name', 'replies' => function($query) {
                    $query->with('user:id,name')
                        ->orderBy('created_at', 'asc');
                }])
                ->whereNull('parent_id')
                ->orderBy('created_at', 'desc')
                ->get();
        });

        return new CommentCollection($comments);
    }

    public function store(CommentRequest $request, Post $post)
    {
        $comment = $post->comments()->create([
            'content' => $request->content,
            'user_id' => auth()->id(),
            'parent_id' => $request->parent_id
        ]);

        // Clear cache
        Cache::forget('post_comments_' . $post->id);
        Cache::forget('post_' . $post->id);

        $comment->load(['user:id,name', 'replies.user:id,name']);

        return (new CommentResource($comment))
            ->additional([
                'message' => 'Thêm bình luận thành công'
            ])
            ->response()
            ->setStatusCode(201);
    }

    public function update(CommentRequest $request, Comment $comment)
    {
        $this->authorize('update', $comment);

        $comment->update([
            'content' => $request->content
        ]);

        // Clear cache
        Cache::forget('post_comments_' . $comment->post_id);
        Cache::forget('post_' . $comment->post_id);

        return (new CommentResource($comment->load(['user:id,name', 'replies.user:id,name'])))
            ->additional([
                'message' => 'Cập nhật bình luận thành công'
            ]);
    }

    public function destroy(Comment $comment)
    {
        $this->authorize('delete', $comment);

        // Lưu post_id trước khi xóa comment
        $postId = $comment->post_id;

        // Xóa cả replies của comment này
        $comment->replies()->delete();
        $comment->delete();

        // Clear cache
        Cache::forget('post_comments_' . $postId);
        Cache::forget('post_' . $postId);

        return response()->json([
            'success' => true,
            'message' => 'Xóa bình luận thành công'
        ]);
    }
} 