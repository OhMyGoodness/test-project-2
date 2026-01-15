<?php

declare(strict_types=1);

namespace App\Services\Comment;

use App\Enums\CommentableType;
use App\Services\Comment\DTO\CommentOutputDto;
use App\Services\Comment\DTO\CreateCommentDto;
use App\Services\Comment\DTO\UpdateCommentDto;
use App\Services\Comment\Repositories\CommentRepositoryInterface;

/**
 * Сервис для управления комментариями.
 * Реализует бизнес-логику создания, обновления и удаления комментариев.
 */
class CommentService implements CommentServiceInterface
{
    /**
     * @param CommentRepositoryInterface $repository Репозиторий комментариев.
     */
    public function __construct(
        private readonly CommentRepositoryInterface $repository,
    ) {}

    /**
     * Создать новый комментарий.
     *
     * @param CreateCommentDto $dto Данные для создания комментария.
     * @return CommentOutputDto
     */
    public function create(CreateCommentDto $dto): CommentOutputDto
    {
        // Валидируем тип через enum — гарантируем допустимое значение
        CommentableType::from($dto->commentableType);

        $comment = $this->repository->create([
            'user_id'          => $dto->userId,
            'commentable_type' => $dto->commentableType,
            'commentable_id'   => $dto->commentableId,
            'parent_id'        => $dto->parentId,
            'text'             => $dto->text,
        ]);

        $comment->loadMissing('replies');

        return CommentOutputDto::fromModel($comment);
    }

    /**
     * Обновить текст существующего комментария.
     *
     * @param int              $id  Идентификатор комментария.
     * @param UpdateCommentDto $dto Данные для обновления.
     * @return CommentOutputDto
     */
    public function update(int $id, UpdateCommentDto $dto): CommentOutputDto
    {
        $comment = $this->repository->update($id, $dto->toArray());
        $comment->loadMissing('replies');

        return CommentOutputDto::fromModel($comment);
    }

    /**
     * Удалить комментарий вместе со всеми ответами.
     *
     * @param int $id Идентификатор комментария.
     * @return void
     */
    public function delete(int $id): void
    {
        $this->repository->deleteWithReplies($id);
    }
}
