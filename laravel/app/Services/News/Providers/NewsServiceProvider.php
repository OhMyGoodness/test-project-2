<?php

declare(strict_types=1);

namespace App\Services\News\Providers;

use App\Services\News\NewsService;
use App\Services\News\NewsServiceInterface;
use App\Services\News\Repositories\EloquentNewsRepository;
use App\Services\News\Repositories\NewsRepositoryInterface;
use Illuminate\Support\ServiceProvider;

/**
 * Провайдер модуля News.
 * Регистрирует биндинги интерфейсов репозитория и сервиса к их реализациям.
 */
class NewsServiceProvider extends ServiceProvider
{
    /**
     * Регистрирует зависимости модуля News в IoC-контейнере.
     *
     * @return void
     */
    public function register(): void
    {
        $this->app->bind(NewsRepositoryInterface::class, EloquentNewsRepository::class);
        $this->app->bind(NewsServiceInterface::class, NewsService::class);
    }
}
