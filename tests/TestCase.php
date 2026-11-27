<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    public function createApplication()
    {
        $app = parent::createApplication();

        // FR : un environnement local ou un cache ne doit pas détourner les tests vers une vraie base.
        // EN: Local environment variables or cached settings must not redirect tests to a real database.
        $app['config']->set([
            'database.default' => 'sqlite',
            'database.connections.sqlite.database' => ':memory:',
            'database.connections.sqlite.url' => null,
            'cache.default' => 'array',
            'session.driver' => 'array',
            'mail.default' => 'array',
            'queue.default' => 'sync',
        ]);

        return $app;
    }
}
