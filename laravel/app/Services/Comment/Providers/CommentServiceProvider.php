<?php

declare(strict_types=1);

namespace App\Services\Comment\Providers;

use App\Services\Comment\CommentService;
use App\Services\Comment\CommentServiceInterface;
use App\Services\Comment\Repositories\CommentRepositoryInterface;
use App\Services\Comment\Repositories\EloquentCommentRepository;
use Illuminate\Support\ServiceProvider;

/**
 * Провайдер модуля Comment.
 * Регистрирует биндинги интерфейсов репозитория и сервиса к их реализациям.
 */
class CommentServiceProvider extends ServiceProvider
{
    /**
     * Регистрирует зависимости модуля Comment в IoC-контейнере.
     *
     * @return void
     */
    public function register(): void
    {
        $this->app->bind(CommentRepositoryInterface::class, EloquentCommentRepository::class);
        $this->app->bind(CommentServiceInterface::class, CommentService::class);
    }
}
