<?php

declare(strict_types=1);

namespace App\Services\VideoPost\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use OpenApi\Attributes as OA;

/**
 * Запрос на создание нового видеопоста.
 * Содержит правила валидации входных данных.
 */
#[OA\Schema(
    schema: 'StoreVideoPostRequest',
    title: 'Запрос на создание видеопоста',
    description: 'Тело запроса для создания нового видеопоста',
    required: ['title', 'description'],
    properties: [
        new OA\Property(
            property: 'title',
            type: 'string',
            description: 'Заголовок видеопоста',
            maxLength: 255,
            example: 'Невероятное видео'
        ),
        new OA\Property(
            property: 'description',
            type: 'string',
            description: 'Описание видеопоста',
            example: 'Посмотрите это видео'
        ),
    ],
    type: 'object'
)]
class StoreVideoPostRequest extends FormRequest
{
    /**
     * Проверить авторизацию для выполнения запроса.
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
     * @return array<string, ValidationRule|array<int, string>|string>
     */
    public function rules(): array
    {
        return [
            'title'       => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
        ];
    }
}
