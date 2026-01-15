<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Exceptions\NotFoundException;
use App\Services\News\DTO\CreateNewsDto;
use App\Services\News\DTO\NewsOutputDto;
use App\Services\News\DTO\UpdateNewsDto;
use App\Services\News\Models\News;
use App\Services\News\NewsService;
use App\Services\News\Repositories\NewsRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use PHPUnit\Framework\MockObject\MockObject;
use Tests\TestCase;

/**
 * Unit-тесты для NewsService.
 * Все зависимости мокируются — база данных не используется.
 */
class NewsServiceTest extends TestCase
{
    /** @var NewsRepositoryInterface&MockObject */
    private NewsRepositoryInterface $repository;

    private NewsService $service;

    /**
     * Инициализация мока репозитория и сервиса перед каждым тестом.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = $this->createMock(NewsRepositoryInterface::class);
        $this->service    = new NewsService($this->repository);
    }

    /**
     * Создать stub-модель News с заданными атрибутами без обращения к БД.
     *
     * @param array<string, mixed> $attributes Атрибуты модели.
     * @return News
     */
    private function makeNewsModel(array $attributes = []): News
    {
        News::unguard();

        $defaults = [
            'id'          => 1,
            'title'       => 'Тестовый заголовок',
            'description' => 'Тестовое описание',
            'created_at'  => Carbon::now(),
            'updated_at'  => Carbon::now(),
        ];

        $model = new News(array_merge($defaults, $attributes));
        // Принудительно устанавливаем первичный ключ, так как unguard не затрагивает id напрямую
        $model->id = $attributes['id'] ?? $defaults['id'];

        News::reguard();

        return $model;
    }

    /**
     * getAll() должен вернуть коллекцию NewsOutputDto,
     * соответствующую моделям из репозитория.
     */
    public function test_get_all_returns_collection_of_dtos(): void
    {
        $model1 = $this->makeNewsModel(['id' => 1, 'title' => 'Новость 1', 'description' => 'Описание 1']);
        $model2 = $this->makeNewsModel(['id' => 2, 'title' => 'Новость 2', 'description' => 'Описание 2']);

        $this->repository
            ->expects($this->once())
            ->method('getAll')
            ->willReturn(new Collection([$model1, $model2]));

        $result = $this->service->getAll();

        $this->assertInstanceOf(Collection::class, $result);
        $this->assertCount(2, $result);
        $this->assertContainsOnlyInstancesOf(NewsOutputDto::class, $result);
        $this->assertEquals('Новость 1', $result->first()->title);
        $this->assertEquals('Новость 2', $result->last()->title);
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
     * getByIdWithComments() должен вернуть NewsOutputDto
     * при успешном нахождении новости в репозитории.
     */
    public function test_get_by_id_with_comments_returns_dto(): void
    {
        $model = $this->makeNewsModel(['id' => 5, 'title' => 'Заголовок с комментариями']);

        $this->repository
            ->expects($this->once())
            ->method('findWithPaginatedComments')
            ->with(5, 10, null)
            ->willReturn($model);

        $result = $this->service->getByIdWithComments(5, 10, null);

        $this->assertInstanceOf(NewsOutputDto::class, $result);
        $this->assertEquals(5, $result->id);
        $this->assertEquals('Заголовок с комментариями', $result->title);
    }

    /**
     * create() должен передать в репозиторий массив с полями title и description
     * и вернуть NewsOutputDto с корректными данными.
     */
    public function test_create_returns_dto(): void
    {
        $dto   = new CreateNewsDto(title: 'Новая новость', description: 'Текст новости');
        $model = $this->makeNewsModel(['id' => 10, 'title' => 'Новая новость', 'description' => 'Текст новости']);

        $this->repository
            ->expects($this->once())
            ->method('create')
            ->with([
                'title'       => 'Новая новость',
                'description' => 'Текст новости',
            ])
            ->willReturn($model);

        $result = $this->service->create($dto);

        $this->assertInstanceOf(NewsOutputDto::class, $result);
        $this->assertEquals(10, $result->id);
        $this->assertEquals('Новая новость', $result->title);
        $this->assertEquals('Текст новости', $result->description);
    }

    /**
     * update() должен вызвать репозиторий с id и массивом изменённых полей,
     * вернуть обновлённый NewsOutputDto.
     */
    public function test_update_returns_dto(): void
    {
        $dto   = new UpdateNewsDto(title: 'Обновлённый заголовок', description: null);
        $model = $this->makeNewsModel(['id' => 3, 'title' => 'Обновлённый заголовок', 'description' => 'Старое описание']);

        $this->repository
            ->expects($this->once())
            ->method('update')
            ->with(3, ['title' => 'Обновлённый заголовок'])
            ->willReturn($model);

        $result = $this->service->update(3, $dto);

        $this->assertInstanceOf(NewsOutputDto::class, $result);
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
            ->willThrowException(new NotFoundException('News', 999));

        $this->expectException(NotFoundException::class);
        $this->expectExceptionMessage('News with ID 999 not found.');

        $this->service->getByIdWithComments(999, 10, null);
    }
}
