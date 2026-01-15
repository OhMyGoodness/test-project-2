<?php

declare(strict_types=1);

namespace App\Services\VideoPost\DTO;

/**
 * DTO для создания нового видеопоста.
 * Передаётся из контроллера в сервис.
 */
final readonly class CreateVideoPostDto
{
    /**
     * @param string $title       Заголовок видеопоста.
     * @param string $description Описание видеопоста.
     */
    public function __construct(
        public string $title,
        public string $description,
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
            title: $data['title'],
            description: $data['description'],
        );
    }
}
