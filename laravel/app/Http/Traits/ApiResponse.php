<?php

declare(strict_types=1);

namespace App\Http\Traits;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

/**
 * Трейт для формирования единообразных HTTP JSON-ответов.
 * Подключается в контроллерах для устранения дублирования response()->json(...).
 */
trait ApiResponse
{
    /**
     * Успешный ответ с данными.
     *
     * @param mixed $data   Данные для включения в ключ 'data'.
     * @param int   $status HTTP-статус (по умолчанию 200).
     * @return JsonResponse
     */
    public function success(mixed $data, int $status = 200): JsonResponse
    {
        return response()->json(['data' => $data], $status);
    }

    /**
     * Ответ 201 Created с данными.
     *
     * @param mixed $data Данные созданного ресурса.
     * @return JsonResponse
     */
    public function created(mixed $data): JsonResponse
    {
        return response()->json(['data' => $data], 201);
    }

    /**
     * Ответ 204 No Content (тело отсутствует).
     *
     * @return Response
     */
    public function noContent(): Response
    {
        return response()->noContent();
    }

    /**
     * Ответ с ошибкой.
     *
     * @param string $message Текст сообщения об ошибке.
     * @param int    $status  HTTP-статус (по умолчанию 400).
     * @return JsonResponse
     */
    public function error(string $message, int $status = 400): JsonResponse
    {
        return response()->json(['message' => $message], $status);
    }
}
