<?php

namespace App\Http\Resources\Comment;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class CommentCollection extends ResourceCollection
{
    public function toArray(Request $request): array
    {
        return [
            'data' => CommentResource::collection($this->collection)
        ];
    }

    public function with(Request $request): array
    {
        return [
            'success' => true,
            'message' => 'Lấy danh sách bình luận thành công'
        ];
    }
} 