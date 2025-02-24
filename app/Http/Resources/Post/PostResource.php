<?php

namespace App\Http\Resources\Post;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PostResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'content' => $this->content,
            'user' => [
                'id' => $this->user->id,
                'name' => $this->user->name
            ],
            'comments' => $this->comments,
            'total_comments' => $this->total_comments ?? 0,
            'total_replies' => $this->total_replies ?? 0,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at
        ];
    }
} 