<?php

declare(strict_types=1);

namespace App\Services\News\Http\Resources;

use App\Http\Traits\WithCommentsPagination;
use App\Services\News\DTO\NewsOutputDto;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

/**
 * JSON-ресурс для представления новости вместе с пагинированными комментариями.
 * Использует трейт WithCommentsPagination для формирования блока комментариев.
 *
 * @property NewsOutputDto $resource
 */
#[OA\Schema(
    schema: 'NewsWithCommentsResource',
    title: 'Ресурс новости с комментариями',
    description: 'Публичное представление новости с курсорной пагинацией комментариев',
    properties: [
        new OA\Property(property: 'id', type: 'integer', description: 'Идентификатор новости', example: 1),
        new OA\Property(property: 'title', type: 'string', description: 'Заголовок новости', example: 'Заголовок'),
        new OA\Property(property: 'description', type: 'string', description: 'Текст новости', example: 'Описание новости'),
        new OA\Property(property: 'created_at', type: 'string', format: 'date-time', description: 'Дата создания', example: '2026-01-15T00:00:00.000000Z'),
        new OA\Property(property: 'updated_at', type: 'string', format: 'date-time', description: 'Дата обновления', example: '2026-01-15T00:00:00.000000Z'),
        new OA\Property(
            property: 'comments',
            description: 'Пагинированные комментарии к новости',
            properties: [
                new OA\Property(
                    property: 'data',
                    type: 'array',
                    items: new OA\Items(ref: '#/components/schemas/CommentResource'),
                    description: 'Список комментариев'
                ),
                new OA\Property(property: 'next_cursor', type: 'string', nullable: true, description: 'Курсор следующей страницы', example: null),
                new OA\Property(property: 'prev_cursor', type: 'string', nullable: true, description: 'Курсор предыдущей страницы', example: null),
                new OA\Property(property: 'per_page', type: 'integer', description: 'Количество на странице', example: 15),
                new OA\Property(property: 'path', type: 'string', description: 'URL текущего запроса', example: '/api/news/1'),
            ],
            type: 'object'
        ),
    ],
    type: 'object'
)]
class NewsWithCommentsResource extends JsonResource
{
    use WithCommentsPagination;

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
            'comments'    => $this->buildCommentsBlock($this->resource->paginatedComments, $request),
        ];
    }
}
