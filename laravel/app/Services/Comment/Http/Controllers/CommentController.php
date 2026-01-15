<?php

declare(strict_types=1);

namespace App\Services\Comment\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponse;
use App\Services\Comment\CommentServiceInterface;
use App\Services\Comment\DTO\CreateCommentDto;
use App\Services\Comment\DTO\UpdateCommentDto;
use App\Services\Comment\Http\Requests\StoreCommentRequest;
use App\Services\Comment\Http\Requests\UpdateCommentRequest;
use App\Services\Comment\Http\Resources\CommentResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use OpenApi\Attributes as OA;

/**
 * Контроллер управления комментариями.
 * Обрабатывает создание, обновление и удаление комментариев.
 */
#[OA\Tag(name: 'Comments', description: 'Операции с комментариями')]
class CommentController extends Controller
{
    use ApiResponse;

    /**
     * @param CommentServiceInterface $commentService Сервис комментариев.
     */
    public function __construct(
        private readonly CommentServiceInterface $commentService,
    ) {}

    /**
     * Создать новый комментарий.
     *
     * @param StoreCommentRequest $request Валидированный запрос на создание.
     * @return JsonResponse
     */
    #[OA\Post(
        path: '/api/comments',
        summary: 'Создать новый комментарий',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                ref: '#/components/schemas/StoreCommentRequest'
            )
        ),
        tags: ['Comments'],
        responses: [
            new OA\Response(
                response: 201,
                description: 'Комментарий успешно создан',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'data', ref: '#/components/schemas/CommentResource'),
                    ],
                    type: 'object'
                )
            ),
            new OA\Response(response: 422, description: 'Ошибка валидации'),
        ]
    )]
    public function store(StoreCommentRequest $request): JsonResponse
    {
        $dto = CreateCommentDto::fromArray($request->validated());
        $comment = $this->commentService->create($dto);

        return $this->created(new CommentResource($comment));
    }

    /**
     * Обновить существующий комментарий.
     *
     * @param UpdateCommentRequest $request Валидированный запрос на обновление.
     * @param int                  $id      Идентификатор комментария.
     * @return JsonResponse
     */
    #[OA\Put(
        path: '/api/comments/{id}',
        summary: 'Обновить комментарий',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                ref: '#/components/schemas/UpdateCommentRequest'
            )
        ),
        tags: ['Comments'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                description: 'Идентификатор комментария',
                schema: new OA\Schema(type: 'integer')
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Комментарий успешно обновлён',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'data', ref: '#/components/schemas/CommentResource'),
                    ],
                    type: 'object'
                )
            ),
            new OA\Response(response: 404, description: 'Комментарий не найден'),
            new OA\Response(response: 422, description: 'Ошибка валидации'),
        ]
    )]
    public function update(UpdateCommentRequest $request, int $id): JsonResponse
    {
        $dto = UpdateCommentDto::fromArray($request->validated());
        $comment = $this->commentService->update($id, $dto);

        return $this->success(new CommentResource($comment));
    }

    /**
     * Удалить комментарий.
     *
     * @param int $id Идентификатор комментария.
     * @return Response
     */
    #[OA\Delete(
        path: '/api/comments/{id}',
        summary: 'Удалить комментарий',
        tags: ['Comments'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                description: 'Идентификатор комментария',
                schema: new OA\Schema(type: 'integer')
            ),
        ],
        responses: [
            new OA\Response(response: 204, description: 'Комментарий успешно удалён'),
            new OA\Response(response: 404, description: 'Комментарий не найден'),
        ]
    )]
    public function destroy(int $id): Response
    {
        $this->commentService->delete($id);

        return $this->noContent();
    }
}
