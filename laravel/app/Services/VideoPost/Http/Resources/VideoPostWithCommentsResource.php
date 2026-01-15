<?php

declare(strict_types=1);

namespace App\Services\VideoPost\Http\Resources;

use App\Http\Traits\WithCommentsPagination;
use App\Services\VideoPost\DTO\VideoPostOutputDto;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

/**
 * Ресурс для трансформации VideoPostOutputDto в JSON-ответ с пагинированными комментариями.
 * Используется при отображении детальной страницы видеопоста.
 *
 * @property VideoPostOutputDto $resource
 */
#[OA\Schema(
    schema: 'VideoPostWithCommentsResource',
    title: 'Ресурс видеопоста с комментариями',
    description: 'Публичное API-представление видеопоста включая пагинированные комментарии',
    allOf: [
        new OA\Schema(ref: '#/components/schemas/VideoPostResource'),
        new OA\Schema(
            properties: [
                new OA\Property(
                    property: 'comments',
                    description: 'Блок курсорной пагинации комментариев',
                    properties: [
                        new OA\Property(
                            property: 'data',
                            type: 'array',
                            items: new OA\Items(ref: '#/components/schemas/CommentResource')
                        ),
                        new OA\Property(property: 'next_cursor', type: 'string', description: 'Курсор следующей страницы', nullable: true),
                        new OA\Property(property: 'prev_cursor', type: 'string', description: 'Курсор предыдущей страницы', nullable: true),
                        new OA\Property(property: 'per_page', type: 'integer', description: 'Количество записей на страницу', example: 15),
                        new OA\Property(property: 'path', type: 'string', description: 'Базовый URL запроса', example: '/api/video-posts/1'),
                    ],
                    type: 'object'
                ),
            ]
        ),
    ]
)]
class VideoPostWithCommentsResource extends JsonResource
{
    use WithCommentsPagination;

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
            'comments'    => $this->buildCommentsBlock($this->resource->paginatedComments, $request),
        ];
    }
}
