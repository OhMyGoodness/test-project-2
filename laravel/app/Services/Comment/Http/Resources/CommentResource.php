<?php

declare(strict_types=1);

namespace App\Services\Comment\Http\Resources;

use App\Services\Comment\DTO\CommentOutputDto;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

/**
 * API-ресурс комментария.
 * Преобразует CommentOutputDto в JSON-представление для API-ответа.
 *
 * @mixin CommentOutputDto
 */
#[OA\Schema(
    schema: 'CommentResource',
    title: 'Comment API Resource',
    description: 'Публичное API-представление комментария',
    allOf: [
        new OA\Schema(ref: '#/components/schemas/CommentModel'),
        new OA\Schema(
            properties: [
                new OA\Property(
                    property: 'replies',
                    type: 'array',
                    items: new OA\Items(ref: '#/components/schemas/CommentResource'),
                    description: 'Список дочерних ответов на комментарий'
                ),
            ]
        ),
    ]
)]
class CommentResource extends JsonResource
{
    /**
     * @var CommentOutputDto $resource DTO комментария.
     */
    public $resource;

    /**
     * Преобразовать DTO в массив для JSON-ответа.
     *
     * @param Request $request HTTP-запрос.
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'               => $this->resource->id,
            'user_id'          => $this->resource->userId,
            'commentable_type' => $this->resource->commentableType,
            'commentable_id'   => $this->resource->commentableId,
            'parent_id'        => $this->resource->parentId,
            'text'             => $this->resource->text,
            'replies'          => $this->resource->replies
                ? CommentResource::collection($this->resource->replies)
                : [],
            'created_at'       => $this->resource->createdAt->toISOString(),
            'updated_at'       => $this->resource->updatedAt->toISOString(),
        ];
    }
}
