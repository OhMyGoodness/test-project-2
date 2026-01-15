<?php

declare(strict_types=1);

namespace App\Services\Shared;

use App\Repositories\Contracts\RepositoryInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

/**
 * Базовый абстрактный сервис для контентных сущностей.
 * Содержит обобщённую реализацию методов getAll, create, update, delete.
 * Конкретные сервисы (NewsService, VideoPostService) наследуют его
 * и предоставляют репозиторий и преобразование модели в DTO.
 */
abstract class AbstractContentService
{
    /**
     * Вернуть репозиторий, с которым работает сервис.
     *
     * @return RepositoryInterface
     */
    abstract protected function repository(): RepositoryInterface;

    /**
     * Преобразовать Eloquent-модель в OutputDto.
     *
     * @param Model $model Eloquent-модель для преобразования.
     * @return mixed
     */
    abstract protected function toOutputDto(Model $model): mixed;

    /**
     * Получить все записи в виде коллекции DTO.
     *
     * @return Collection<int, mixed>
     */
    public function getAll(): Collection
    {
        return $this->repository()->getAll()->map(
            fn (Model $model) => $this->toOutputDto($model)
        );
    }

    /**
     * Создать новую запись через репозиторий и вернуть OutputDto.
     *
     * @param array<string, mixed> $data Данные для создания.
     * @return mixed
     */
    protected function performCreate(array $data): mixed
    {
        $model = $this->repository()->create($data);

        return $this->toOutputDto($model);
    }

    /**
     * Обновить запись через репозиторий и вернуть OutputDto.
     *
     * @param int                  $id   Идентификатор записи.
     * @param array<string, mixed> $data Данные для обновления.
     * @return mixed
     */
    protected function performUpdate(int $id, array $data): mixed
    {
        $model = $this->repository()->update($id, $data);

        return $this->toOutputDto($model);
    }

    /**
     * Удалить запись по ID.
     *
     * @param int $id Идентификатор записи.
     * @return void
     */
    public function delete(int $id): void
    {
        $this->repository()->delete($id);
    }
}
