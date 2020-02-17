<?php

namespace Rockbuzz\LaraRbac;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider as SupportServiceProvider;

class ServiceProvider extends SupportServiceProvider
{
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/database/migrations/2020_02_15_000000_create_rbac_table.php' =>
                database_path('migrations') . '/' .
                now()->format('Y_m_d_his') . '_create_'. config('rbac.tables.prefix') .'rbac_table.php'
        ], 'migrations');

        $this->publishes([
            __DIR__ . '/config/rbac.php' => config_path('rbac.php')
        ], 'config');

        Blade::if('hasrole', function ($expression) {
            list($roles, $resource) = $this->parseExpression($expression);
            return auth()->user()->hasRole($roles, $resource);
        });
        Blade::if('haspermission', function ($expression) {
            list($permissions, $resource) = $this->parseExpression($expression);
            return auth()->user()->hasPermission($permissions, $resource);
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
