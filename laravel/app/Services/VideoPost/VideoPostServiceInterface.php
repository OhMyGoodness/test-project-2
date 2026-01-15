<?php

declare(strict_types=1);

namespace App\Services\VideoPost;

use App\Exceptions\NotFoundException;
use App\Services\VideoPost\DTO\CreateVideoPostDto;
use App\Services\VideoPost\DTO\UpdateVideoPostDto;
use App\Services\VideoPost\DTO\VideoPostOutputDto;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

/**
 * Контракт сервиса бизнес-логики для домена VideoPost.
 * Позволяет подменять реализацию без изменения контроллера.
 */
interface VideoPostServiceInterface
{
    /**
     * Получить все видеопосты в виде коллекции DTO.
     *
     * @return Collection<int, VideoPostOutputDto>
     */
    public function getAll(): Collection;

    /**
     * Получить видеопосты с постраничной пагинацией.
     *
     * @param int $perPage Количество записей на страницу.
     * @return LengthAwarePaginator
     */
    public function getPaginated(int $perPage): LengthAwarePaginator;

    /**
     * Получить видеопост по ID вместе с пагинированными комментариями.
     *
     * @param int         $id      Идентификатор видеопоста.
     * @param int         $perPage Количество комментариев на страницу.
     * @param string|null $cursor  Курсор для пагинации комментариев.
     * @return VideoPostOutputDto
     * @throws NotFoundException Если видеопост не найден.
     */
    public function getByIdWithComments(int $id, int $perPage, ?string $cursor): VideoPostOutputDto;

    /**
     * Создать новый видеопост.
     *
     * @param CreateVideoPostDto $dto Данные для создания видеопоста.
     * @return VideoPostOutputDto
     */
    public function create(CreateVideoPostDto $dto): VideoPostOutputDto;

    /**
     * Обновить видеопост.
     *
     * @param int                $id  Идентификатор видеопоста.
     * @param UpdateVideoPostDto $dto Данные для обновления видеопоста.
     * @return VideoPostOutputDto
     * @throws NotFoundException Если видеопост не найден.
     */
    public function update(int $id, UpdateVideoPostDto $dto): VideoPostOutputDto;

    /**
     * Удалить видеопост.
     *
     * @param int $id Идентификатор видеопоста.
     * @return void
     * @throws NotFoundException Если видеопост не найден.
     */
    public function delete(int $id): void;
}
