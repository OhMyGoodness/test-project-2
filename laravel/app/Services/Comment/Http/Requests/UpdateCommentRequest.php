<?php

declare(strict_types=1);

namespace App\Services\Comment\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use OpenApi\Attributes as OA;

/**
 * Запрос на обновление существующего комментария.
 */
#[OA\Schema(
    schema: 'UpdateCommentRequest',
    required: ['text'],
    properties: [
        new OA\Property(
            property: 'text',
            type: 'string',
            description: 'Новый текст комментария',
            example: 'Обновлённый текст комментария'
        ),
    ],
    type: 'object'
)]
class UpdateCommentRequest extends FormRequest
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
            'text' => ['required', 'string'],
        ];
    }
}
