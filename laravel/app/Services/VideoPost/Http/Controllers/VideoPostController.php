<?php

declare(strict_types=1);

namespace App\Services\VideoPost\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponse;
use App\Services\VideoPost\DTO\CreateVideoPostDto;
use App\Services\VideoPost\DTO\UpdateVideoPostDto;
use App\Services\VideoPost\Http\Requests\StoreVideoPostRequest;
use App\Services\VideoPost\Http\Requests\UpdateVideoPostRequest;
use App\Services\VideoPost\Http\Resources\VideoPostResource;
use App\Services\VideoPost\Http\Resources\VideoPostWithCommentsResource;
use App\Services\VideoPost\VideoPostServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use OpenApi\Attributes as OA;

/**
 * Контроллер для управления видеопостами.
 * Принимает HTTP-запросы, делегирует бизнес-логику сервису и возвращает JSON-ответы.
 */
#[OA\Tag(name: 'Video Posts', description: 'Операции с видеопостами')]
class VideoPostController extends Controller
{
    use ApiResponse;

    /**
     * @param VideoPostServiceInterface $videoPostService Сервис бизнес-логики видеопостов.
     */
    public function __construct(
        private readonly VideoPostServiceInterface $videoPostService
    ) {
    }

    /**
     * Получить список видеопостов с пагинацией.
     *
     * @param Request $request HTTP-запрос с параметром per_page.
     * @return AnonymousResourceCollection
     */
    #[OA\Get(
        path: '/api/video-posts',
        summary: 'Получить список видеопостов',
        tags: ['Video Posts'],
        parameters: [
            new OA\Parameter(
                name: 'per_page',
                in: 'query',
                required: false,
                description: 'Количество записей на страницу',
                schema: new OA\Schema(type: 'integer', default: 15)
            ),
            new OA\Parameter(
                name: 'page',
                in: 'query',
                required: false,
                description: 'Номер страницы',
                schema: new OA\Schema(type: 'integer', default: 1)
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Успешная операция',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'data',
                            type: 'array',
                            items: new OA\Items(ref: '#/components/schemas/VideoPostResource')
                        ),
                        new OA\Property(
                            property: 'meta',
                            description: 'Метаданные пагинации',
                            properties: [
                                new OA\Property(property: 'current_page', type: 'integer', example: 1),
                                new OA\Property(property: 'last_page', type: 'integer', example: 5),
                                new OA\Property(property: 'per_page', type: 'integer', example: 15),
                                new OA\Property(property: 'total', type: 'integer', example: 75),
                            ],
                            type: 'object'
                        ),
                    ],
                    type: 'object'
                )
            ),
        ]
    )]
    public function index(Request $request): AnonymousResourceCollection
    {
        $paginated = $this->videoPostService->getPaginated($request->integer('per_page', 15));

        return VideoPostResource::collection($paginated);
    }

    /**
     * Создать новый видеопост.
     *
     * @param StoreVideoPostRequest $request Валидированный запрос на создание.
     * @return JsonResponse
     */
    #[OA\Post(
        path: '/api/video-posts',
        summary: 'Создать новый видеопост',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/StoreVideoPostRequest')
        ),
        tags: ['Video Posts'],
        responses: [
            new OA\Response(
                response: 201,
                description: 'Видеопост успешно создан',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'data', ref: '#/components/schemas/VideoPostResource'),
                    ]
                )
            ),
            new OA\Response(
                response: 422,
                description: 'Ошибка валидации'
            ),
        ]
    )]
    public function store(StoreVideoPostRequest $request): JsonResponse
    {
        $dto = CreateVideoPostDto::fromArray($request->validated());
        $videoPost = $this->videoPostService->create($dto);

        return $this->created(new VideoPostResource($videoPost));
    }

    /**
     * Получить видеопост по ID с пагинированными комментариями.
     *
     * @param int     $id      Идентификатор видеопоста.
     * @param Request $request HTTP-запрос с параметрами per_page и cursor.
     * @return JsonResponse
     */
    #[OA\Get(
        path: '/api/video-posts/{id}',
        summary: 'Получить видеопост по ID с комментариями',
        tags: ['Video Posts'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                description: 'Идентификатор видеопоста',
                schema: new OA\Schema(type: 'integer')
            ),
            new OA\Parameter(
                name: 'per_page',
                in: 'query',
                required: false,
                description: 'Количество комментариев на страницу',
                schema: new OA\Schema(type: 'integer', default: 15)
            ),
            new OA\Parameter(
                name: 'cursor',
                in: 'query',
                required: false,
                description: 'Курсор для пагинации комментариев',
                schema: new OA\Schema(type: 'string')
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Успешная операция',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'data', ref: '#/components/schemas/VideoPostWithCommentsResource'),
                    ]
                )
            ),
            new OA\Response(response: 404, description: 'Видеопост не найден'),
        ]
    )]
    public function show(int $id, Request $request): JsonResponse
    {
        $videoPost = $this->videoPostService->getByIdWithComments(
            $id,
            $request->integer('per_page', 15),
            $request->string('cursor')->value() ?: null
        );

        return $this->success(new VideoPostWithCommentsResource($videoPost));
    }

    /**
     * Обновить видеопост.
     *
     * @param UpdateVideoPostRequest $request Валидированный запрос на обновление.
     * @param int                    $id      Идентификатор видеопоста.
     * @return JsonResponse
     */
    #[OA\Put(
        path: '/api/video-posts/{id}',
        summary: 'Обновить видеопост',
        requestBody: new OA\RequestBody(
            content: new OA\JsonContent(ref: '#/components/schemas/UpdateVideoPostRequest')
        ),
        tags: ['Video Posts'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                description: 'Идентификатор видеопоста',
                schema: new OA\Schema(type: 'integer')
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Видеопост успешно обновлён',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'data', ref: '#/components/schemas/VideoPostResource'),
                    ]
                )
            ),
            new OA\Response(response: 404, description: 'Видеопост не найден'),
            new OA\Response(response: 422, description: 'Ошибка валидации'),
        ]
    )]
    public function update(UpdateVideoPostRequest $request, int $id): JsonResponse
    {
        $dto = UpdateVideoPostDto::fromArray($request->validated());
        $videoPost = $this->videoPostService->update($id, $dto);

        return $this->success(new VideoPostResource($videoPost));
    }

    /**
     * Удалить видеопост.
     *
     * @param int $id Идентификатор видеопоста.
     * @return Response
     */
    #[OA\Delete(
        path: '/api/video-posts/{id}',
        summary: 'Удалить видеопост',
        tags: ['Video Posts'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                description: 'Идентификатор видеопоста',
                schema: new OA\Schema(type: 'integer')
            ),
        ],
        responses: [
            new OA\Response(response: 204, description: 'Видеопост успешно удалён'),
            new OA\Response(response: 404, description: 'Видеопост не найден'),
        ]
    )]
    public function destroy(int $id): Response
    {
        $this->videoPostService->delete($id);

        return $this->noContent();
    }
}
