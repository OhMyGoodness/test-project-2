<?php

declare(strict_types=1);

namespace App\Services\VideoPost\Repositories;

use App\Repositories\AbstractEloquentRepository;
use App\Services\VideoPost\Models\VideoPost;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

/**
 * Eloquent-реализация репозитория видеопостов.
 * Наследует универсальные CRUD-операции и добавляет специфичные методы домена.
 */
class EloquentVideoPostRepository extends AbstractEloquentRepository implements VideoPostRepositoryInterface
{
    /**
     * Вернуть FQCN модели видеопоста.
     *
     * @return class-string<VideoPost>
     */
    protected function model(): string
    {
        return VideoPost::class;
    }

    /**
     * Найти видеопост с курсорной пагинацией комментариев верхнего уровня.
     * Загружает только корневые комментарии (без parent_id) с пользователями и ответами.
     *
     * @param int         $id      Идентификатор видеопоста.
     * @param int         $perPage Количество комментариев на страницу.
     * @param string|null $cursor  Курсор для пагинации.
     * @return VideoPost
     */
    public function findWithPaginatedComments(int $id, int $perPage, ?string $cursor): VideoPost
    {
        /** @var VideoPost $videoPost */
        $videoPost = $this->getById($id);

        $videoPost->setRelation(
            'paginatedComments',
            $videoPost->comments()
                ->whereNull('parent_id')
                ->with(['user', 'replies.user'])
                ->cursorPaginate($perPage, ['*'], 'cursor', $cursor)
        );

        return $videoPost;
    }

    /**
     * Получить видеопосты с постраничной пагинацией.
     *
     * @param int $perPage Количество записей на страницу.
     * @return LengthAwarePaginator
     */
    public function getPaginated(int $perPage): LengthAwarePaginator
    {
        return $this->model()::query()->paginate($perPage);
    }
}
