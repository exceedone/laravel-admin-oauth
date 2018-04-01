<?php

namespace Exceedone\LaravelAdminOauth;

use Illuminate\Support\ServiceProvider;
use Exceedone\LaravelAdminOauth\Console;

class AdminOauthServiceProvider extends ServiceProvider
{
    /**
     * @var array commands
     */
    protected $commands = [
        'Exceedone\LaravelAdminOauth\Console\InstallCommand',
    ];

    /**
     * The application's route middleware.
     *
     * @var array
     */
    protected $routeMiddleware = [
        'admin.auth'       => \Exceedone\LaravelAdminOauth\Middleware\OauthAuthenticate::class,
    ];

    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__.'/../config/adminoauth.php' => config_path('adminoauth.php'),
        ]);

        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        $this->loadViewsFrom(__DIR__.'/../resources/views', 'adminoauth');

        $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'adminoauth');
        $this->publishes([
            __DIR__.'/../resources/lang' => resource_path('lang'),
        ]);

        $this->commands($this->commands);
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(
            'AdminOauth',
            'Exceedone\LaravelAdminOauth\AdminOauth'
        );

        // register route middleware.
        foreach ($this->routeMiddleware as $key => $middleware) {
            app('router')->aliasMiddleware($key, $middleware);
        }

    }
}
