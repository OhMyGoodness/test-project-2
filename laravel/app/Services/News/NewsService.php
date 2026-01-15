<?php

declare(strict_types=1);

namespace App\Services\News;

use App\Services\News\DTO\CreateNewsDto;
use App\Services\News\DTO\NewsOutputDto;
use App\Services\News\DTO\UpdateNewsDto;
use App\Services\News\Repositories\NewsRepositoryInterface;
use App\Services\Shared\AbstractContentService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;

/**
 * Сервис для управления новостями.
 * Реализует бизнес-логику домена News.
 * Наследует общие CRUD-операции из AbstractContentService.
 */
class NewsService extends AbstractContentService implements NewsServiceInterface
{
    /**
     * @param NewsRepositoryInterface $repository Репозиторий новостей.
     */
    public function __construct(
        private readonly NewsRepositoryInterface $repository,
    ) {}

    /**
     * Вернуть репозиторий новостей.
     *
     * @return NewsRepositoryInterface
     */
    protected function repository(): NewsRepositoryInterface
    {
        return $this->repository;
    }

    /**
     * Преобразовать Eloquent-модель в DTO новости.
     *
     * @param Model $model Eloquent-модель новости.
     * @return NewsOutputDto
     */
    protected function toOutputDto(Model $model): NewsOutputDto
    {
        return NewsOutputDto::fromModel($model);
    }

    /**
     * Получить новости с постраничной пагинацией.
     *
     * @param int $perPage Количество записей на страницу.
     * @return LengthAwarePaginator
     */
    public function getPaginated(int $perPage): LengthAwarePaginator
    {
        return $this->repository->getPaginated($perPage);
    }

    /**
     * Получить новость по ID вместе с пагинированными комментариями.
     *
     * @param int         $id      Идентификатор новости.
     * @param int         $perPage Количество комментариев на страницу.
     * @param string|null $cursor  Курсор для пагинации.
     * @return NewsOutputDto
     */
    public function getByIdWithComments(int $id, int $perPage, ?string $cursor): NewsOutputDto
    {
        $news = $this->repository->findWithPaginatedComments($id, $perPage, $cursor);

        return NewsOutputDto::fromModel($news);
    }

    /**
     * Создать новую новость.
     *
     * @param CreateNewsDto $dto Данные для создания.
     * @return NewsOutputDto
     */
    public function create(CreateNewsDto $dto): NewsOutputDto
    {
        /** @var NewsOutputDto $result */
        $result = $this->performCreate([
            'title'       => $dto->title,
            'description' => $dto->description,
        ]);

        return $result;
    }

    /**
     * Обновить существующую новость.
     *
     * @param int           $id  Идентификатор новости.
     * @param UpdateNewsDto $dto Данные для обновления.
     * @return NewsOutputDto
     */
    public function update(int $id, UpdateNewsDto $dto): NewsOutputDto
    {
        /** @var NewsOutputDto $result */
        $result = $this->performUpdate($id, $dto->toArray());

        return $result;
    }
}
