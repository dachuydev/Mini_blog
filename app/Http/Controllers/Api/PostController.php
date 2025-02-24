<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Post\PostRequest;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use App\Http\Resources\Post\PostResource;
use App\Http\Resources\Post\PostCollection;

class PostController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum')->except(['index', 'show']);
    }

    public function index(Request $request)
    {
        $posts = Post::with('user:id,name')
            ->select('posts.*')
            ->selectSub(
                DB::table('comments')
                    ->whereColumn('post_id', 'posts.id')
                    ->whereNull('parent_id')
                    ->selectRaw('COUNT(*)'),
                'total_comments'
            )
            ->selectSub(
                DB::table('comments')
                    ->whereColumn('post_id', 'posts.id')
                    ->whereNotNull('parent_id')
                    ->selectRaw('COUNT(*)'),
                'total_replies'
            )
            ->latest()
            ->paginate(10);

        return new PostCollection($posts);
    }

    public function store(PostRequest $request)
    {
        $post = Post::create([
            'title' => $request->title,
            'content' => $request->content,
            'user_id' => auth()->id()
        ]);

        return (new PostResource($post->load('user:id,name')))
            ->additional([
                'message' => 'Tạo bài viết thành công'
            ]);
    }

    public function show(Post $post)
    {
        $post = Post::where('id', $post->id)
            ->select('posts.*')
            ->selectSub(
                DB::table('comments')
                    ->whereColumn('post_id', 'posts.id')
                    ->whereNull('parent_id')
                    ->selectRaw('COUNT(*)'),
                'total_comments'
            )
            ->selectSub(
                DB::table('comments')
                    ->whereColumn('post_id', 'posts.id')
                    ->whereNotNull('parent_id')
                    ->selectRaw('COUNT(*)'),
                'total_replies'
            )
            ->with(['user:id,name', 'comments' => function($query) {
                $query->with(['user:id,name'])
                    ->whereNull('parent_id')
                    ->orderBy('created_at', 'desc')
                    ->with(['replies' => function($query) {
                        $query->with(['user:id,name', 'replies.user:id,name', 'replies' => function($query) {
                            $query->with('replies.user:id,name');
                        }]);
                    }]);
            }])
            ->first();

        return new PostResource($post);
    }

    public function update(PostRequest $request, Post $post)
    {
        $this->authorize('update', $post);

        $post->update([
            'title' => $request->title,
            'content' => $request->content
        ]);

        return (new PostResource($post->load('user:id,name')))
            ->additional([
                'message' => 'Cập nhật bài viết thành công'
            ]);
    }

    public function destroy(Post $post)
    {
        $this->authorize('delete', $post);

        $post->delete();

        return response()->json([
            'message' => 'Xóa bài viết thành công'
        ]);
    }
}