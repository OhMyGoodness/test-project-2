<?php

declare(strict_types=1);

namespace App\Services\Comment\DTO;

/**
 * DTO для обновления существующего комментария.
 */
final readonly class UpdateCommentDto
{
    /**
     * @param string|null $text Новый текст комментария. Null — поле не обновляется.
     */
    public function __construct(
        public ?string $text = null,
    ) {}

    /**
     * Создать DTO из массива данных.
     *
     * @param array<string, mixed> $data Ассоциативный массив с обновляемыми полями.
     * @return self
     */
    public static function fromArray(array $data): self
    {
        return new self(
            text: $data['text'] ?? null,
        );
    }

    /**
     * Преобразовать DTO в массив, исключая null-значения.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return array_filter([
            'text' => $this->text,
        ], fn (mixed $value): bool => $value !== null);
    }
}
