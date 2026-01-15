<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Миграция: добавление soft delete в таблицы news и video_posts.
 */
return new class extends Migration
{
    /**
     * Применить миграцию.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::table('news', function (Blueprint $table) {
            $table->softDeletes();
        });

        Schema::table('video_posts', function (Blueprint $table) {
            $table->softDeletes();
        });
    }

    /**
     * Откатить миграцию.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table('news', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('video_posts', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
};
