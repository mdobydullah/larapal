<?php

namespace Obydul\LaraPal;

use Illuminate\Support\ServiceProvider;

class LaraPalServiceProvider extends ServiceProvider
{
    public function boot()
    {
        // Publish config files
        $this->publishes([
            __DIR__ . '/../config/config.php' => config_path('larapal.php'),
        ]);
    }

    public function register()
    {
        $this->mergeConfig();
    }

    /**
     * Merges user's and Larapal's configs.
     *
     * @return void
     */
    private function mergeConfig()
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/config.php',
            'larapal'
        );
    }
}
