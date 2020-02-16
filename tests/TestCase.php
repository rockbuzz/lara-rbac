<?php

namespace Tests;

use Orchestra\Testbench\TestCase as OrchestraTestCase;
use Rockbuzz\LaraRbac\ServiceProvider;
use Tests\Models\User;

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
            '--path' => realpath(__DIR__ . '/migrations'),
        ]);
    }


    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('database.default', 'testing');
    }


    protected function getPackageProviders($app)
    {
        return [ServiceProvider::class];
    }

    /**
     * @return mixed
     */
    protected function createUser()
    {
        $user = User::create([
            'name' => 'name test',
            'email' => 'user.test@email.com',
            'password' => bcrypt(123456),
        ]);
        return $user;
    }
}
