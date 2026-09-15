<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Http;
use RuntimeException;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        // Tests must not call paid/live AI services or other external HTTP APIs.
        Http::preventStrayRequests();
    }

    /**
     * Never allow destructive database test helpers to run against the
     * application's real MySQL database. This also protects the project when
     * a cached local configuration exists before the test suite starts.
     */
    public function createApplication()
    {
        $app = parent::createApplication();

        $connection = config('database.default');
        $database = config("database.connections.{$connection}.database");

        if (! $app->environment('testing') || $connection !== 'sqlite' || $database !== ':memory:') {
            throw new RuntimeException(
                'Test safety lock: tests may only use the in-memory SQLite database.'
            );
        }

        return $app;
    }
}
