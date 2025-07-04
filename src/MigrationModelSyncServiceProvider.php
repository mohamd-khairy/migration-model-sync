<?php

namespace Khairy\MigrationModelSync;

use Illuminate\Support\ServiceProvider;
use Khairy\MigrationModelSync\Commands\SyncModelCommand;

class MigrationModelSyncServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/Config/modelsync.php', 'modelsync');
    }

    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                SyncModelCommand::class,
            ]);

            $this->publishes([
                __DIR__ . '/Config/modelsync.php' => config_path('modelsync.php'),
            ], 'modelsync-config');
        }
    }
}