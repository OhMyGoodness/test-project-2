<?php

declare(strict_types=1);

namespace App\Services\VideoPost\Http\Resources;

use App\Services\VideoPost\DTO\VideoPostOutputDto;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

/**
 * Ресурс для трансформации VideoPostOutputDto в JSON-ответ.
 * Используется при возврате данных видеопоста без комментариев.
 *
 * @property VideoPostOutputDto $resource
 */
#[OA\Schema(
    schema: 'VideoPostResource',
    title: 'Ресурс видеопоста',
    description: 'Публичное API-представление видеопоста',
    properties: [
        new OA\Property(property: 'id', type: 'integer', description: 'Идентификатор видеопоста', example: 1),
        new OA\Property(property: 'title', type: 'string', description: 'Заголовок видеопоста', example: 'Невероятное видео'),
        new OA\Property(property: 'description', type: 'string', description: 'Описание видеопоста', example: 'Посмотрите это удивительное видео...'),
        new OA\Property(property: 'created_at', type: 'string', format: 'date-time', description: 'Дата создания', example: '2026-01-15T00:00:00.000000Z'),
        new OA\Property(property: 'updated_at', type: 'string', format: 'date-time', description: 'Дата последнего обновления', example: '2026-01-15T00:00:00.000000Z'),
    ],
    type: 'object'
)]
class VideoPostResource extends JsonResource
{
    /**
     * Преобразовать ресурс в массив для JSON-ответа.
     *
     * @param Request $request Текущий HTTP-запрос.
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->resource->id,
            'title'       => $this->resource->title,
            'description' => $this->resource->description,
            'created_at'  => $this->resource->createdAt?->toISOString(),
            'updated_at'  => $this->resource->updatedAt?->toISOString(),
        ];
    }
}
