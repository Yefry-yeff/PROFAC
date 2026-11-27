<?php

namespace App\Services\PosIntegration;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Symfony\Component\Process\Process;

class LocalSupervisor
{
    public function ensureOnline(): void
    {
        if ($this->online()) {
            return;
        }
        if (PHP_OS_FAMILY !== 'Windows' || ! app()->environment('local')) {
            throw ValidationException::withMessages(['integration' => 'Supervisor apagado. Inicie el servicio instalado en el VPS y vuelva a intentarlo. No se inicio el worker.']);
        }
        Cache::lock('integration-local-supervisor-start', 30)->block(15, function () {
            if ($this->online()) {
                return;
            }
            try {
                $this->launch();
                $deadline = microtime(true) + 10;
                do {
                    if ($this->online()) {
                        return;
                    }
                    usleep(200000);
                } while (microtime(true) < $deadline);
            } catch (\Symfony\Component\Process\Exception\ExceptionInterface $e) {
                Log::error('No se pudo iniciar el supervisor local', ['exception' => get_class($e),
                    'output' => $e instanceof \Symfony\Component\Process\Exception\ProcessFailedException ? mb_substr($e->getProcess()->getErrorOutput(), -4000) : $e->getMessage()]);
                throw ValidationException::withMessages(['integration' => 'No se pudo ejecutar el lanzador Windows del supervisor local. Revise storage/logs/laravel.log. No se confirmo el inicio del worker.']);
            }
            throw ValidationException::withMessages(['integration' => 'El supervisor local no confirmo su heartbeat. Revise storage/logs/integration-supervisor-out.log e integration-supervisor-error.log y la ruta PHP CLI. No se confirmo el inicio del worker.']);
        });
    }

    protected function online(): bool
    {
        return Coordinator::online(DB::table('integration_runtime')->where('id', 1)->first());
    }

    protected function launch(): void
    {
        $php = (string) config('pos_integration.cli_php');
        if (! is_file($php) || strtolower(basename($php)) !== 'php.exe') {
            throw ValidationException::withMessages(['integration' => 'Configure una ruta PHP CLI valida (php.exe), no php-cgi.exe.']);
        }
        // HTTP's filtered environment can omit SystemRoot, breaking Windows CLI networking.
        $environment = getenv(null, true);
        if (! is_array($environment)) {
            throw ValidationException::withMessages(['integration' => 'No se pudo leer el entorno nativo Windows para iniciar PHP CLI.']);
        }
        $process = Process::fromShellCommandline(
            'start "" /B "${:INTEGRATION_PHP}" "${:INTEGRATION_ARTISAN}" pos-integration:manage > "${:INTEGRATION_OUT}" 2> "${:INTEGRATION_ERROR}" < NUL',
            base_path(), array_merge($environment, [
                'INTEGRATION_PHP' => $php,
                'INTEGRATION_ARTISAN' => base_path('artisan'),
                'INTEGRATION_OUT' => storage_path('logs/integration-supervisor-out.log'),
                'INTEGRATION_ERROR' => storage_path('logs/integration-supervisor-error.log'),
            ]));
        $process->setTimeout(15);
        $process->mustRun();
    }
}
