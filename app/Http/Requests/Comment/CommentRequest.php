<?php

namespace App\Http\Requests\Comment;

use Illuminate\Foundation\Http\FormRequest;

class CommentRequest extends FormRequest
{
    public function authorize(): bool
    {
        if ($this->route('comment')) {
            return $this->user()->can('update', $this->route('comment'));
        }
        return true;
    }

    public function rules(): array
    {
        return [
            'content' => 'required|string',
            'parent_id' => 'nullable|exists:comments,id'
        ];
    }

    public function messages(): array
    {
        return [
            'content.required' => 'Nội dung bình luận là bắt buộc',
            'parent_id.exists' => 'Bình luận cha không tồn tại'
        ];
    }
} 