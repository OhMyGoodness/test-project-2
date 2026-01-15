<?php

declare(strict_types=1);

namespace App\Services\VideoPost\DTO;

/**
 * DTO для обновления существующего видеопоста.
 * Все поля опциональны: обновляются только переданные значения.
 */
final readonly class UpdateVideoPostDto
{
    /**
     * @param string|null $title       Новый заголовок видеопоста или null, если не обновляется.
     * @param string|null $description Новое описание видеопоста или null, если не обновляется.
     */
    public function __construct(
        public ?string $title = null,
        public ?string $description = null,
    ) {
    }

    /**
     * Создать DTO из массива данных.
     *
     * @param array<string, mixed> $data Массив с данными запроса.
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
     *
     * @return array<string, string>
     */
    public function toArray(): array
    {
        return array_filter([
            'title'       => $this->title,
            'description' => $this->description,
        ], fn ($value) => $value !== null);
    }
}
