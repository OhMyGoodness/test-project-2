<?php

declare(strict_types=1);

namespace App\Exceptions;

/**
 * Исключение бизнес-логики.
 * Выбрасывается из сервисного слоя при нарушении бизнес-правил приложения.
 * HTTP-статус по умолчанию — 422 (Unprocessable Entity).
 */
class BusinessLogicException extends \RuntimeException
{
    /**
     * @param string $message Описание бизнес-ошибки.
     * @param int    $code    HTTP-код ответа (по умолчанию 422).
     */
    public function __construct(string $message, int $code = 422)
    {
        parent::__construct($message, $code);
    }
}
