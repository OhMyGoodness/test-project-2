<?php

declare(strict_types=1);

namespace App\Services\Comment\Repositories;

use App\Exceptions\NotFoundException;
use App\Repositories\AbstractEloquentRepository;
use App\Services\Comment\Models\Comment;
use Illuminate\Support\Facades\DB;

/**
 * Eloquent-реализация репозитория комментариев.
 * Инкапсулирует все запросы к таблице comments.
 */
class EloquentCommentRepository extends AbstractEloquentRepository implements CommentRepositoryInterface
{
    /**
     * Вернуть FQCN модели комментария.
     *
     * @return class-string<Comment>
     */
    protected function model(): string
    {
        return Comment::class;
    }

    /**
     * Удалить комментарий вместе со всеми дочерними ответами в рамках транзакции.
     *
     * @param int $id Идентификатор комментария.
     * @return void
     * @throws NotFoundException Если комментарий не найден.
     */
    public function deleteWithReplies(int $id): void
    {
        DB::transaction(function () use ($id) {
            $comment = $this->getById($id);
            $comment->replies()->delete();
            $comment->delete();
        });
    }
}
