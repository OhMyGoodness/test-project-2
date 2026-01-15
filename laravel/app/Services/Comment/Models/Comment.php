<?php

declare(strict_types=1);

namespace App\Services\Comment\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Carbon;
use OpenApi\Attributes as OA;

/**
 * Модель комментария.
 * Поддерживает прикрепление к любой сущности через полиморфную связь,
 * а также вложенность на один уровень (ответы на комментарии).
 *
 * @property int                    $id               Идентификатор комментария.
 * @property int                    $user_id          Идентификатор автора.
 * @property string                 $commentable_type Тип комментируемой сущности (алиас из morphMap).
 * @property int                    $commentable_id   Идентификатор комментируемой сущности.
 * @property int|null               $parent_id        Идентификатор родительского комментария.
 * @property string                 $text             Текст комментария.
 * @property Carbon                 $created_at       Дата и время создания.
 * @property Carbon                 $updated_at       Дата и время последнего обновления.
 *
 * @property-read Model             $commentable      Полиморфная родительская сущность.
 * @property-read User              $user             Автор комментария.
 * @property-read Comment|null      $parent           Родительский комментарий.
 * @property-read Collection<int, Comment> $replies   Дочерние ответы на комментарий.
 */
#[OA\Schema(
    schema: 'CommentModel',
    title: 'Comment',
    description: 'Модель комментария с поддержкой вложенных ответов',
    required: ['user_id', 'commentable_type', 'commentable_id', 'text'],
    properties: [
        new OA\Property(property: 'id', type: 'integer', description: 'Идентификатор комментария', example: 1),
        new OA\Property(property: 'user_id', type: 'integer', description: 'Идентификатор автора', example: 1),
        new OA\Property(property: 'commentable_type', type: 'string', description: 'Тип сущности (алиас morphMap)', example: 'news'),
        new OA\Property(property: 'commentable_id', type: 'integer', description: 'Идентификатор сущности', example: 1),
        new OA\Property(property: 'parent_id', type: 'integer', description: 'Идентификатор родительского комментария', example: null, nullable: true),
        new OA\Property(property: 'text', type: 'string', description: 'Текст комментария', example: 'Отличная статья!'),
        new OA\Property(property: 'created_at', type: 'string', format: 'date-time', description: 'Дата создания', example: '2026-01-15T00:00:00.000000Z'),
        new OA\Property(property: 'updated_at', type: 'string', format: 'date-time', description: 'Дата обновления', example: '2026-01-15T00:00:00.000000Z'),
    ]
)]
class Comment extends Model
{
    /**
     * Поля, разрешённые для массового заполнения.
     *
     * @var list<string>
     */
    protected $fillable = [
        'text',
        'user_id',
        'commentable_id',
        'commentable_type',
        'parent_id',
    ];

    /**
     * Приведение типов атрибутов.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Полиморфная связь: родительская сущность (News или VideoPost).
     *
     * @return MorphTo
     */
    public function commentable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Связь: автор комментария.
     *
     * @return BelongsTo<User, Comment>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Связь: родительский комментарий.
     *
     * @return BelongsTo<Comment, Comment>
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Comment::class, 'parent_id');
    }

    /**
     * Связь: дочерние ответы на этот комментарий.
     *
     * @return HasMany<Comment>
     */
    public function replies(): HasMany
    {
        return $this->hasMany(Comment::class, 'parent_id');
    }
}
