<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Exceptions\NotFoundException;
use App\Repositories\Contracts\RepositoryInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Collection;

/**
 * Базовая Eloquent-реализация репозитория.
 * Содержит универсальную реализацию CRUD-операций через Eloquent.
 * Конкретные репозитории наследуют этот класс и указывают модель через метод model().
 */
abstract class AbstractEloquentRepository implements RepositoryInterface
{
    /**
     * Вернуть FQCN модели, с которой работает репозиторий.
     *
     * @return class-string<Model>
     */
    abstract protected function model(): string;

    /**
     * Получить все записи.
     *
     * @return Collection<int, Model>
     */
    public function getAll(): Collection
    {
        return $this->model()::query()->get();
    }

    /**
     * Найти запись по первичному ключу.
     *
     * @param int $id Идентификатор записи.
     * @return Model
     * @throws NotFoundException Если запись не найдена.
     */
    public function getById(int $id): Model
    {
        try {
            return $this->model()::query()->findOrFail($id);
        } catch (ModelNotFoundException) {
            throw new NotFoundException($this->resolveResourceName(), $id);
        }
    }

    /**
     * Создать новую запись.
     *
     * @param array<string, mixed> $data Данные для создания.
     * @return Model
     */
    public function create(array $data): Model
    {
        return $this->model()::query()->create($data);
    }

    /**
     * Обновить существующую запись по ID.
     *
     * @param int                  $id   Идентификатор записи.
     * @param array<string, mixed> $data Данные для обновления.
     * @return Model
     * @throws NotFoundException Если запись не найдена.
     */
    public function update(int $id, array $data): Model
    {
        $model = $this->getById($id);
        $model->update($data);

        return $model->fresh();
    }

    /**
     * Удалить запись по ID.
     *
     * @param int $id Идентификатор записи.
     * @return void
     * @throws NotFoundException Если запись не найдена.
     */
    public function delete(int $id): void
    {
        $model = $this->getById($id);
        $model->delete();
    }

    /**
     * Извлечь читаемое название ресурса из FQCN модели.
     *
     * @return string
     */
    private function resolveResourceName(): string
    {
        $parts = explode('\\', $this->model());

        return end($parts);
    }
}
