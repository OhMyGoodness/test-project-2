<?php

declare(strict_types=1);

namespace App\Services\News\DTO;

/**
 * DTO для создания новости.
 * Передаётся из контроллера в сервис при запросе на создание.
 */
final readonly class CreateNewsDto
{
    /**
     * @param string $title       Заголовок новости.
     * @param string $description Текст (описание) новости.
     */
    public function __construct(
        public string $title,
        public string $description,
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
            title: $data['title'],
            description: $data['description'],
        );
    }
}
