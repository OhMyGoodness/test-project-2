<?php

declare(strict_types=1);

namespace App\Services\Comment\DTO;

use App\Services\Comment\Models\Comment;
use Illuminate\Support\Carbon;

/**
 * DTO для представления комментария в ответе API.
 */
final readonly class CommentOutputDto
{
    /**
     * @param int                      $id              Идентификатор комментария.
     * @param int                      $userId          Идентификатор автора.
     * @param string                   $commentableType Тип комментируемой сущности (алиас из morphMap).
     * @param int                      $commentableId   Идентификатор комментируемой сущности.
     * @param int|null                 $parentId        Идентификатор родительского комментария.
     * @param string                   $text            Текст комментария.
     * @param Carbon                   $createdAt       Дата и время создания.
     * @param Carbon                   $updatedAt       Дата и время последнего обновления.
     * @param CommentOutputDto[]|null  $replies         Список дочерних ответов (null — не загружены).
     */
    public function __construct(
        public int     $id,
        public int     $userId,
        public string  $commentableType,
        public int     $commentableId,
        public ?int    $parentId,
        public string  $text,
        public Carbon  $createdAt,
        public Carbon  $updatedAt,
        /** @var CommentOutputDto[]|null */
        public ?array  $replies = null,
    ) {}

    /**
     * Создать DTO из Eloquent-модели комментария.
     *
     * @param Comment $comment Модель комментария (с опционально загруженным отношением replies).
     * @return self
     */
    public static function fromModel(Comment $comment): self
    {
        $replies = null;
        if ($comment->relationLoaded('replies')) {
            $replies = $comment->replies
                ->map(fn (Comment $reply): self => self::fromModel($reply))
                ->all();
        }

        return new self(
            id: $comment->id,
            userId: $comment->user_id,
            commentableType: $comment->commentable_type,
            commentableId: $comment->commentable_id,
            parentId: $comment->parent_id,
            text: $comment->text,
            createdAt: $comment->created_at,
            updatedAt: $comment->updated_at,
            replies: $replies,
        );
    }
}
