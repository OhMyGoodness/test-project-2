<?php

declare(strict_types=1);

namespace App\Services\News\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use OpenApi\Attributes as OA;

/**
 * Запрос на обновление новости.
 * Валидирует входные данные при PUT /api/news/{id}.
 * Все поля опциональны — передаётся только то, что нужно изменить.
 */
#[OA\Schema(
    schema: 'UpdateNewsRequest',
    title: 'Запрос обновления новости',
    description: 'Тело запроса для обновления существующей новости. Все поля опциональны.',
    properties: [
        new OA\Property(
            property: 'title',
            type: 'string',
            description: 'Новый заголовок новости',
            maxLength: 255,
            example: 'Обновлённый заголовок'
        ),
        new OA\Property(
            property: 'description',
            type: 'string',
            description: 'Новый текст новости',
            example: 'Обновлённое описание...'
        ),
    ],
    type: 'object'
)]
class UpdateNewsRequest extends FormRequest
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
            'title'       => ['sometimes', 'required', 'string', 'max:255'],
            'description' => ['sometimes', 'required', 'string'],
        ];
    }
}
