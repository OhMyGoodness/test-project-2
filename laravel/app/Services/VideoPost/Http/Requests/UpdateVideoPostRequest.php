<?php

declare(strict_types=1);

namespace App\Services\VideoPost\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use OpenApi\Attributes as OA;

/**
 * Запрос на обновление существующего видеопоста.
 * Все поля опциональны: обновляются только переданные значения.
 */
#[OA\Schema(
    schema: 'UpdateVideoPostRequest',
    title: 'Запрос на обновление видеопоста',
    description: 'Тело запроса для обновления существующего видеопоста. Все поля опциональны.',
    properties: [
        new OA\Property(
            property: 'title',
            type: 'string',
            description: 'Новый заголовок видеопоста',
            maxLength: 255,
            example: 'Обновлённый заголовок видео'
        ),
        new OA\Property(
            property: 'description',
            type: 'string',
            description: 'Новое описание видеопоста',
            example: 'Обновлённое описание видео'
        ),
    ],
    type: 'object'
)]
class UpdateVideoPostRequest extends FormRequest
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
            'title'       => ['sometimes', 'required', 'string', 'max:255'],
            'description' => ['sometimes', 'required', 'string'],
        ];
    }
}
