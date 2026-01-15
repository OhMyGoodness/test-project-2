<?php

declare(strict_types=1);

namespace App\Services\VideoPost\DTO;

use App\Services\VideoPost\Models\VideoPost;
use Illuminate\Pagination\CursorPaginator;
use Illuminate\Support\Carbon;

/**
 * DTO для передачи данных видеопоста из сервиса в контроллер/ресурс.
 */
final readonly class VideoPostOutputDto
{
    /**
     * @param int                  $id                Идентификатор видеопоста.
     * @param string               $title             Заголовок видеопоста.
     * @param string               $description       Описание видеопоста.
     * @param Carbon               $createdAt         Дата создания.
     * @param Carbon               $updatedAt         Дата последнего обновления.
     * @param CursorPaginator|null $paginatedComments Пагинированные комментарии или null.
     */
    public function __construct(
        public int              $id,
        public string           $title,
        public string           $description,
        public Carbon           $createdAt,
        public Carbon           $updatedAt,
        public ?CursorPaginator $paginatedComments = null,
    ) {
    }

    /**
     * Создать DTO из Eloquent-модели VideoPost.
     *
     * @param VideoPost $videoPost Модель видеопоста.
     * @return self
     */
    public static function fromModel(VideoPost $videoPost): self
    {
        $paginatedComments = $videoPost->relationLoaded('paginatedComments')
            ? $videoPost->getRelation('paginatedComments')
            : null;

        return new self(
            id: $videoPost->id,
            title: $videoPost->title,
            description: $videoPost->description,
            createdAt: $videoPost->created_at,
            updatedAt: $videoPost->updated_at,
            paginatedComments: $paginatedComments,
        );
    }
}
