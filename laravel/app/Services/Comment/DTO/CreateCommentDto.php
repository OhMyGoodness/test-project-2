<?php

declare(strict_types=1);

namespace App\Services\Comment\DTO;

/**
 * DTO для создания нового комментария.
 */
final readonly class CreateCommentDto
{
    /**
     * @param int      $userId          Идентификатор автора комментария.
     * @param string   $commentableType Тип комментируемой сущности (алиас из morphMap: 'news', 'video_post').
     * @param int      $commentableId   Идентификатор комментируемой сущности.
     * @param string   $text            Текст комментария.
     * @param int|null $parentId        Идентификатор родительского комментария (для ответов).
     */
    public function __construct(
        public int    $userId,
        public string $commentableType,
        public int    $commentableId,
        public string $text,
        public ?int   $parentId = null,
    ) {}

    /**
     * Создать DTO из массива данных.
     *
     * @param array<string, mixed> $data Ассоциативный массив с полями комментария.
     * @return self
     */
    public static function fromArray(array $data): self
    {
        return new self(
            userId: $data['user_id'],
            commentableType: $data['commentable_type'],
            commentableId: $data['commentable_id'],
            text: $data['text'],
            parentId: $data['parent_id'] ?? null,
        );
    }
}
