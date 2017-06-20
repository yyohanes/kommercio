<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Database\SQLiteConnection;

class TestCase extends Illuminate\Foundation\Testing\TestCase
{
    /**
     * The base URL to use while testing the application.
     *
     * @var string
     */
    protected $baseUrl = 'http://localhost';

    protected function setUp() {
        parent::setUp();

        // Because we rely on testing database, we try to migrate if there is migrations.
        $this->artisan('migrate');

        $this->app[Illuminate\Contracts\Console\Kernel::class]->setArtisan(null);
    }

    /**
     * Creates the application.
     *
     * @return \Illuminate\Foundation\Application
     */
    public function createApplication()
    {
        $app = require __DIR__.'/../bootstrap/app.php';

        $app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

        return $app;
    }
}
