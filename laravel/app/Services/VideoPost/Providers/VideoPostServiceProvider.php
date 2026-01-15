<?php

declare(strict_types=1);

namespace App\Services\VideoPost\Providers;

use App\Services\VideoPost\Repositories\EloquentVideoPostRepository;
use App\Services\VideoPost\Repositories\VideoPostRepositoryInterface;
use App\Services\VideoPost\VideoPostService;
use App\Services\VideoPost\VideoPostServiceInterface;
use Illuminate\Support\ServiceProvider;

/**
 * Провайдер модуля VideoPost.
 * Регистрирует биндинги интерфейсов репозитория и сервиса к их реализациям.
 */
class VideoPostServiceProvider extends ServiceProvider
{
    /**
     * Регистрирует зависимости модуля VideoPost в IoC-контейнере.
     *
     * @return void
     */
    public function register(): void
    {
        $this->app->bind(VideoPostRepositoryInterface::class, EloquentVideoPostRepository::class);
        $this->app->bind(VideoPostServiceInterface::class, VideoPostService::class);
    }
}
