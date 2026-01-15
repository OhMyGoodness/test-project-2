<?php

declare(strict_types=1);

namespace App\Services\News;

use App\Exceptions\NotFoundException;
use App\Services\News\DTO\CreateNewsDto;
use App\Services\News\DTO\NewsOutputDto;
use App\Services\News\DTO\UpdateNewsDto;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

/**
 * Контракт сервиса для работы с новостями.
 * Определяет бизнес-логику домена News.
 */
interface NewsServiceInterface
{
    /**
     * Получить все новости в виде коллекции DTO.
     *
     * @return Collection<int, NewsOutputDto>
     */
    public function getAll(): Collection;

    /**
     * Получить новости с постраничной пагинацией.
     *
     * @param int $perPage Количество записей на страницу.
     * @return LengthAwarePaginator
     */
    public function getPaginated(int $perPage): LengthAwarePaginator;

    /**
     * Получить новость по ID вместе с пагинированными комментариями.
     *
     * @param int         $id      Идентификатор новости.
     * @param int         $perPage Количество комментариев на страницу.
     * @param string|null $cursor  Курсор для пагинации.
     * @return NewsOutputDto
     * @throws NotFoundException Если новость не найдена.
     */
    public function getByIdWithComments(int $id, int $perPage, ?string $cursor): NewsOutputDto;

    /**
     * Создать новую новость.
     *
     * @param CreateNewsDto $dto Данные для создания.
     * @return NewsOutputDto
     */
    public function create(CreateNewsDto $dto): NewsOutputDto;

    /**
     * Обновить существующую новость.
     *
     * @param int           $id  Идентификатор новости.
     * @param UpdateNewsDto $dto Данные для обновления.
     * @return NewsOutputDto
     * @throws NotFoundException Если новость не найдена.
     */
    public function update(int $id, UpdateNewsDto $dto): NewsOutputDto;

    /**
     * Удалить новость по ID.
     *
     * @param int $id Идентификатор новости.
     * @return void
     * @throws NotFoundException Если новость не найдена.
     */
    public function delete(int $id): void;
}
