<?php

declare(strict_types=1);

namespace App\Services\News\DTO;

/**
 * DTO для обновления новости.
 * Все поля опциональны — передаётся только то, что нужно изменить.
 */
final readonly class UpdateNewsDto
{
    /**
     * @param string|null $title       Новый заголовок новости или null, если не меняется.
     * @param string|null $description Новый текст новости или null, если не меняется.
     */
    public function __construct(
        public ?string $title = null,
        public ?string $description = null,
    ) {}

    /**
     * Создать DTO из ассоциативного массива данных.
     *
     * @param array<string, mixed> $data Валидированные данные запроса.
     * @return self
     */
    public static function fromArray(array $data): self
    {
        return new self(
            title: $data['title'] ?? null,
            description: $data['description'] ?? null,
        );
    }

    /**
     * Преобразовать DTO в массив, исключая null-значения.
     * Используется при передаче данных в репозиторий.
     *
     * @return array<string, string>
     */
    public function toArray(): array
    {
        return array_filter([
            'title'       => $this->title,
            'description' => $this->description,
        ], fn (?string $value) => $value !== null);
    }
}
