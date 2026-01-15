<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use App\Models\User;
use App\Services\News\Models\News;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Feature-тесты для News API (/api/news).
 * Проверяют CRUD-операции, пагинацию, валидацию и коды HTTP-ответов.
 */
class NewsApiTest extends TestCase
{
    use RefreshDatabase;

    // -------------------------------------------------------------------------
    // Вспомогательные методы
    // -------------------------------------------------------------------------

    /**
     * Создать новость через API и вернуть её ID.
     *
     * @param string $title       Заголовок новости.
     * @param string $description Описание новости.
     * @return int
     */
    private function createNews(string $title = 'Тестовый заголовок', string $description = 'Тестовое описание'): int
    {
        $response = $this->postJson('/api/news', [
            'title'       => $title,
            'description' => $description,
        ]);

        return (int) $response->json('data.id');
    }

    // -------------------------------------------------------------------------
    // Тесты index
    // -------------------------------------------------------------------------

    /**
     * GET /api/news возвращает пагинированный список новостей.
     * Ожидается: 200, data содержит 2 элемента, присутствуют meta и links.
     */
    public function test_index_returns_paginated_news(): void
    {
        $this->createNews('Новость первая', 'Описание первой');
        $this->createNews('Новость вторая', 'Описание второй');

        $response = $this->getJson('/api/news');

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
     * GET /api/news?per_page=2 поддерживает параметр per_page.
     * Ожидается: data содержит 2 элемента, meta.total = 3.
     */
    public function test_index_supports_per_page(): void
    {
        $this->createNews('Новость 1', 'Описание 1');
        $this->createNews('Новость 2', 'Описание 2');
        $this->createNews('Новость 3', 'Описание 3');

        $response = $this->getJson('/api/news?per_page=2');

        $response->assertStatus(200);
        $this->assertCount(2, $response->json('data'));
        $response->assertJsonPath('meta.total', 3);
    }

    // -------------------------------------------------------------------------
    // Тесты store
    // -------------------------------------------------------------------------

    /**
     * POST /api/news создаёт новость и возвращает 201.
     * Ожидается: data.title и data.description совпадают с переданными.
     */
    public function test_store_creates_news(): void
    {
        $response = $this->postJson('/api/news', [
            'title'       => 'Новая важная новость',
            'description' => 'Подробное описание события',
        ]);

        $response->assertStatus(201);
        $response->assertJsonStructure([
            'data' => ['id', 'title', 'description', 'created_at', 'updated_at'],
        ]);
        $response->assertJsonPath('data.title', 'Новая важная новость');
        $response->assertJsonPath('data.description', 'Подробное описание события');
    }

    /**
     * POST /api/news с пустым телом возвращает 422.
     * Ожидается: ошибки валидации для обязательных полей title и description.
     */
    public function test_store_validates_required_fields(): void
    {
        $response = $this->postJson('/api/news', []);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['title', 'description']);
    }

    // -------------------------------------------------------------------------
    // Тесты show
    // -------------------------------------------------------------------------

    /**
     * GET /api/news/{id} возвращает новость с пустым блоком комментариев.
     * Ожидается: 200, data.comments.data является пустым массивом.
     */
    public function test_show_returns_news_with_comments(): void
    {
        $id = $this->createNews();

        $response = $this->getJson("/api/news/{$id}");

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
     * GET /api/news/9999 для несуществующей новости возвращает 404.
     * Ожидается: message содержит "not found".
     */
    public function test_show_returns_404_for_nonexistent(): void
    {
        $response = $this->getJson('/api/news/9999');

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
     * PUT /api/news/{id} обновляет поля новости.
     * Ожидается: 200, data.title и data.description совпадают с новыми значениями.
     */
    public function test_update_modifies_news(): void
    {
        $id = $this->createNews('Старый заголовок', 'Старое описание');

        $response = $this->putJson("/api/news/{$id}", [
            'title'       => 'Обновлённый заголовок',
            'description' => 'Обновлённое описание',
        ]);

        $response->assertStatus(200);
        $response->assertJsonPath('data.title', 'Обновлённый заголовок');
        $response->assertJsonPath('data.description', 'Обновлённое описание');
    }

    /**
     * PUT /api/news/9999 для несуществующей новости возвращает 404.
     */
    public function test_update_returns_404_for_nonexistent(): void
    {
        $response = $this->putJson('/api/news/9999', [
            'title' => 'Любой заголовок',
        ]);

        $response->assertStatus(404);
    }

    // -------------------------------------------------------------------------
    // Тесты destroy
    // -------------------------------------------------------------------------

    /**
     * DELETE /api/news/{id} удаляет новость.
     * Ожидается: 204, повторный GET возвращает 404.
     */
    public function test_destroy_deletes_news(): void
    {
        $id = $this->createNews();

        $this->deleteJson("/api/news/{$id}")->assertStatus(204);
        $this->getJson("/api/news/{$id}")->assertStatus(404);
    }

    /**
     * DELETE /api/news/9999 для несуществующей новости возвращает 404.
     */
    public function test_destroy_returns_404_for_nonexistent(): void
    {
        $response = $this->deleteJson('/api/news/9999');

        $response->assertStatus(404);
    }
}
