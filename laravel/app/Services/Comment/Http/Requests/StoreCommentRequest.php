<?php

declare(strict_types=1);

namespace App\Services\Comment\Http\Requests;

use App\Enums\CommentableType;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;
use OpenApi\Attributes as OA;

/**
 * Запрос на создание нового комментария.
 */
#[OA\Schema(
    schema: 'StoreCommentRequest',
    required: ['user_id', 'commentable_type', 'commentable_id', 'text'],
    properties: [
        new OA\Property(property: 'user_id', type: 'integer', description: 'Идентификатор пользователя', example: 1),
        new OA\Property(
            property: 'commentable_type',
            type: 'string',
            description: 'Тип комментируемой сущности',
            enum: ['news', 'video_post'],
            example: 'news'
        ),
        new OA\Property(property: 'commentable_id', type: 'integer', description: 'Идентификатор комментируемой сущности', example: 1),
        new OA\Property(property: 'parent_id', type: 'integer', description: 'Идентификатор родительского комментария (для ответов)', example: null, nullable: true),
        new OA\Property(property: 'text', type: 'string', description: 'Текст комментария', example: 'Отличная новость!'),
    ],
    type: 'object'
)]
class StoreCommentRequest extends FormRequest
{
    /**
     * Определить, авторизован ли пользователь для выполнения этого запроса.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Получить правила валидации для запроса.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'user_id'          => ['required', 'integer', 'exists:users,id'],
            'commentable_type' => ['required', 'string', new Enum(CommentableType::class)],
            'commentable_id'   => [
                'required',
                'integer',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    $type = CommentableType::tryFrom($this->input('commentable_type'));
                    if ($type && !$type->modelClass()::query()->where('id', $value)->exists()) {
                        $fail('Указанная сущность не найдена.');
                    }
                },
            ],
            'parent_id'        => ['nullable', 'integer', 'exists:comments,id'],
            'text'             => ['required', 'string'],
        ];
    }
}
