<?php

namespace App\Services\PosIntegration;

use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class Settings
{
    private array $fallback;
    private bool $overridden = false;

    public function __construct()
    {
        $this->fallback = config('pos_integration');
    }

    public function reload(): void
    {
        try {
            if (!Schema::hasTable('integration_settings')) {
                if ($this->overridden) {
                    config(['pos_integration' => $this->fallback]);
                    $this->overridden = false;
                }
                return;
            }
            $row = DB::table('integration_settings')->where('id', 1)->first();
            if (!$row) {
                if ($this->overridden) {
                    config(['pos_integration' => $this->fallback]);
                    $this->overridden = false;
                }
                return;
            }
            $secret = $row->secret ? Crypt::decryptString($row->secret) : null;
            $enabled = (bool) $row->enabled && is_string($secret) && strlen($secret) >= 32
                && self::validPeerUrl($row->peer_url) && (int) $row->store_id > 0;
            config(['pos_integration.enabled' => $enabled,
                'pos_integration.peer_url' => $row->peer_url,
                'pos_integration.store_id' => (int) $row->store_id,
                'pos_integration.secret' => $secret]);
            $this->overridden = true;
        } catch (\Throwable $e) {
            config(['pos_integration.enabled' => false, 'pos_integration.secret' => null,
                'pos_integration.peer_url' => null, 'pos_integration.store_id' => 1]);
            $this->overridden = true;
            \Log::warning('Configuración de integración POS no disponible.', ['exception' => get_class($e)]);
        }
    }

    public static function validPeerUrl(?string $url): bool
    {
        if (!$url || strlen($url) > 2048 || !filter_var($url, FILTER_VALIDATE_URL)) {
            return false;
        }
        $parts = parse_url($url);
        return is_array($parts) && in_array(strtolower($parts['scheme'] ?? ''), ['http', 'https'], true)
            && !empty($parts['host']) && !isset($parts['user']) && !isset($parts['pass'])
            && !isset($parts['query']) && !isset($parts['fragment'])
            && (!isset($parts['path']) || $parts['path'] === '' || $parts['path'] === '/')
            && (!isset($parts['port']) || ($parts['port'] >= 1 && $parts['port'] <= 65535));
    }
}
