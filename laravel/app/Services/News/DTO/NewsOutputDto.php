<?php

declare(strict_types=1);

namespace App\Services\News\DTO;

use App\Services\News\Models\News;
use Illuminate\Pagination\CursorPaginator;
use Illuminate\Support\Carbon;

/**
 * DTO выходных данных новости.
 * Возвращается из сервисного слоя в контроллер.
 */
final readonly class NewsOutputDto
{
    /**
     * @param int                  $id                Идентификатор новости.
     * @param string               $title             Заголовок новости.
     * @param string               $description       Текст (описание) новости.
     * @param Carbon               $createdAt         Дата создания.
     * @param Carbon               $updatedAt         Дата последнего обновления.
     * @param CursorPaginator|null $paginatedComments Курсорный пагинатор комментариев или null.
     */
    public function __construct(
        public int              $id,
        public string           $title,
        public string           $description,
        public Carbon           $createdAt,
        public Carbon           $updatedAt,
        public ?CursorPaginator $paginatedComments = null,
    ) {}

    /**
     * Создать DTO из Eloquent-модели новости.
     *
     * @param News $news Модель новости.
     * @return self
     */
    public static function fromModel(News $news): self
    {
        /** @var CursorPaginator|null $paginatedComments */
        $paginatedComments = $news->relationLoaded('paginatedComments')
            ? $news->getRelation('paginatedComments')
            : null;

        return new self(
            id: $news->id,
            title: $news->title,
            description: $news->description,
            createdAt: $news->created_at,
            updatedAt: $news->updated_at,
            paginatedComments: $paginatedComments,
        );
    }
}
