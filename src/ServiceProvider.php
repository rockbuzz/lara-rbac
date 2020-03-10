<?php

namespace Rockbuzz\LaraRbac;

use Illuminate\Support\Facades\Blade;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\ServiceProvider as SupportServiceProvider;

class ServiceProvider extends SupportServiceProvider
{
    public function boot(Filesystem $filesystem)
    {
        $projectPath = database_path('migrations') . DIRECTORY_SEPARATOR;
        $localPath = __DIR__ . DIRECTORY_SEPARATOR . 'database' . DIRECTORY_SEPARATOR . 'migrations' .
            DIRECTORY_SEPARATOR;

        if (! $this->hasMigrationInProject($projectPath, $filesystem)) {
            $this->loadMigrationsFrom($localPath . '2020_02_15_000000_create_rbac_tables.php');

            $this->publishes([
                $localPath . '2020_02_15_000000_create_rbac_tables.php' =>
                    $projectPath . now()->format('Y_m_d_his') . '_create_rbac_tables.php'
            ], 'migrations');
        }

        $this->publishes([
            __DIR__ . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . ' rbac.php' => config_path('rbac.php')
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

    private function hasMigrationInProject(string $path, Filesystem $filesystem)
    {
        return count($filesystem->glob($path . '*_create_rbac_tables.php')) > 0;
    }
}
