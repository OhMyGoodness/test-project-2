<?php

declare(strict_types=1);

namespace App\Providers;

use App\Services\News\Models\News;
use App\Services\VideoPost\Models\VideoPost;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\ServiceProvider;

/**
 * Основной провайдер приложения.
 * Регистрирует глобальные настройки, не относящиеся к конкретным модулям.
 * Модульные биндинги регистрируются в отдельных ServiceProvider-ах.
 */
class AppServiceProvider extends ServiceProvider
{
    /**
     * Регистрирует зависимости приложения в IoC-контейнере.
     *
     * @return void
     */
    public function register(): void
    {
        //
    }

    /**
     * Выполняет начальную настройку сервисов приложения.
     * Регистрирует morphMap для полиморфных связей Eloquent.
     *
     * @return void
     */
    public function boot(): void
    {
        Relation::morphMap([
            'news'       => News::class,
            'video_post' => VideoPost::class,
        ]);
    }
}
