<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

abstract class TestCase extends BaseTestCase
{
    use RefreshDatabase;

    /**
     * Creates the application.
     */
    public function createApplication()
    {
        $app = require __DIR__.'/../bootstrap/app.php';

        $app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

        return $app;
    }

    /**
     * Setup per i test
     */
    protected function setUp(): void
    {
        parent::setUp();
        
        // Disable analytics in testing
        config(['analytics.google.enabled' => false]);
        
        // Disable notifications in testing
        config(['sudoku.notifications.new_challenges' => false]);
    }
}
