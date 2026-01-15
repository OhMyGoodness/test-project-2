<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Exceptions\NotFoundException;
use App\Services\VideoPost\DTO\CreateVideoPostDto;
use App\Services\VideoPost\DTO\UpdateVideoPostDto;
use App\Services\VideoPost\DTO\VideoPostOutputDto;
use App\Services\VideoPost\Models\VideoPost;
use App\Services\VideoPost\Repositories\VideoPostRepositoryInterface;
use App\Services\VideoPost\VideoPostService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use PHPUnit\Framework\MockObject\MockObject;
use Tests\TestCase;

/**
 * Unit-тесты для VideoPostService.
 * Все зависимости мокируются — база данных не используется.
 */
class VideoPostServiceTest extends TestCase
{
    /** @var VideoPostRepositoryInterface&MockObject */
    private VideoPostRepositoryInterface $repository;

    private VideoPostService $service;

    /**
     * Инициализация мока репозитория и сервиса перед каждым тестом.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = $this->createMock(VideoPostRepositoryInterface::class);
        $this->service    = new VideoPostService($this->repository);
    }

    /**
     * Создать stub-модель VideoPost с заданными атрибутами без обращения к БД.
     *
     * @param array<string, mixed> $attributes Атрибуты модели.
     * @return VideoPost
     */
    private function makeVideoPostModel(array $attributes = []): VideoPost
    {
        VideoPost::unguard();

        $defaults = [
            'id'          => 1,
            'title'       => 'Тестовый видеопост',
            'description' => 'Описание видеопоста',
            'created_at'  => Carbon::now(),
            'updated_at'  => Carbon::now(),
        ];

        $model     = new VideoPost(array_merge($defaults, $attributes));
        $model->id = $attributes['id'] ?? $defaults['id'];

        VideoPost::reguard();

        return $model;
    }

    /**
     * getAll() должен вернуть коллекцию VideoPostOutputDto,
     * соответствующую моделям из репозитория.
     */
    public function test_get_all_returns_collection_of_dtos(): void
    {
        $model1 = $this->makeVideoPostModel(['id' => 1, 'title' => 'Видео 1', 'description' => 'Описание 1']);
        $model2 = $this->makeVideoPostModel(['id' => 2, 'title' => 'Видео 2', 'description' => 'Описание 2']);

        $this->repository
            ->expects($this->once())
            ->method('getAll')
            ->willReturn(new Collection([$model1, $model2]));

        $result = $this->service->getAll();

        $this->assertInstanceOf(Collection::class, $result);
        $this->assertCount(2, $result);
        $this->assertContainsOnlyInstancesOf(VideoPostOutputDto::class, $result);
        $this->assertEquals('Видео 1', $result->first()->title);
        $this->assertEquals('Видео 2', $result->last()->title);
    }

    /**
     * getPaginated() должен делегировать вызов репозиторию с правильным параметром perPage.
     */
    public function test_get_paginated_delegates_to_repository(): void
    {
        $paginator = $this->createMock(LengthAwarePaginator::class);

        $this->repository
            ->expects($this->once())
            ->method('getPaginated')
            ->with(15)
            ->willReturn($paginator);

        $result = $this->service->getPaginated(15);

        $this->assertSame($paginator, $result);
    }

    /**
     * getByIdWithComments() должен вернуть VideoPostOutputDto
     * при успешном нахождении видеопоста в репозитории.
     */
    public function test_get_by_id_with_comments_returns_dto(): void
    {
        $model = $this->makeVideoPostModel(['id' => 5, 'title' => 'Видео с комментариями']);

        $this->repository
            ->expects($this->once())
            ->method('findWithPaginatedComments')
            ->with(5, 10, null)
            ->willReturn($model);

        $result = $this->service->getByIdWithComments(5, 10, null);

        $this->assertInstanceOf(VideoPostOutputDto::class, $result);
        $this->assertEquals(5, $result->id);
        $this->assertEquals('Видео с комментариями', $result->title);
    }

    /**
     * create() должен передать в репозиторий массив с полями title и description
     * и вернуть VideoPostOutputDto с корректными данными.
     */
    public function test_create_returns_dto(): void
    {
        $dto   = new CreateVideoPostDto(title: 'Новое видео', description: 'Описание нового видео');
        $model = $this->makeVideoPostModel(['id' => 10, 'title' => 'Новое видео', 'description' => 'Описание нового видео']);

        $this->repository
            ->expects($this->once())
            ->method('create')
            ->with([
                'title'       => 'Новое видео',
                'description' => 'Описание нового видео',
            ])
            ->willReturn($model);

        $result = $this->service->create($dto);

        $this->assertInstanceOf(VideoPostOutputDto::class, $result);
        $this->assertEquals(10, $result->id);
        $this->assertEquals('Новое видео', $result->title);
        $this->assertEquals('Описание нового видео', $result->description);
    }

    /**
     * update() должен вызвать репозиторий с id и массивом изменённых полей,
     * вернуть обновлённый VideoPostOutputDto.
     */
    public function test_update_returns_dto(): void
    {
        $dto   = new UpdateVideoPostDto(title: 'Обновлённый заголовок', description: null);
        $model = $this->makeVideoPostModel(['id' => 3, 'title' => 'Обновлённый заголовок', 'description' => 'Старое описание']);

        $this->repository
            ->expects($this->once())
            ->method('update')
            ->with(3, ['title' => 'Обновлённый заголовок'])
            ->willReturn($model);

        $result = $this->service->update(3, $dto);

        $this->assertInstanceOf(VideoPostOutputDto::class, $result);
        $this->assertEquals(3, $result->id);
        $this->assertEquals('Обновлённый заголовок', $result->title);
    }

    /**
     * delete() должен делегировать вызов репозиторию с корректным id.
     */
    public function test_delete_delegates_to_repository(): void
    {
        $this->repository
            ->expects($this->once())
            ->method('delete')
            ->with(7);

        $this->service->delete(7);
    }

    /**
     * getByIdWithComments() должен пробросить NotFoundException,
     * если репозиторий выбросил исключение.
     */
    public function test_get_by_id_with_comments_throws_not_found(): void
    {
        $this->repository
            ->expects($this->once())
            ->method('findWithPaginatedComments')
            ->with(999, 10, null)
            ->willThrowException(new NotFoundException('VideoPost', 999));

        $this->expectException(NotFoundException::class);
        $this->expectExceptionMessage('VideoPost with ID 999 not found.');

        $this->service->getByIdWithComments(999, 10, null);
    }
}
