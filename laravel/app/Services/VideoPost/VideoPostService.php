<?php

declare(strict_types=1);

namespace App\Services\VideoPost;

use App\Services\Shared\AbstractContentService;
use App\Services\VideoPost\DTO\CreateVideoPostDto;
use App\Services\VideoPost\DTO\UpdateVideoPostDto;
use App\Services\VideoPost\DTO\VideoPostOutputDto;
use App\Services\VideoPost\Repositories\VideoPostRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

/**
 * Сервис бизнес-логики для домена VideoPost.
 * Наследует общие операции из AbstractContentService.
 * Работает исключительно через VideoPostRepositoryInterface.
 */
class VideoPostService extends AbstractContentService implements VideoPostServiceInterface
{
    /**
     * @param VideoPostRepositoryInterface $repository Репозиторий видеопостов.
     */
    public function __construct(
        private readonly VideoPostRepositoryInterface $repository
    ) {
    }

    /**
     * Вернуть репозиторий видеопостов.
     *
     * @return VideoPostRepositoryInterface
     */
    protected function repository(): VideoPostRepositoryInterface
    {
        return $this->repository;
    }

    /**
     * Преобразовать Eloquent-модель в VideoPostOutputDto.
     *
     * @param Model $model Eloquent-модель видеопоста.
     * @return VideoPostOutputDto
     */
    protected function toOutputDto(Model $model): VideoPostOutputDto
    {
        return VideoPostOutputDto::fromModel($model);
    }

    /**
     * Получить видеопосты с постраничной пагинацией.
     *
     * @param int $perPage Количество записей на страницу.
     * @return LengthAwarePaginator
     */
    public function getPaginated(int $perPage): LengthAwarePaginator
    {
        return $this->repository->getPaginated($perPage);
    }

    /**
     * Получить видеопост по ID вместе с пагинированными комментариями.
     *
     * @param int         $id      Идентификатор видеопоста.
     * @param int         $perPage Количество комментариев на страницу.
     * @param string|null $cursor  Курсор для пагинации комментариев.
     * @return VideoPostOutputDto
     */
    public function getByIdWithComments(int $id, int $perPage, ?string $cursor): VideoPostOutputDto
    {
        $videoPost = $this->repository->findWithPaginatedComments($id, $perPage, $cursor);

        return $this->toOutputDto($videoPost);
    }

    /**
     * Создать новый видеопост.
     *
     * @param CreateVideoPostDto $dto Данные для создания.
     * @return VideoPostOutputDto
     */
    public function create(CreateVideoPostDto $dto): VideoPostOutputDto
    {
        /** @var VideoPostOutputDto $result */
        $result = $this->performCreate([
            'title'       => $dto->title,
            'description' => $dto->description,
        ]);

        return $result;
    }

    /**
     * Обновить видеопост.
     *
     * @param int                $id  Идентификатор видеопоста.
     * @param UpdateVideoPostDto $dto Данные для обновления.
     * @return VideoPostOutputDto
     */
    public function update(int $id, UpdateVideoPostDto $dto): VideoPostOutputDto
    {
        /** @var VideoPostOutputDto $result */
        $result = $this->performUpdate($id, $dto->toArray());

        return $result;
    }

    /**
     * Получить все видеопосты в виде коллекции DTO.
     *
     * @return Collection<int, VideoPostOutputDto>
     */
    public function getAll(): Collection
    {
        return parent::getAll();
    }

    /**
     * Удалить видеопост.
     *
     * @param int $id Идентификатор видеопоста.
     * @return void
     */
    public function delete(int $id): void
    {
        parent::delete($id);
    }
}
