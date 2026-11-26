<?php

namespace App\Console\Commands;

use App\Services\PosIntegration\Coordinator;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class PosIntegrationManage extends Command
{
    protected $signature = 'pos-integration:manage {--once}';
    protected $description = 'Coordina rondas de integración POS mediante lease persistente';
    private bool $stopping = false;

    public function handle(Coordinator $coordinator): int
    {
        if (PHP_OS_FAMILY !== 'Windows' && (!function_exists('pcntl_signal') || !function_exists('pcntl_async_signals'))) {
            $this->error('El coordinador requiere la extensión pcntl de PHP CLI en Linux.');
            return self::FAILURE;
        }
        if (function_exists('pcntl_signal') && function_exists('pcntl_async_signals')) {
            pcntl_async_signals(true);
            pcntl_signal(SIGTERM, function () { $this->stopping = true; });
            pcntl_signal(SIGINT, function () { $this->stopping = true; });
        }
        $token = (string) Str::uuid();
        if (!$coordinator->acquire($token)) {
            $this->error('Ya hay un coordinador activo.');
            return self::FAILURE;
        }
        try {
            $nextAutomatic = 0.0;
            do {
                if (!$coordinator->heartbeat($token)) {
                    $this->error('Lease del coordinador perdido.');
                    return self::FAILURE;
                }
                $op = $coordinator->claim($token, microtime(true) >= $nextAutomatic);
                if ($op) {
                    if ($op->requested_by === null) {
                        $nextAutomatic = microtime(true) + 5;
                    }
                    $inline = $this->option('once') && config('database.default') === 'sqlite'
                        && config('database.connections.sqlite.database') === ':memory:';
                    $coordinator->run($token, $op, $inline, fn () => $this->stopping);
                }
                if (!$this->option('once') && !$this->stopping) {
                    sleep(2);
                }
            } while (!$this->option('once') && !$this->stopping);
            return self::SUCCESS;
        } finally {
            $coordinator->release($token);
        }
    }
}
