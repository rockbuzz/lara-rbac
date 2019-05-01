<?php

namespace Rockbuzz\LaraRbac;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider as SupportServiceProvider;

class ServiceProvider extends SupportServiceProvider
{
    public function boot()
    {
        $this->loadMigrationsFrom(__DIR__ . '/database/migrations');

        $this->publishes([
            __DIR__ . '/database/migrations/' => database_path('migrations')
        ], 'migrations');

        $this->publishes([
            __DIR__ . '/config/rbac.php' => config_path('rbac.php')
        ], 'config');

        Blade::if('hasrole', function($expression){
            if (!strpos($expression, ',')) {
                return auth()->user()->hasRole($expression);
            }
            list($roles, $company) = $this->parseExpression($expression);
            return auth()->user()->hasRole($roles, $company);
        });
        Blade::if('haspermission', function($expression){
            if (!strpos($expression, ',')) {
                return auth()->user()->hasPermission($expression);
            }
            list($permissions, $company) = $this->parseExpression($expression);
            return auth()->user()->hasPermission($permissions, $company);
        });
    }

    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/config/rbac.php', 'rbac');
    }

    private function parseExpression(string $expression): array
    {
        return $this->takeOutSpaces(
            explode(',', $expression)
        );
    }

    private function takeOutSpaces(array $parameters): array
    {
        return array_map(function ($parameter) {
            return trim($parameter);
        }, $parameters);
    }
}
