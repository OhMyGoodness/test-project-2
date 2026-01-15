<?php

declare(strict_types=1);

namespace App\Services\News\Models;

use App\Services\Comment\Models\Comment;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use OpenApi\Attributes as OA;

/**
 * Модель новости.
 *
 * @property int         $id          Идентификатор новости.
 * @property string      $title       Заголовок новости.
 * @property string      $description Текст (описание) новости.
 * @property Carbon      $created_at  Дата создания.
 * @property Carbon      $updated_at  Дата последнего обновления.
 * @property Carbon|null $deleted_at  Дата мягкого удаления или null.
 *
 * @property-read Collection<int, Comment> $comments Коллекция всех комментариев новости.
 */
#[OA\Schema(
    schema: 'NewsModel',
    title: 'Модель новости',
    description: 'Представляет сущность новости в системе',
    required: ['title', 'description'],
    properties: [
        new OA\Property(property: 'id', type: 'integer', description: 'Идентификатор новости', example: 1),
        new OA\Property(property: 'title', type: 'string', description: 'Заголовок новости', example: 'Важная новость'),
        new OA\Property(property: 'description', type: 'string', description: 'Текст новости', example: 'Подробное описание события...'),
        new OA\Property(property: 'created_at', type: 'string', format: 'date-time', description: 'Дата создания', example: '2026-01-15T00:00:00.000000Z'),
        new OA\Property(property: 'updated_at', type: 'string', format: 'date-time', description: 'Дата обновления', example: '2026-01-15T00:00:00.000000Z'),
    ]
)]
class News extends Model
{
    use SoftDeletes;

    /**
     * Поля, доступные для массового заполнения.
     *
     * @var list<string>
     */
    protected $fillable = [
        'title',
        'description',
    ];

    /**
     * Приведение атрибутов к типам.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Хук жизненного цикла модели.
     * При удалении новости каскадно удаляет все её комментарии.
     *
     * @return void
     */
    protected static function booted(): void
    {
        static::deleting(function (News $news): void {
            $news->comments()->delete();
        });
    }

    /**
     * Связь: все комментарии к данной новости (полиморфная).
     *
     * @return MorphMany<Comment>
     */
    public function comments(): MorphMany
    {
        return $this->morphMany(Comment::class, 'commentable');
    }
}
