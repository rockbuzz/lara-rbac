<?php

namespace Tests;

use Orchestra\Testbench\TestCase as OrchestraTestCase;
use Rockbuzz\LaraRbac\ServiceProvider;

class TestCase extends OrchestraTestCase
{
    public function setUp(): void
    {
        parent::setUp();

        $this->loadLaravelMigrations(['--database' => 'testing']);

        $this->loadMigrationsFrom([
            '--database' => 'testing',
            '--path' => realpath(__DIR__ . '/../src/database/migrations'),
        ]);

        $this->loadMigrationsFrom([
            '--database' => 'testing',
            '--path' => realpath(__DIR__ . '/database/migrations'),
        ]);

        $this->withFactories(__DIR__ . '/database/factories');
        $this->withFactories(__DIR__ . '/../src/database/factories');
    }


    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('database.default', 'testing');
        /*
        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver'   => 'mysql',
            'host'   => 'dbtest',
            'database' => 'testing',
            'username'   => 'testing',
            'password' => 'secret'
        ]);*/
    }


    protected function getPackageProviders($app)
    {
        return [ServiceProvider::class];
    }

    protected function create(string $class, array $attributes = [], int $times = null)
    {
        return factory($class, $times)->create($attributes);
    }
}
