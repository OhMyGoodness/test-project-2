<?php

declare(strict_types=1);

namespace App\Enums;

use App\Services\News\Models\News;
use App\Services\VideoPost\Models\VideoPost;
use Illuminate\Database\Eloquent\Model;

/**
 * Enum типов комментируемых сущностей.
 * Инкапсулирует маппинг между публичными API-ключами и FQCN Eloquent-моделей.
 */
enum CommentableType: string
{
    case News = 'news';
    case VideoPost = 'video_post';

    /**
     * Вернуть FQCN соответствующей Eloquent-модели.
     *
     * @return class-string<Model>
     */
    public function modelClass(): string
    {
        return match($this) {
            self::News      => News::class,
            self::VideoPost => VideoPost::class,
        };
    }
}
