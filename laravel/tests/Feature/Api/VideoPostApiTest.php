<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Feature-тесты для VideoPost API (/api/video-posts).
 * Проверяют CRUD-операции, пагинацию, валидацию и коды HTTP-ответов.
 */
class VideoPostApiTest extends TestCase
{
    use RefreshDatabase;

    // -------------------------------------------------------------------------
    // Вспомогательные методы
    // -------------------------------------------------------------------------

    /**
     * Создать видеопост через API и вернуть его ID.
     *
     * @param string $title       Заголовок видеопоста.
     * @param string $description Описание видеопоста.
     * @return int
     */
    private function createVideoPost(string $title = 'Тестовый заголовок', string $description = 'Тестовое описание'): int
    {
        $response = $this->postJson('/api/video-posts', [
            'title'       => $title,
            'description' => $description,
        ]);

        return (int) $response->json('data.id');
    }

    // -------------------------------------------------------------------------
    // Тесты index
    // -------------------------------------------------------------------------

    /**
     * GET /api/video-posts возвращает пагинированный список видеопостов.
     * Ожидается: 200, data содержит 2 элемента, присутствуют meta и links.
     */
    public function test_index_returns_paginated_video_posts(): void
    {
        $this->createVideoPost('Видео первое', 'Описание первого');
        $this->createVideoPost('Видео второе', 'Описание второго');

        $response = $this->getJson('/api/video-posts');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                '*' => ['id', 'title', 'description', 'created_at', 'updated_at'],
            ],
            'links' => ['first', 'last', 'prev', 'next'],
            'meta'  => ['current_page', 'last_page', 'per_page', 'total'],
        ]);
        $this->assertCount(2, $response->json('data'));
    }

    /**
     * GET /api/video-posts?per_page=2 поддерживает параметр per_page.
     * Ожидается: data содержит 2 элемента, meta.total = 3.
     */
    public function test_index_supports_per_page(): void
    {
        $this->createVideoPost('Видео 1', 'Описание 1');
        $this->createVideoPost('Видео 2', 'Описание 2');
        $this->createVideoPost('Видео 3', 'Описание 3');

        $response = $this->getJson('/api/video-posts?per_page=2');

        $response->assertStatus(200);
        $this->assertCount(2, $response->json('data'));
        $response->assertJsonPath('meta.total', 3);
    }

    // -------------------------------------------------------------------------
    // Тесты store
    // -------------------------------------------------------------------------

    /**
     * POST /api/video-posts создаёт видеопост и возвращает 201.
     * Ожидается: data.title и data.description совпадают с переданными.
     */
    public function test_store_creates_video_post(): void
    {
        $response = $this->postJson('/api/video-posts', [
            'title'       => 'Невероятное видео',
            'description' => 'Посмотрите это видео',
        ]);

        $response->assertStatus(201);
        $response->assertJsonStructure([
            'data' => ['id', 'title', 'description', 'created_at', 'updated_at'],
        ]);
        $response->assertJsonPath('data.title', 'Невероятное видео');
        $response->assertJsonPath('data.description', 'Посмотрите это видео');
    }

    /**
     * POST /api/video-posts с пустым телом возвращает 422.
     * Ожидается: ошибки валидации для обязательных полей title и description.
     */
    public function test_store_validates_required_fields(): void
    {
        $response = $this->postJson('/api/video-posts', []);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['title', 'description']);
    }

    // -------------------------------------------------------------------------
    // Тесты show
    // -------------------------------------------------------------------------

    /**
     * GET /api/video-posts/{id} возвращает видеопост с пустым блоком комментариев.
     * Ожидается: 200, data.comments.data является пустым массивом.
     */
    public function test_show_returns_video_post_with_comments(): void
    {
        $id = $this->createVideoPost();

        $response = $this->getJson("/api/video-posts/{$id}");

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                'id',
                'title',
                'description',
                'created_at',
                'updated_at',
                'comments' => [
                    'data',
                    'next_cursor',
                    'prev_cursor',
                    'per_page',
                    'path',
                ],
            ],
        ]);
        $this->assertIsArray($response->json('data.comments.data'));
        $this->assertCount(0, $response->json('data.comments.data'));
    }

    /**
     * GET /api/video-posts/9999 для несуществующего видеопоста возвращает 404.
     * Ожидается: message содержит "not found".
     */
    public function test_show_returns_404_for_nonexistent(): void
    {
        $response = $this->getJson('/api/video-posts/9999');

        $response->assertStatus(404);
        $this->assertStringContainsStringIgnoringCase(
            'not found',
            (string) $response->json('message'),
        );
    }

    // -------------------------------------------------------------------------
    // Тесты update
    // -------------------------------------------------------------------------

    /**
     * PUT /api/video-posts/{id} обновляет поля видеопоста.
     * Ожидается: 200, data.title и data.description совпадают с новыми значениями.
     */
    public function test_update_modifies_video_post(): void
    {
        $id = $this->createVideoPost('Старый заголовок', 'Старое описание');

        $response = $this->putJson("/api/video-posts/{$id}", [
            'title'       => 'Обновлённый заголовок',
            'description' => 'Обновлённое описание',
        ]);

        $response->assertStatus(200);
        $response->assertJsonPath('data.title', 'Обновлённый заголовок');
        $response->assertJsonPath('data.description', 'Обновлённое описание');
    }

    /**
     * PUT /api/video-posts/9999 для несуществующего видеопоста возвращает 404.
     */
    public function test_update_returns_404_for_nonexistent(): void
    {
        $response = $this->putJson('/api/video-posts/9999', [
            'title' => 'Любой заголовок',
        ]);

        $response->assertStatus(404);
    }

    // -------------------------------------------------------------------------
    // Тесты destroy
    // -------------------------------------------------------------------------

    /**
     * DELETE /api/video-posts/{id} удаляет видеопост.
     * Ожидается: 204, повторный GET возвращает 404.
     */
    public function test_destroy_deletes_video_post(): void
    {
        $id = $this->createVideoPost();

        $this->deleteJson("/api/video-posts/{$id}")->assertStatus(204);
        $this->getJson("/api/video-posts/{$id}")->assertStatus(404);
    }

    /**
     * DELETE /api/video-posts/9999 для несуществующего видеопоста возвращает 404.
     */
    public function test_destroy_returns_404_for_nonexistent(): void
    {
        $response = $this->deleteJson('/api/video-posts/9999');

        $response->assertStatus(404);
    }
}
