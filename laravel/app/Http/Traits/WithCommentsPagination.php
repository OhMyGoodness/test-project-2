<?php

declare(strict_types=1);

namespace App\Http\Traits;

use App\Services\Comment\DTO\CommentOutputDto;
use App\Services\Comment\Http\Resources\CommentResource;
use App\Services\Comment\Models\Comment;
use Illuminate\Http\Request;
use Illuminate\Pagination\CursorPaginator;

/**
 * Трейт для формирования блока курсорной пагинации комментариев.
 * Подключается в ресурсах, возвращающих сущность с комментариями.
 */
trait WithCommentsPagination
{
    /**
     * Сформировать блок курсорной пагинации комментариев для JSON-ответа.
     * Конвертирует Eloquent-модели комментариев в DTO перед передачей в ресурс.
     *
     * @param CursorPaginator $paginator Курсорный пагинатор комментариев.
     * @param Request         $request   Текущий HTTP-запрос.
     * @return array<string, mixed>
     */
    protected function buildCommentsBlock(CursorPaginator $paginator, Request $request): array
    {
        $dtos = $paginator->getCollection()->map(
            fn (Comment $comment) => CommentOutputDto::fromModel($comment)
        );

        return [
            'data'        => CommentResource::collection($dtos),
            'next_cursor' => $paginator->nextCursor()?->encode(),
            'prev_cursor' => $paginator->previousCursor()?->encode(),
            'per_page'    => $paginator->perPage(),
            'path'        => $request->url(),
        ];
    }
}
