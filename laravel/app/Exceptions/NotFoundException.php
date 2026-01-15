<?php

declare(strict_types=1);

namespace App\Exceptions;

/**
 * Исключение "ресурс не найден".
 * Выбрасывается репозиторием, когда запрашиваемая запись отсутствует в БД.
 */
class NotFoundException extends \RuntimeException
{
    /**
     * @param string $resource Название ресурса, например 'News', 'VideoPost', 'Comment'.
     * @param int    $id       Запрашиваемый идентификатор.
     */
    public function __construct(string $resource, int $id)
    {
        parent::__construct("{$resource} with ID {$id} not found.");
    }
}
