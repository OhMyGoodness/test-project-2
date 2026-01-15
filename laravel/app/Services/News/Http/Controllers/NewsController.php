<?php

declare(strict_types=1);

namespace App\Services\News\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponse;
use App\Services\News\DTO\CreateNewsDto;
use App\Services\News\DTO\UpdateNewsDto;
use App\Services\News\Http\Requests\StoreNewsRequest;
use App\Services\News\Http\Requests\UpdateNewsRequest;
use App\Services\News\Http\Resources\NewsResource;
use App\Services\News\Http\Resources\NewsWithCommentsResource;
use App\Services\News\NewsServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use OpenApi\Attributes as OA;

/**
 * Контроллер для работы с новостями.
 * Принимает HTTP-запросы, делегирует бизнес-логику сервису,
 * возвращает стандартизированные JSON-ответы через трейт ApiResponse.
 */
#[OA\Tag(name: 'News', description: 'Операции над новостями')]
class NewsController extends Controller
{
    use ApiResponse;

    /**
     * @param NewsServiceInterface $newsService Сервис для работы с новостями.
     */
    public function __construct(
        private readonly NewsServiceInterface $newsService,
    ) {}

    /**
     * Получить список новостей с пагинацией.
     *
     * @param Request $request HTTP-запрос с параметрами пагинации.
     * @return AnonymousResourceCollection
     */
    #[OA\Get(
        path: '/api/news',
        summary: 'Список новостей',
        description: 'Возвращает постраничный список новостей.',
        tags: ['News'],
        parameters: [
            new OA\Parameter(
                name: 'per_page',
                in: 'query',
                required: false,
                description: 'Количество записей на страницу (по умолчанию 15).',
                schema: new OA\Schema(type: 'integer', default: 15)
            ),
            new OA\Parameter(
                name: 'page',
                in: 'query',
                required: false,
                description: 'Номер страницы.',
                schema: new OA\Schema(type: 'integer', default: 1)
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Успешный ответ со списком новостей',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'data',
                            type: 'array',
                            items: new OA\Items(ref: '#/components/schemas/NewsResource')
                        ),
                    ],
                    type: 'object'
                )
            ),
        ]
    )]
    public function index(Request $request): AnonymousResourceCollection
    {
        $paginated = $this->newsService->getPaginated($request->integer('per_page', 15));

        return NewsResource::collection($paginated);
    }

    /**
     * Создать новую новость.
     *
     * @param StoreNewsRequest $request Валидированный запрос на создание.
     * @return JsonResponse
     */
    #[OA\Post(
        path: '/api/news',
        summary: 'Создание новости',
        description: 'Создаёт новую новость и возвращает её данные.',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/StoreNewsRequest')
        ),
        tags: ['News'],
        responses: [
            new OA\Response(
                response: 201,
                description: 'Новость успешно создана',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'data', ref: '#/components/schemas/NewsResource'),
                    ],
                    type: 'object'
                )
            ),
            new OA\Response(
                response: 422,
                description: 'Ошибка валидации входных данных'
            ),
        ]
    )]
    public function store(StoreNewsRequest $request): JsonResponse
    {
        $dto  = CreateNewsDto::fromArray($request->validated());
        $news = $this->newsService->create($dto);

        return $this->created(new NewsResource($news));
    }

    /**
     * Получить новость по ID вместе с комментариями.
     *
     * @param int     $id      Идентификатор новости.
     * @param Request $request HTTP-запрос с параметрами пагинации комментариев.
     * @return JsonResponse
     */
    #[OA\Get(
        path: '/api/news/{id}',
        summary: 'Новость с комментариями',
        description: 'Возвращает новость по идентификатору вместе с курсорной пагинацией комментариев.',
        tags: ['News'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                description: 'Идентификатор новости.',
                schema: new OA\Schema(type: 'integer', example: 1)
            ),
            new OA\Parameter(
                name: 'per_page',
                in: 'query',
                required: false,
                description: 'Количество комментариев на страницу.',
                schema: new OA\Schema(type: 'integer', default: 15)
            ),
            new OA\Parameter(
                name: 'cursor',
                in: 'query',
                required: false,
                description: 'Курсор для пагинации комментариев.',
                schema: new OA\Schema(type: 'string')
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Успешный ответ с данными новости и комментариями',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'data', ref: '#/components/schemas/NewsWithCommentsResource'),
                    ],
                    type: 'object'
                )
            ),
            new OA\Response(response: 404, description: 'Новость не найдена'),
        ]
    )]
    public function show(int $id, Request $request): JsonResponse
    {
        $news = $this->newsService->getByIdWithComments(
            $id,
            $request->integer('per_page', 15),
            $request->string('cursor')->value() ?: null,
        );

        return $this->success(new NewsWithCommentsResource($news));
    }

    /**
     * Обновить существующую новость.
     *
     * @param UpdateNewsRequest $request Валидированный запрос на обновление.
     * @param int               $id      Идентификатор новости.
     * @return JsonResponse
     */
    #[OA\Put(
        path: '/api/news/{id}',
        summary: 'Обновление новости',
        description: 'Обновляет поля существующей новости. Все поля опциональны.',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/UpdateNewsRequest')
        ),
        tags: ['News'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                description: 'Идентификатор новости.',
                schema: new OA\Schema(type: 'integer', example: 1)
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Новость успешно обновлена',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'data', ref: '#/components/schemas/NewsResource'),
                    ],
                    type: 'object'
                )
            ),
            new OA\Response(response: 404, description: 'Новость не найдена'),
            new OA\Response(response: 422, description: 'Ошибка валидации входных данных'),
        ]
    )]
    public function update(UpdateNewsRequest $request, int $id): JsonResponse
    {
        $dto  = UpdateNewsDto::fromArray($request->validated());
        $news = $this->newsService->update($id, $dto);

        return $this->success(new NewsResource($news));
    }

    /**
     * Удалить новость по ID.
     *
     * @param int $id Идентификатор новости.
     * @return Response
     */
    #[OA\Delete(
        path: '/api/news/{id}',
        summary: 'Удаление новости',
        description: 'Удаляет новость и каскадно удаляет все её комментарии.',
        tags: ['News'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                description: 'Идентификатор новости.',
                schema: new OA\Schema(type: 'integer', example: 1)
            ),
        ],
        responses: [
            new OA\Response(response: 204, description: 'Новость успешно удалена'),
            new OA\Response(response: 404, description: 'Новость не найдена'),
        ]
    )]
    public function destroy(int $id): Response
    {
        $this->newsService->delete($id);

        return $this->noContent();
    }
}
