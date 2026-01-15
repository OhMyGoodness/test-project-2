<?php

declare(strict_types=1);

namespace App\Services\News\Http\Resources;

use App\Services\News\DTO\NewsOutputDto;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

/**
 * JSON-ресурс для представления новости в API.
 * Трансформирует NewsOutputDto в массив для ответа.
 *
 * @property NewsOutputDto $resource
 */
#[OA\Schema(
    schema: 'NewsResource',
    title: 'Ресурс новости',
    description: 'Публичное представление новости в API',
    properties: [
        new OA\Property(property: 'id', type: 'integer', description: 'Идентификатор новости', example: 1),
        new OA\Property(property: 'title', type: 'string', description: 'Заголовок новости', example: 'Заголовок'),
        new OA\Property(property: 'description', type: 'string', description: 'Текст новости', example: 'Описание новости'),
        new OA\Property(property: 'created_at', type: 'string', format: 'date-time', description: 'Дата создания', example: '2026-01-15T00:00:00.000000Z'),
        new OA\Property(property: 'updated_at', type: 'string', format: 'date-time', description: 'Дата обновления', example: '2026-01-15T00:00:00.000000Z'),
    ],
    type: 'object'
)]
class NewsResource extends JsonResource
{
    /**
     * Трансформировать ресурс в массив для JSON-ответа.
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
