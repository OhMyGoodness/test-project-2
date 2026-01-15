<?php

declare(strict_types=1);

namespace App\Services\VideoPost\Models;

use App\Services\Comment\Models\Comment;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use OpenApi\Attributes as OA;

/**
 * Eloquent-модель видеопоста.
 *
 * @property int         $id          Идентификатор видеопоста.
 * @property string      $title       Заголовок видеопоста.
 * @property string      $description Описание видеопоста.
 * @property Carbon      $created_at  Дата создания.
 * @property Carbon      $updated_at  Дата последнего обновления.
 * @property Carbon|null $deleted_at  Дата мягкого удаления или null.
 *
 * @property-read Collection<int, Comment> $comments Коллекция комментариев к видеопосту.
 */
#[OA\Schema(
    schema: 'VideoPost',
    title: 'Модель видеопоста',
    description: 'Eloquent-модель видеопоста',
    required: ['title', 'description'],
    properties: [
        new OA\Property(property: 'id', type: 'integer', description: 'Идентификатор видеопоста', example: 1),
        new OA\Property(property: 'title', type: 'string', description: 'Заголовок видеопоста', example: 'Невероятное видео'),
        new OA\Property(property: 'description', type: 'string', description: 'Описание видеопоста', example: 'Посмотрите это удивительное видео...'),
        new OA\Property(property: 'created_at', type: 'string', format: 'date-time', description: 'Дата создания', example: '2026-01-15T00:00:00.000000Z'),
        new OA\Property(property: 'updated_at', type: 'string', format: 'date-time', description: 'Дата последнего обновления', example: '2026-01-15T00:00:00.000000Z'),
    ]
)]
class VideoPost extends Model
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
     * При удалении видеопоста каскадно удаляет все связанные комментарии.
     *
     * @return void
     */
    protected static function booted(): void
    {
        static::deleting(function (VideoPost $videoPost): void {
            $videoPost->comments()->delete();
        });
    }

    /**
     * Полиморфная связь: все комментарии к данному видеопосту.
     *
     * @return MorphMany<Comment>
     */
    public function comments(): MorphMany
    {
        return $this->morphMany(Comment::class, 'commentable');
    }
}
