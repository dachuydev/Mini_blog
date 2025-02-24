<?php

namespace App\Http\Resources\Post;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class PostCollection extends ResourceCollection
{
    public function toArray(Request $request): array
    {
        return [
            'data' => $this->collection->map(function ($post) {
                return [
                    'id' => $post->id,
                    'title' => $post->title,
                    'content' => $post->content,
                    'user' => [
                        'id' => $post->user->id,
                        'name' => $post->user->name
                    ],
                    // 'comments' => $post->comments,
                    'total_comments' => $post->total_comments ?? 0,
                    'total_replies' => $post->total_replies ?? 0,
                    'created_at' => $post->created_at,
                    'updated_at' => $post->updated_at
                ];
            })
        ];
    }

    public function with(Request $request): array
    {
        return [
            'success' => true,
            'message' => 'Lấy danh sách bài viết thành công'
        ];
    }
} 