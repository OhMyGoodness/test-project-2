<?php

declare(strict_types=1);

namespace App\Services\VideoPost\Repositories;

use App\Exceptions\NotFoundException;
use App\Repositories\Contracts\RepositoryInterface;
use App\Services\VideoPost\Models\VideoPost;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

/**
 * Контракт репозитория для работы с видеопостами.
 * Расширяет базовый интерфейс специфичными методами домена VideoPost.
 */
interface VideoPostRepositoryInterface extends RepositoryInterface
{
    /**
     * Найти видеопост с курсорной пагинацией комментариев верхнего уровня.
     *
     * @param int         $id      Идентификатор видеопоста.
     * @param int         $perPage Количество комментариев на страницу.
     * @param string|null $cursor  Курсор для пагинации.
     * @return VideoPost
     * @throws NotFoundException Если видеопост не найден.
     */
    public function findWithPaginatedComments(int $id, int $perPage, ?string $cursor): VideoPost;

    /**
     * Получить видеопосты с постраничной пагинацией.
     *
     * @param int $perPage Количество записей на страницу.
     * @return LengthAwarePaginator
     */
    public function getPaginated(int $perPage): LengthAwarePaginator;
}
