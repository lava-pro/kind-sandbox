<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Repositories\Post\PostRepositoryInterface;
use App\Repositories\Post\PostRepository;
use App\Repositories\Tag\TagRepositoryInterface;
use App\Repositories\Tag\TagRepository;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(PostRepositoryInterface::class, function ($app) {
            return new PostRepository($app->request->route('lang'));
        });

        $this->app->bind(TagRepositoryInterface::class, function ($app) {
            return new TagRepository($app->request->route('lang'));
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
