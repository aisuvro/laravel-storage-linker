<?php

namespace Aisuvro\LaravelStorageLinker;

use Illuminate\Support\ServiceProvider;
use Aisuvro\LaravelStorageLinker\Commands\StorageLinkCommand;

class StorageLinkerServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                StorageLinkCommand::class,
            ]);
        }
    }
}
