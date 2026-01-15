<?php

declare(strict_types=1);

namespace App\Services\Comment;

use App\Services\Comment\DTO\CommentOutputDto;
use App\Services\Comment\DTO\CreateCommentDto;
use App\Services\Comment\DTO\UpdateCommentDto;

/**
 * Контракт сервиса для управления комментариями.
 */
interface CommentServiceInterface
{
    /**
     * Создать новый комментарий.
     *
     * @param CreateCommentDto $dto Данные для создания комментария.
     * @return CommentOutputDto
     */
    public function create(CreateCommentDto $dto): CommentOutputDto;

    /**
     * Обновить текст существующего комментария.
     *
     * @param int              $id  Идентификатор комментария.
     * @param UpdateCommentDto $dto Данные для обновления.
     * @return CommentOutputDto
     */
    public function update(int $id, UpdateCommentDto $dto): CommentOutputDto;

    /**
     * Удалить комментарий вместе со всеми ответами.
     *
     * @param int $id Идентификатор комментария.
     * @return void
     */
    public function delete(int $id): void;
}
