<?php

declare(strict_types=1);

namespace App\Services\News\Repositories;

use App\Exceptions\NotFoundException;
use App\Repositories\Contracts\RepositoryInterface;
use App\Services\News\Models\News;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

/**
 * Контракт репозитория для работы с новостями.
 * Расширяет базовый интерфейс специфичными методами домена News.
 */
interface NewsRepositoryInterface extends RepositoryInterface
{
    /**
     * Найти новость с курсорной пагинацией комментариев верхнего уровня.
     *
     * @param int         $id      Идентификатор новости.
     * @param int         $perPage Количество комментариев на страницу.
     * @param string|null $cursor  Курсор для пагинации.
     * @return News
     * @throws NotFoundException Если новость не найдена.
     */
    public function findWithPaginatedComments(int $id, int $perPage, ?string $cursor): News;

    /**
     * Получить новости с постраничной пагинацией.
     *
     * @param int $perPage Количество записей на страницу.
     * @return LengthAwarePaginator
     */
    public function getPaginated(int $perPage): LengthAwarePaginator;
}
