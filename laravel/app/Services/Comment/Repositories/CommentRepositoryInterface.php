<?php

declare(strict_types=1);

namespace App\Services\Comment\Repositories;

use App\Exceptions\NotFoundException;
use App\Repositories\Contracts\RepositoryInterface;

/**
 * Контракт репозитория для работы с комментариями.
 * Расширяет базовый CRUD-интерфейс специфичной операцией удаления с ответами.
 */
interface CommentRepositoryInterface extends RepositoryInterface
{
    /**
     * Удалить комментарий вместе со всеми дочерними ответами в рамках транзакции.
     *
     * @param int $id Идентификатор комментария.
     * @return void
     * @throws NotFoundException Если комментарий не найден.
     */
    public function deleteWithReplies(int $id): void;
}
