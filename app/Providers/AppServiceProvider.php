<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

use App\Repositories\Interfaces\ChatMessageRepositoryInterface;
use App\Repositories\ChatMessageRepository;

use App\Repositories\Interfaces\GroupChatRepositoryInterface;
use App\Repositories\GroupChatRepository;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(ChatMessageRepositoryInterface::class, ChatMessageRepository::class);
        $this->app->bind(GroupChatRepositoryInterface::class, GroupChatRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
