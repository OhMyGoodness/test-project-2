<?php

declare(strict_types=1);

namespace App\Services\News\Repositories;

use App\Repositories\AbstractEloquentRepository;
use App\Services\News\Models\News;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

/**
 * Eloquent-реализация репозитория новостей.
 * Наследует базовый CRUD из AbstractEloquentRepository
 * и реализует специфичные методы домена News.
 */
class EloquentNewsRepository extends AbstractEloquentRepository implements NewsRepositoryInterface
{
    /**
     * Вернуть FQCN модели новости.
     *
     * @return class-string<News>
     */
    protected function model(): string
    {
        return News::class;
    }

    /**
     * Найти новость с курсорной пагинацией комментариев верхнего уровня.
     * Подгружает пользователей комментариев и их ответы с пользователями.
     *
     * @param int         $id      Идентификатор новости.
     * @param int         $perPage Количество комментариев на страницу.
     * @param string|null $cursor  Курсор для пагинации.
     * @return News
     */
    public function findWithPaginatedComments(int $id, int $perPage, ?string $cursor): News
    {
        /** @var News $news */
        $news = $this->getById($id);

        $news->setRelation(
            'paginatedComments',
            $news->comments()
                ->whereNull('parent_id')
                ->with(['user', 'replies.user'])
                ->cursorPaginate($perPage, ['*'], 'cursor', $cursor)
        );

        return $news;
    }

    /**
     * Получить новости с постраничной пагинацией.
     *
     * @param int $perPage Количество записей на страницу.
     * @return LengthAwarePaginator
     */
    public function getPaginated(int $perPage): LengthAwarePaginator
    {
        return News::query()->paginate($perPage);
    }
}
