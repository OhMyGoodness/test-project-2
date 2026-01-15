<?php

declare(strict_types=1);

namespace App\Services\News\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use OpenApi\Attributes as OA;

/**
 * Запрос на создание новости.
 * Валидирует входные данные при POST /api/news.
 */
#[OA\Schema(
    schema: 'StoreNewsRequest',
    title: 'Запрос создания новости',
    description: 'Тело запроса для создания новой новости',
    required: ['title', 'description'],
    properties: [
        new OA\Property(
            property: 'title',
            type: 'string',
            description: 'Заголовок новости',
            maxLength: 255,
            example: 'Важная новость'
        ),
        new OA\Property(
            property: 'description',
            type: 'string',
            description: 'Текст (описание) новости',
            example: 'Подробное описание события...'
        ),
    ],
    type: 'object'
)]
class StoreNewsRequest extends FormRequest
{
    /**
     * Проверить авторизацию на выполнение запроса.
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
     * @return array<string, ValidationRule|array<int, mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title'       => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
        ];
    }
}
