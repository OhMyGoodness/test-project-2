<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Exceptions\NotFoundException;
use App\Services\Comment\CommentService;
use App\Services\Comment\DTO\CommentOutputDto;
use App\Services\Comment\DTO\CreateCommentDto;
use App\Services\Comment\DTO\UpdateCommentDto;
use App\Services\Comment\Models\Comment;
use App\Services\Comment\Repositories\CommentRepositoryInterface;
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use PHPUnit\Framework\MockObject\MockObject;
use Tests\TestCase;

/**
 * Unit-тесты для CommentService.
 * Все зависимости мокируются — база данных не используется.
 */
class CommentServiceTest extends TestCase
{
    /** @var CommentRepositoryInterface&MockObject */
    private CommentRepositoryInterface $repository;

    private CommentService $service;

    /**
     * Инициализация мока репозитория и сервиса перед каждым тестом.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = $this->createMock(CommentRepositoryInterface::class);
        $this->service    = new CommentService($this->repository);
    }

    /**
     * Создать stub-модель Comment с заданными атрибутами без обращения к БД.
     * Отношение replies принудительно устанавливается как пустая коллекция,
     * чтобы не вызывать запросы к БД при load('replies').
     *
     * @param array<string, mixed> $attributes Атрибуты модели.
     * @return Comment
     */
    private function makeCommentModel(array $attributes = []): Comment
    {
        Comment::unguard();

        $defaults = [
            'id'               => 1,
            'user_id'          => 1,
            'commentable_type' => 'news',
            'commentable_id'   => 1,
            'parent_id'        => null,
            'text'             => 'Тестовый комментарий',
            'created_at'       => Carbon::now(),
            'updated_at'       => Carbon::now(),
        ];

        $model     = new Comment(array_merge($defaults, $attributes));
        $model->id = $attributes['id'] ?? $defaults['id'];

        // Устанавливаем загруженное отношение replies, чтобы избежать обращения к БД
        $model->setRelation('replies', new EloquentCollection());

        Comment::reguard();

        return $model;
    }

    /**
     * create() должен передать репозиторию корректный массив данных
     * (включая commentable_type из DTO) и вернуть CommentOutputDto.
     */
    public function test_create_returns_dto(): void
    {
        $dto = new CreateCommentDto(
            userId: 1,
            commentableType: 'news',
            commentableId: 5,
            text: 'Отличная новость!',
            parentId: null,
        );

        $model = $this->makeCommentModel([
            'id'               => 1,
            'user_id'          => 1,
            'commentable_type' => 'news',
            'commentable_id'   => 5,
            'parent_id'        => null,
            'text'             => 'Отличная новость!',
        ]);

        $this->repository
            ->expects($this->once())
            ->method('create')
            ->with([
                'user_id'          => 1,
                'commentable_type' => 'news',
                'commentable_id'   => 5,
                'parent_id'        => null,
                'text'             => 'Отличная новость!',
            ])
            ->willReturn($model);

        $result = $this->service->create($dto);

        $this->assertInstanceOf(CommentOutputDto::class, $result);
        $this->assertEquals(1, $result->id);
        $this->assertEquals(1, $result->userId);
        $this->assertEquals('news', $result->commentableType);
        $this->assertEquals(5, $result->commentableId);
        $this->assertNull($result->parentId);
        $this->assertEquals('Отличная новость!', $result->text);
    }

    /**
     * create() должен выбросить ValueError при невалидном значении commentable_type,
     * поскольку CommentableType::from() не знает такого кейса.
     */
    public function test_create_validates_commentable_type_enum(): void
    {
        $dto = new CreateCommentDto(
            userId: 1,
            commentableType: 'invalid_type',
            commentableId: 1,
            text: 'Комментарий',
        );

        // Репозиторий не должен вызываться — сервис упадёт до него
        $this->repository
            ->expects($this->never())
            ->method('create');

        $this->expectException(\ValueError::class);

        $this->service->create($dto);
    }

    /**
     * update() должен вызвать репозиторий с id и массивом данных,
     * после чего вернуть CommentOutputDto с обновлёнными полями.
     */
    public function test_update_returns_dto(): void
    {
        $dto   = new UpdateCommentDto(text: 'Обновлённый текст');
        $model = $this->makeCommentModel([
            'id'               => 2,
            'user_id'          => 3,
            'commentable_type' => 'video_post',
            'commentable_id'   => 7,
            'parent_id'        => null,
            'text'             => 'Обновлённый текст',
        ]);

        $this->repository
            ->expects($this->once())
            ->method('update')
            ->with(2, ['text' => 'Обновлённый текст'])
            ->willReturn($model);

        $result = $this->service->update(2, $dto);

        $this->assertInstanceOf(CommentOutputDto::class, $result);
        $this->assertEquals(2, $result->id);
        $this->assertEquals('Обновлённый текст', $result->text);
        $this->assertEquals('video_post', $result->commentableType);
    }

    /**
     * delete() должен делегировать вызов deleteWithReplies репозитория с корректным id.
     */
    public function test_delete_calls_delete_with_replies(): void
    {
        $this->repository
            ->expects($this->once())
            ->method('deleteWithReplies')
            ->with(4);

        $this->service->delete(4);
    }

    /**
     * delete() должен пробросить NotFoundException,
     * если репозиторий выбросил исключение при удалении.
     */
    public function test_delete_throws_not_found(): void
    {
        $this->repository
            ->expects($this->once())
            ->method('deleteWithReplies')
            ->with(999)
            ->willThrowException(new NotFoundException('Comment', 999));

        $this->expectException(NotFoundException::class);
        $this->expectExceptionMessage('Comment with ID 999 not found.');

        $this->service->delete(999);
    }
}
