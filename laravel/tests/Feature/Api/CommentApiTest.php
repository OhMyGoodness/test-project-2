<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Feature-тесты для Comment API (/api/comments).
 * Проверяют создание, обновление, удаление комментариев,
 * валидацию входных данных и их отображение в ответах родительских сущностей.
 */
class CommentApiTest extends TestCase
{
    use RefreshDatabase;

    // -------------------------------------------------------------------------
    // Вспомогательные методы
    // -------------------------------------------------------------------------

    /**
     * Создать пользователя через фабрику и вернуть его ID.
     *
     * @return int
     */
    private function createUser(): int
    {
        return User::factory()->create()->id;
    }

    /**
     * Создать новость через API и вернуть её ID.
     *
     * @return int
     */
    private function createNews(): int
    {
        $response = $this->postJson('/api/news', [
            'title'       => 'Тестовая новость',
            'description' => 'Описание тестовой новости',
        ]);

        return (int) $response->json('data.id');
    }

    /**
     * Создать видеопост через API и вернуть его ID.
     *
     * @return int
     */
    private function createVideoPost(): int
    {
        $response = $this->postJson('/api/video-posts', [
            'title'       => 'Тестовый видеопост',
            'description' => 'Описание тестового видеопоста',
        ]);

        return (int) $response->json('data.id');
    }

    /**
     * Создать комментарий к новости через API и вернуть его ID.
     *
     * @param int         $userId   ID пользователя.
     * @param int         $newsId   ID новости.
     * @param string      $text     Текст комментария.
     * @param int|null    $parentId ID родительского комментария (для ответов).
     * @return int
     */
    private function createCommentForNews(
        int $userId,
        int $newsId,
        string $text = 'Тестовый комментарий',
        ?int $parentId = null,
    ): int {
        $payload = [
            'user_id'          => $userId,
            'commentable_type' => 'news',
            'commentable_id'   => $newsId,
            'text'             => $text,
        ];

        if ($parentId !== null) {
            $payload['parent_id'] = $parentId;
        }

        $response = $this->postJson('/api/comments', $payload);

        return (int) $response->json('data.id');
    }

    // -------------------------------------------------------------------------
    // Тесты store
    // -------------------------------------------------------------------------

    /**
     * POST /api/comments создаёт комментарий к новости.
     * Ожидается: 201, data.text совпадает с переданным.
     */
    public function test_store_creates_comment(): void
    {
        $userId = $this->createUser();
        $newsId = $this->createNews();

        $response = $this->postJson('/api/comments', [
            'user_id'          => $userId,
            'commentable_type' => 'news',
            'commentable_id'   => $newsId,
            'text'             => 'Отличная новость!',
        ]);

        $response->assertStatus(201);
        $response->assertJsonStructure([
            'data' => [
                'id',
                'user_id',
                'commentable_type',
                'commentable_id',
                'parent_id',
                'text',
                'replies',
                'created_at',
                'updated_at',
            ],
        ]);
        $response->assertJsonPath('data.text', 'Отличная новость!');
        $response->assertJsonPath('data.commentable_type', 'news');
        $response->assertJsonPath('data.commentable_id', $newsId);
    }

    /**
     * POST /api/comments с parent_id создаёт ответ на комментарий.
     * Ожидается: 201, data.parent_id совпадает с ID родительского комментария.
     */
    public function test_store_creates_reply(): void
    {
        $userId   = $this->createUser();
        $newsId   = $this->createNews();
        $parentId = $this->createCommentForNews($userId, $newsId, 'Родительский комментарий');

        $response = $this->postJson('/api/comments', [
            'user_id'          => $userId,
            'commentable_type' => 'news',
            'commentable_id'   => $newsId,
            'parent_id'        => $parentId,
            'text'             => 'Ответ на комментарий',
        ]);

        $response->assertStatus(201);
        $response->assertJsonPath('data.parent_id', $parentId);
        $response->assertJsonPath('data.text', 'Ответ на комментарий');
    }

    /**
     * POST /api/comments с commentable_type=invalid возвращает 422.
     * Ожидается: ошибка валидации для поля commentable_type.
     */
    public function test_store_validates_commentable_type(): void
    {
        $userId = $this->createUser();

        $response = $this->postJson('/api/comments', [
            'user_id'          => $userId,
            'commentable_type' => 'invalid',
            'commentable_id'   => 1,
            'text'             => 'Текст комментария',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['commentable_type']);
    }

    /**
     * POST /api/comments с несуществующим commentable_id возвращает 422.
     * Ожидается: ошибка валидации для поля commentable_id (сущность не найдена).
     */
    public function test_store_validates_commentable_id_exists(): void
    {
        $userId = $this->createUser();

        $response = $this->postJson('/api/comments', [
            'user_id'          => $userId,
            'commentable_type' => 'news',
            'commentable_id'   => 9999,
            'text'             => 'Текст комментария',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['commentable_id']);
    }

    /**
     * POST /api/comments с пустым телом возвращает 422.
     * Ожидается: ошибки валидации для всех обязательных полей.
     */
    public function test_store_validates_required_fields(): void
    {
        $response = $this->postJson('/api/comments', []);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['user_id', 'commentable_type', 'commentable_id', 'text']);
    }

    // -------------------------------------------------------------------------
    // Тесты update
    // -------------------------------------------------------------------------

    /**
     * PUT /api/comments/{id} обновляет текст комментария.
     * Ожидается: 200, data.text совпадает с новым значением.
     */
    public function test_update_modifies_comment(): void
    {
        $userId    = $this->createUser();
        $newsId    = $this->createNews();
        $commentId = $this->createCommentForNews($userId, $newsId, 'Исходный текст');

        $response = $this->putJson("/api/comments/{$commentId}", [
            'text' => 'Обновлённый текст комментария',
        ]);

        $response->assertStatus(200);
        $response->assertJsonPath('data.text', 'Обновлённый текст комментария');
    }

    /**
     * PUT /api/comments/9999 для несуществующего комментария возвращает 404.
     */
    public function test_update_returns_404_for_nonexistent(): void
    {
        $response = $this->putJson('/api/comments/9999', [
            'text' => 'Любой текст',
        ]);

        $response->assertStatus(404);
    }

    // -------------------------------------------------------------------------
    // Тесты destroy
    // -------------------------------------------------------------------------

    /**
     * DELETE /api/comments/{id} удаляет комментарий.
     * Ожидается: 204. Повторное удаление возвращает 404.
     */
    public function test_destroy_deletes_comment(): void
    {
        $userId    = $this->createUser();
        $newsId    = $this->createNews();
        $commentId = $this->createCommentForNews($userId, $newsId);

        $this->deleteJson("/api/comments/{$commentId}")->assertStatus(204);

        // Повторное удаление — комментарий уже не существует
        $this->deleteJson("/api/comments/{$commentId}")->assertStatus(404);
    }

    /**
     * DELETE /api/comments/9999 для несуществующего комментария возвращает 404.
     */
    public function test_destroy_returns_404_for_nonexistent(): void
    {
        $response = $this->deleteJson('/api/comments/9999');

        $response->assertStatus(404);
    }

    // -------------------------------------------------------------------------
    // Интеграционный тест: комментарии в ответе новости
    // -------------------------------------------------------------------------

    /**
     * GET /api/news/{id} возвращает созданный комментарий в блоке comments.data.
     * Ожидается: data.comments.data содержит 1 элемент с корректным текстом.
     */
    public function test_comments_appear_in_news_show(): void
    {
        $userId = $this->createUser();
        $newsId = $this->createNews();

        $this->createCommentForNews($userId, $newsId, 'Видимый комментарий');

        $response = $this->getJson("/api/news/{$newsId}");

        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data.comments.data'));
        $response->assertJsonPath('data.comments.data.0.text', 'Видимый комментарий');
    }
}
