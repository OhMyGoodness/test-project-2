<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\Exceptions\NotFoundException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

/**
 * Базовый интерфейс репозитория.
 * Определяет контракт CRUD-операций для всех репозиториев приложения.
 */
interface RepositoryInterface
{
    /**
     * Получить все записи.
     *
     * @return Collection<int, Model>
     */
    public function getAll(): Collection;

    /**
     * Найти запись по первичному ключу.
     *
     * @param int $id Идентификатор записи.
     * @return Model
     * @throws NotFoundException Если запись не найдена.
     */
    public function getById(int $id): Model;

    /**
     * Создать новую запись.
     *
     * @param array<string, mixed> $data Данные для создания.
     * @return Model
     */
    public function create(array $data): Model;

    /**
     * Обновить существующую запись по ID.
     *
     * @param int                  $id   Идентификатор записи.
     * @param array<string, mixed> $data Данные для обновления.
     * @return Model
     * @throws NotFoundException Если запись не найдена.
     */
    public function update(int $id, array $data): Model;

    /**
     * Удалить запись по ID.
     *
     * @param int $id Идентификатор записи.
     * @return void
     * @throws NotFoundException Если запись не найдена.
     */
    public function delete(int $id): void;
}
