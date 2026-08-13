<?php

namespace Tests;

use Illuminate\Contracts\Console\Kernel;

trait CreatesApplication
{
    /**
     * Creates the application.
     *
     * @return \Illuminate\Foundation\Application
     */
    public function createApplication()
    {
        $app = require __DIR__.'/../bootstrap/app.php';

        $app->make(Kernel::class)->bootstrap();

        $connection = (string) config('database.default');
        $database = (string) config("database.connections.{$connection}.database");

        if (!str_ends_with($database, '_testing')) {
            throw new \RuntimeException(
                "Pruebas bloqueadas: la base configurada '{$database}' no termina en _testing."
            );
        }

        return $app;
    }
}
