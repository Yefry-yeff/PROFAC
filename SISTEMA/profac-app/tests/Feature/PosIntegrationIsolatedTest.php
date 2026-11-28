<?php

namespace Tests\Feature;

use App\Services\PosIntegration\Receiving;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\TestCase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class PaperlandNavigationStub extends \Livewire\Component
{
    public function render()
    {
        return '<div></div>';
    }
}

#[\PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses]
class PosIntegrationIsolatedTest extends TestCase
{
    public function createApplication()
    {
        foreach (['DB_CONNECTION' => 'sqlite', 'DB_DATABASE' => ':memory:', 'DATABASE_URL' => ''] as $key => $value) {
            putenv("$key=$value");
            $_ENV[$key] = $_SERVER[$key] = $value;
        }
        $app = require __DIR__.'/../../bootstrap/app.php';
        $app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();
        if (config('database.default') !== 'sqlite' || config('database.connections.sqlite.database') !== ':memory:'
            || config('database.connections.sqlite.url')) {
            throw new \RuntimeException('Pruebas POS bloqueadas: conexión no aislada.');
        }
        return $app;
    }

    protected function setUp(): void
    {
        parent::setUp();
        config(['pos_integration.enabled' => true, 'pos_integration.secret' => 'test-secret', 'pos_integration.peer_url' => 'http://localhost:8001']);
        (require database_path('migrations/2026_11_24_200000_create_pos_integration_tables.php'))->up();
        Schema::create('producto', function (Blueprint $t) { $t->id(); $t->string('nombre'); $t->decimal('costo_promedio', 12, 4)->nullable(); });
        Schema::create('unidad_medida', function (Blueprint $t) { $t->id(); $t->string('unidad'); $t->string('nombre'); $t->string('simbolo'); });
        Schema::create('unidad_medida_venta', function (Blueprint $t) { $t->id(); $t->integer('producto_id'); $t->integer('unidad_medida_id'); $t->decimal('unidad_venta', 12, 2); $t->integer('estado_id'); $t->integer('unidad_venta_defecto'); });
        Schema::create('bodega', function (Blueprint $t) { $t->id(); $t->string('nombre'); });
        Schema::create('segmento', function (Blueprint $t) { $t->id(); $t->integer('bodega_id'); $t->string('descripcion'); });
        Schema::create('seccion', function (Blueprint $t) { $t->id(); $t->integer('segmento_id'); $t->string('descripcion'); });
        Schema::create('translado', function (Blueprint $t) { $t->id(); $t->string('codigo')->nullable(); $t->text('comentario')->nullable(); $t->timestamps(); });
        Schema::create('compra_has_producto', function (Blueprint $t) { $t->integer('compra_id'); $t->integer('producto_id'); $t->decimal('precio_unidad', 12, 4); });
        Schema::create('log_translado', function (Blueprint $t) {
            $t->id(); $t->integer('translado_id'); $t->integer('origen'); $t->integer('destino')->nullable();
            $t->integer('cantidad'); $t->integer('unidad_medida_venta_id'); $t->integer('users_id')->nullable();
            $t->string('descripcion'); $t->text('comentario')->nullable(); $t->timestamps();
        });
        Schema::create('recibido_bodega', function (Blueprint $t) {
            $t->id(); $t->integer('compra_id')->nullable(); $t->integer('producto_id'); $t->integer('seccion_id');
            $t->integer('cantidad_compra_lote'); $t->integer('cantidad_inicial_seccion'); $t->integer('cantidad_disponible');
            $t->timestamp('fecha_recibido'); $t->date('fecha_expiracion')->nullable(); $t->integer('estado_recibido');
            $t->integer('recibido_por'); $t->integer('unidad_compra_id'); $t->integer('unidades_compra'); $t->timestamps();
        });
        DB::table('producto')->insert(['id' => 10, 'nombre' => 'Artículo']);
        DB::table('unidad_medida')->insert(['id' => 1, 'unidad' => 'U', 'nombre' => 'Unidad', 'simbolo' => 'u']);
        DB::table('unidad_medida_venta')->insert([
            ['id' => 12, 'producto_id' => 10, 'unidad_medida_id' => 1, 'unidad_venta' => 12, 'estado_id' => 1, 'unidad_venta_defecto' => 0],
            ['id' => 13, 'producto_id' => 10, 'unidad_medida_id' => 1, 'unidad_venta' => 13, 'estado_id' => 1, 'unidad_venta_defecto' => 0],
            ['id' => 1, 'producto_id' => 10, 'unidad_medida_id' => 1, 'unidad_venta' => 1, 'estado_id' => 1, 'unidad_venta_defecto' => 1],
        ]);
        DB::table('bodega')->insert(['id' => 1, 'nombre' => 'Principal']);
        DB::table('segmento')->insert(['id' => 2, 'bodega_id' => 1, 'descripcion' => 'A']);
        DB::table('seccion')->insert(['id' => 3, 'segmento_id' => 2, 'descripcion' => 'B']);
    }

    private function event(string $type, array $data): array
    {
        return ['schema_version' => 1, 'event_id' => (string) Str::uuid(), 'source' => 'pos', 'type' => $type, 'occurred_at' => now()->toIso8601String(), 'data' => $data];
    }

    private function sendEvent(array $event, ?string $signature = null, ?int $timestamp = null)
    {
        return $this->sendRawEvent(json_encode($event), $signature, $timestamp);
    }

    private function sendRawEvent(string $raw, ?string $signature = null, ?int $timestamp = null)
    {
        $timestamp = (string) ($timestamp ?? time());
        return $this->call('POST', '/api/pos-integration/events', [], [], [], [
            'CONTENT_TYPE' => 'application/json', 'HTTP_ACCEPT' => 'application/json',
            'HTTP_X_INTEGRATION_TIMESTAMP' => $timestamp,
            'HTTP_X_INTEGRATION_SIGNATURE' => $signature ?? hash_hmac('sha256', $timestamp.'.'.$raw, 'test-secret'),
        ], $raw);
    }

    private function rawEvents(string $json): array
    {
        $expected = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
        $this->assertIsArray($expected);
        $raw = []; $depth = 0; $inString = false; $escaped = false; $start = null;
        for ($i = 0, $length = strlen($json); $i < $length; $i++) {
            $char = $json[$i];
            if ($inString) {
                if ($escaped) { $escaped = false; }
                elseif ($char === '\\') { $escaped = true; }
                elseif ($char === '"') { $inString = false; }
                continue;
            }
            if ($char === '"') { $inString = true; }
            elseif ($char === '{') {
                if ($depth++ === 0) { $start = $i; }
            } elseif ($char === '}') {
                if (--$depth === 0) { $raw[] = substr($json, $start, $i - $start + 1); }
            }
        }
        $this->assertSame($expected, array_map(fn ($value) => json_decode($value, true, 512, JSON_THROW_ON_ERROR), $raw));
        return $raw;
    }

    private function transfer(): array
    {
        return ['transfer_uuid' => (string) Str::uuid(), 'reference' => 'POS-1', 'source_store_id' => 1,
            'destination_store_id' => null, 'comment' => null, 'actor_id' => 1, 'lines' => [[
                'line_uuid' => (string) Str::uuid(), 'product_profac_id' => 10, 'product_pos_id' => 99,
                'name' => 'Artículo', 'quantity_base' => 25, 'source_factor' => 12, 'unit_profac_id' => 1,
                'presentation_profac_id' => 12, 'source_lot_id' => 77, 'expires_at' => '2030-01-02', 'cost_base' => '3.50',
            ]]];
    }

    private function installSettings(): void
    {
        config(['app.key' => 'base64:'.base64_encode(str_repeat('k', 32)), 'cache.default' => 'array']);
        app()->forgetInstance('encrypter');
        \Illuminate\Support\Facades\Crypt::clearResolvedInstance('encrypter');
        Schema::create('menu', function (Blueprint $t) {
            $t->id(); $t->string('nombre_menu'); $t->string('icon'); $t->integer('orden'); $t->integer('estado_id'); $t->timestamps();
        });
        Schema::create('sub_menu', function (Blueprint $t) {
            $t->id(); $t->string('url'); $t->string('nombre'); $t->integer('menu_id'); $t->integer('orden');
            $t->integer('estado_id'); $t->integer('padre_id')->nullable(); $t->string('icono')->nullable(); $t->timestamps();
        });
        Schema::create('rol_submenu', function (Blueprint $t) {
            $t->id(); $t->integer('rol_id'); $t->integer('sub_menu_id'); $t->timestamps(); $t->unique(['rol_id', 'sub_menu_id']);
        });
        DB::table('menu')->insert(['id' => 3, 'nombre_menu' => 'Otro', 'icon' => 'fa', 'orden' => 1, 'estado_id' => 1]);
        DB::table('sub_menu')->insert(['id' => 4, 'url' => 'otro', 'nombre' => 'Otro', 'menu_id' => 3, 'orden' => 1, 'estado_id' => 1]);
        DB::table('rol_submenu')->insert(['rol_id' => 2, 'sub_menu_id' => 4]);
        (require database_path('migrations/2026_11_24_210000_create_paperland_integration_settings.php'))->up();
    }

    private function settingsUser(bool &$allowed): void
    {
        $user = \Mockery::mock(\App\Models\User::class)->makePartial();
        $user->id = 7;
        $user->shouldReceive('tienePermiso')->andReturnUsing(function ($url) use (&$allowed) {
            return $allowed && $url === 'configuracion/api-paperland';
        });
        $this->actingAs($user);
    }

    private function installProfiles(): void
    {
        $this->installSettings();
        (require database_path('migrations/2026_11_24_220000_create_paperland_connection_runtime.php'))->up();
    }

    private function peerHealth(string $uuid): array
    {
        return ['schema_version' => 1, 'source' => 'pos', 'enabled' => false, 'worker_last_seen_at' => null,
            'instance_id' => $uuid, 'coordinator_online' => false, 'worker_state' => 'offline', 'prepared' => false];
    }

    public function test_auth_and_catalog(): void
    {
        $event = $this->event('transfer.dispatched', $this->transfer());
        $this->sendEvent($event, str_repeat('0', 64))->assertStatus(401);
        $this->sendEvent($event, null, time() - 301)->assertStatus(401);
        config(['pos_integration.enabled' => false]);
        $this->sendEvent($event)->assertStatus(503);
        config(['pos_integration.enabled' => true]);
        $invalid = $event; $invalid['source'] = 'profac';
        $this->sendEvent($invalid)->assertStatus(422);
        config(['pos_integration.secret' => '']);
        $this->sendEvent($event)->assertStatus(503);
        config(['pos_integration.secret' => 'test-secret']);
        $ts = (string) time();
        $this->call('GET', '/api/pos-integration/catalog/units?per_page=1', [], [], [], [
            'HTTP_ACCEPT' => 'application/json', 'HTTP_X_INTEGRATION_TIMESTAMP' => $ts,
            'HTTP_X_INTEGRATION_SIGNATURE' => hash_hmac('sha256', $ts.'.', 'test-secret'),
        ], '')->assertOk()->assertJsonPath('data.0.simbolo', 'u')->assertJsonPath('next_page', null);
    }

    public function test_product_image_metadata_requires_signature_and_returns_only_safe_existing_images(): void
    {
        Schema::create('img_producto', function (Blueprint $table) {
            $table->id(); $table->integer('producto_id'); $table->string('url_img');
        });
        $directory = sys_get_temp_dir().DIRECTORY_SEPARATOR.'profac-image-test-'.Str::uuid();
        mkdir($directory);
        mkdir($directory.DIRECTORY_SEPARATOR.'catalogo');
        $file = $directory.DIRECTORY_SEPARATOR.'catalogo'.DIRECTORY_SEPARATOR.'producto.png';
        file_put_contents($file, base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+jRZkAAAAASUVORK5CYII='));
        $previousPublicPath = public_path();
        $this->app->usePublicPath($directory);
        try {
            DB::table('img_producto')->insert([
                ['producto_id' => 10, 'url_img' => '../privado.png'],
                ['producto_id' => 10, 'url_img' => 'no-existe.png'],
                ['producto_id' => 10, 'url_img' => 'producto.png'],
            ]);
            $this->getJson('/api/pos-integration/products/10/image')->assertUnauthorized();
            $timestamp = (string) time();
            $headers = ['HTTP_ACCEPT' => 'application/json', 'HTTP_X_INTEGRATION_TIMESTAMP' => $timestamp,
                'HTTP_X_INTEGRATION_SIGNATURE' => hash_hmac('sha256', $timestamp.'.', 'test-secret')];
            $this->call('GET', '/api/pos-integration/products/10/image', [], [], [], $headers)
                ->assertOk()->assertJsonPath('filename', 'producto.png');
            $this->call('GET', '/api/pos-integration/products/11/image', [], [], [], $headers)->assertNotFound();
        } finally {
            $this->app->usePublicPath($previousPublicPath);
            unlink($file);
            rmdir($directory.DIRECTORY_SEPARATOR.'catalogo');
            rmdir($directory);
        }
    }

    public function test_catalog_bulk_page_limit_preserves_pagination_and_default_size(): void
    {
        DB::table('unidad_medida')->insert(array_map(fn ($id) => [
            'id' => $id, 'unidad' => 'U', 'nombre' => 'Unidad '.$id, 'simbolo' => 'u',
        ], range(2, 501)));
        $ts = (string) time();
        $headers = ['HTTP_ACCEPT' => 'application/json', 'HTTP_X_INTEGRATION_TIMESTAMP' => $ts,
            'HTTP_X_INTEGRATION_SIGNATURE' => hash_hmac('sha256', $ts.'.', 'test-secret')];
        $this->call('GET', '/api/pos-integration/catalog/units?per_page=500', [], [], [], $headers, '')
            ->assertOk()->assertJsonCount(500, 'data')->assertJsonPath('next_page', 2);
        $this->call('GET', '/api/pos-integration/catalog/units?per_page=500&page=2', [], [], [], $headers, '')
            ->assertOk()->assertJsonCount(1, 'data')->assertJsonPath('data.0.id', 501)->assertJsonPath('next_page', null);
        $this->call('GET', '/api/pos-integration/catalog/units', [], [], [], $headers, '')
            ->assertOk()->assertJsonCount(100, 'data')->assertJsonPath('next_page', 2);
        $this->call('GET', '/api/pos-integration/catalog/units?per_page=501', [], [], [], $headers, '')->assertStatus(422);
    }

    public function test_idempotence_conflicts_and_no_inventory_until_approval(): void
    {
        $event = $this->event('transfer.dispatched', $this->transfer());
        $this->sendEvent($event)->assertOk()->assertJsonPath('status', 'processed');
        $this->sendEvent($event)->assertOk()->assertJsonPath('status', 'duplicate');
        $modified = $event; $modified['data']['reference'] = 'alterado';
        $this->sendEvent($modified)->assertStatus(409);
        $modified['event_id'] = (string) Str::uuid();
        $this->sendEvent($modified)->assertStatus(409);
        $this->assertSame(1, DB::table('pos_integration_inbox')->count());
        $this->assertSame(0, DB::table('recibido_bodega')->count());
    }

    public function test_approval_is_atomic_and_distributes_twelve_and_thirteen(): void
    {
        $data = $this->transfer(); $this->sendEvent($this->event('transfer.dispatched', $data))->assertOk();
        $base = ['line_uuid' => $data['lines'][0]['line_uuid'], 'quantity' => 1, 'bodega_id' => 1, 'segmento_id' => 2, 'seccion_id' => 3];
        $receiving = app(Receiving::class);
        try { $receiving->approve($data['transfer_uuid'], 7, [$base + ['presentation_id' => 12]]); $this->fail('Incomplete accepted'); }
        catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) { $this->assertSame(422, $e->getStatusCode()); }
        $this->assertSame(0, DB::table('recibido_bodega')->count());
        $receiving->approve($data['transfer_uuid'], 7, [$base + ['presentation_id' => 12], $base + ['presentation_id' => 13]]);
        $this->assertSame(25, DB::table('recibido_bodega')->sum('cantidad_disponible'));
        $this->assertSame(2, DB::table('recibido_bodega')->whereNull('compra_id')->count());
        $this->assertSame('received', DB::table('pos_integration_transfers')->value('status'));
        $this->assertSame(1, DB::table('pos_integration_outbox')->count());
    }

    public function test_approval_accepts_a_pos_product_with_no_profac_source_presentation(): void
    {
        $data = $this->transfer();
        $data['lines'][0]['presentation_profac_id'] = null;
        $this->sendEvent($this->event('transfer.dispatched', $data))->assertOk();
        $base = ['line_uuid' => $data['lines'][0]['line_uuid'], 'quantity' => 1,
            'bodega_id' => 1, 'segmento_id' => 2, 'seccion_id' => 3];
        app(Receiving::class)->approve($data['transfer_uuid'], 7, [
            $base + ['presentation_id' => 12], $base + ['presentation_id' => 13],
        ]);
        $this->assertSame(25, DB::table('recibido_bodega')->sum('cantidad_disponible'));
        $this->assertSame('received', DB::table('pos_integration_transfers')->value('status'));
    }

    public function test_failure_after_stock_insert_rolls_back_everything(): void
    {
        $data = $this->transfer(); $this->sendEvent($this->event('transfer.dispatched', $data))->assertOk();
        $base = ['line_uuid' => $data['lines'][0]['line_uuid'], 'quantity' => 1,
            'bodega_id' => 1, 'segmento_id' => 2, 'seccion_id' => 3];
        Schema::drop('pos_integration_outbox');
        try {
            app(Receiving::class)->approve($data['transfer_uuid'], 7, [$base + ['presentation_id' => 12], $base + ['presentation_id' => 13]]);
            $this->fail('Missing outbox should fail');
        } catch (\Illuminate\Database\QueryException $e) {
            $this->assertSame(0, DB::table('recibido_bodega')->count());
            $this->assertSame('pending', DB::table('pos_integration_transfers')->value('status'));
        }
    }

    public function test_rejection_does_not_create_stock_and_mirror_ignores_older_versions(): void
    {
        $data = $this->transfer(); $this->sendEvent($this->event('transfer.dispatched', $data))->assertOk();
        app(Receiving::class)->reject($data['transfer_uuid'], 7, 'Falta producto');
        $this->assertSame(0, DB::table('recibido_bodega')->count());
        $inventory = ['store_id' => 1, 'product_pos_id' => 9, 'product_profac_id' => 10, 'name' => 'Artículo',
            'version' => 2, 'physical_base' => 5, 'known_physical_base' => 5, 'unresolved_lots' => 0, 'available_base' => null, 'reserved_base' => null,
            'availability_status' => 'reservation_unit_pending', 'pending_base' => 0, 'reason' => 'conteo',
            'document_type' => null, 'document_id' => null, 'presentations' => []];
        $this->sendEvent($this->event('inventory.changed', $inventory))->assertOk();
        $inventory['version'] = 1; $inventory['physical_base'] = 99;
        $this->sendEvent($this->event('inventory.changed', $inventory))->assertOk();
        $mirror = json_decode(DB::table('pos_integration_mirror')->value('snapshot'), true);
        $this->assertSame(5, $mirror['physical_base']); $this->assertNull($mirror['available_base']);
        $this->assertSame(2, DB::table('pos_integration_movements')->where('type', 'inventory.changed')->count());
    }

    public function test_unresolved_presentation_and_returned_event_never_create_stock(): void
    {
        $inventory = ['store_id' => 1, 'product_pos_id' => 9, 'product_profac_id' => 10, 'name' => 'Artículo',
            'version' => 1, 'physical_base' => null, 'known_physical_base' => 5, 'unresolved_lots' => 2,
            'available_base' => null, 'reserved_base' => null, 'availability_status' => 'presentation_unit_pending',
            'pending_base' => 0, 'reason' => 'legacy', 'presentations' => [],
            'movements' => [['quantity' => 2, 'factor' => null, 'delta_base' => null, 'unit_status' => 'unresolved']]];
        $this->sendEvent($this->event('inventory.changed', $inventory))->assertOk();
        $mirror = json_decode(DB::table('pos_integration_mirror')->value('snapshot'), true);
        $this->assertNull($mirror['physical_base']);
        $this->assertSame(5, $mirror['known_physical_base']);
        $this->assertNull($mirror['movements'][0]['delta_base']);
        $data = $this->transfer();
        $this->sendEvent($this->event('transfer.dispatched', $data))->assertOk();
        app(Receiving::class)->reject($data['transfer_uuid'], 7, 'Falta producto');
        $event = $this->event('transfer.returned', ['transfer_uuid' => $data['transfer_uuid'], 'actor_id' => 4]);
        $this->sendEvent($event)->assertOk()->assertJsonPath('status', 'processed');
        $this->sendEvent($event)->assertOk()->assertJsonPath('status', 'duplicate');
        $this->assertSame('returned', DB::table('pos_integration_transfers')->value('status'));
        $this->assertSame(0, DB::table('recibido_bodega')->count());
    }

    public function test_outbox_uses_transfer_log_quantity_and_rolls_back_with_parent_transaction(): void
    {
        DB::table('producto')->where('id', 10)->update(['costo_promedio' => 3.25]);
        DB::table('translado')->insert(['id' => 4, 'codigo' => '2026-4', 'comentario' => 'traslado']);
        $lot = ['compra_id' => null, 'producto_id' => 10, 'seccion_id' => 3, 'cantidad_compra_lote' => 12,
            'cantidad_inicial_seccion' => 12, 'cantidad_disponible' => 0, 'fecha_recibido' => now(),
            'fecha_expiracion' => null, 'estado_recibido' => 4, 'recibido_por' => 7, 'unidad_compra_id' => 1,
            'unidades_compra' => 12, 'created_at' => now(), 'updated_at' => now()];
        DB::table('recibido_bodega')->insert($lot + ['id' => 5]);
        DB::table('log_translado')->insert(['id' => 1, 'translado_id' => 4, 'origen' => 5, 'destino' => null, 'cantidad' => 7, 'unidad_medida_venta_id' => 12, 'descripcion' => 'Translado de bodega']);
        $service = app(\App\Services\PosIntegration\Outbox::class);
        try {
            DB::transaction(function () use ($service) { $service->captureTransfer(4, 7, [1]); throw new \RuntimeException('rollback'); });
        } catch (\RuntimeException $e) { $this->assertSame('rollback', $e->getMessage()); }
        $this->assertSame(0, DB::table('pos_integration_outbox')->count());
        DB::transaction(fn () => $service->captureTransfer(4, 7, [1]));
        $event = json_decode(DB::table('pos_integration_outbox')->value('payload'), true);
        $this->assertSame(7, $event['data']['lines'][0]['quantity_base']);
        $this->assertSame(1, $event['data']['destination_store_id']);
        $this->assertSame(1, $event['data']['lines'][0]['unit_profac_id']);
        $this->assertEquals(3.25, $event['data']['lines'][0]['cost_base']);
        Http::fake(['*' => Http::response(['status' => 'processed'], 200)]);
        $this->artisan('pos-integration:work', ['--once' => true])->assertSuccessful();
        $this->assertSame('sent', DB::table('pos_integration_outbox')->value('status'));
        Http::assertSent(function ($request) {
            $timestamp = $request->header('X-Integration-Timestamp')[0];
            return hash_equals(hash_hmac('sha256', $timestamp.'.'.$request->body(), 'test-secret'), $request->header('X-Integration-Signature')[0]);
        });
        $rejection = $this->event('transfer.rejected', ['transfer_uuid' => $event['data']['transfer_uuid'], 'actor_id' => 2, 'reason' => 'No corresponde']);
        $this->sendEvent($rejection)->assertOk();
        $this->assertSame(0, DB::table('recibido_bodega')->where('id', 5)->value('cantidad_disponible'));
        DB::table('log_translado')->where('id', 1)->update(['cantidad' => 8]);
        try {
            app(Receiving::class)->confirmReturn($event['data']['transfer_uuid'], 7);
            $this->fail('Modified log accepted');
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) { $this->assertSame(409, $e->getStatusCode()); }
        DB::table('log_translado')->where('id', 1)->update(['cantidad' => 7]);
        app(Receiving::class)->confirmReturn($event['data']['transfer_uuid'], 7);
        $this->assertSame(7, DB::table('recibido_bodega')->where('id', 5)->value('cantidad_disponible'));
        $this->assertSame(1, DB::table('recibido_bodega')->count());
        $this->assertSame('returned', DB::table('pos_integration_transfers')->value('status'));
        try {
            app(Receiving::class)->confirmReturn($event['data']['transfer_uuid'], 7);
            $this->fail('Returned twice');
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) { $this->assertSame(409, $e->getStatusCode()); }
    }

    public function test_worker_retries_temporary_errors_and_exposes_permanent_errors(): void
    {
        app(\App\Services\PosIntegration\Outbox::class)->enqueue('transfer.rejected', [
            'transfer_uuid' => (string) Str::uuid(), 'actor_id' => 7, 'reason' => 'No corresponde',
        ]);
        Http::fakeSequence()->push([], 503)->push([], 409);
        $this->artisan('pos-integration:work', ['--once' => true])->assertSuccessful();
        $row = DB::table('pos_integration_outbox')->first();
        $this->assertSame('pending', $row->status);
        $this->assertNotNull($row->next_attempt_at);
        DB::table('pos_integration_outbox')->update(['next_attempt_at' => now()->subSecond()]);
        $this->artisan('pos-integration:work', ['--once' => true])->assertSuccessful();
        $row = DB::table('pos_integration_outbox')->first();
        $this->assertSame('failed', $row->status);
        $this->assertSame('HTTP 409', $row->error);
    }

    public static function receivedStockModes(): array
    {
        return ['legacy' => [false, false], 'manual set conversion' => [true, false], 'partial manual reception' => [true, true]];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('receivedStockModes')]
    public function test_dispatch_to_pos_is_in_transit_and_received_event_does_not_change_stock(bool $manual, bool $partial): void
    {
        DB::table('producto')->where('id', 10)->update(['costo_promedio' => 3.25]);
        DB::table('recibido_bodega')->insert([
            'id' => 5, 'compra_id' => null, 'producto_id' => 10, 'seccion_id' => 3,
            'cantidad_compra_lote' => 24, 'cantidad_inicial_seccion' => 24, 'cantidad_disponible' => 24,
            'fecha_recibido' => now(), 'fecha_expiracion' => null, 'estado_recibido' => 4,
            'recibido_por' => 7, 'unidad_compra_id' => 1, 'unidades_compra' => 12, 'created_at' => now(), 'updated_at' => now(),
        ]);
        $user = new \App\Models\User();
        $user->id = 7;
        $this->actingAs($user);
        DB::table('unidad_medida_venta')->where('id', 12)->update(['unidad_venta' => 12.5]);
        $invalid = new \Illuminate\Http\Request([
            'arregloIdInputs' => '1', 'idRecibido1' => '5', 'unidadMedidaId1' => 12,
            'cantidad1' => 1, 'idSeccion1' => 433,
        ]);
        $failed = app(\App\Livewire\Inventario\Translados::class)->ejectarTranslado($invalid);
        $this->assertSame('error', $failed->getData(true)['icon']);
        $this->assertSame(24, DB::table('recibido_bodega')->where('id', 5)->value('cantidad_disponible'));
        $this->assertSame(0, DB::table('log_translado')->count());
        DB::table('unidad_medida_venta')->where('id', 12)->update(['unidad_venta' => 12]);
        $request = new \Illuminate\Http\Request([
            'arregloIdInputs' => '1,2', 'idRecibido1' => '5', 'unidadMedidaId1' => 12,
            'cantidad1' => 1, 'idSeccion1' => 433, 'idRecibido2' => '5',
            'unidadMedidaId2' => 12, 'cantidad2' => 1, 'idSeccion2' => 3,
        ]);
        $response = app(\App\Livewire\Inventario\Translados::class)->ejectarTranslado($request);
        $this->assertSame('success', $response->getData(true)['icon']);
        $this->assertSame(0, DB::table('recibido_bodega')->where('id', 5)->value('cantidad_disponible'));
        $this->assertSame(12, DB::table('recibido_bodega')->sum('cantidad_disponible'));
        $this->assertSame(0, DB::table('recibido_bodega')->where('seccion_id', 433)->count());
        $this->assertSame(1, DB::table('log_translado')->whereNull('destino')->count());
        $event = json_decode(DB::table('pos_integration_outbox')->value('payload'), true);
        $line = $event['data']['lines'][0];
        $this->assertSame(12, $line['quantity_base']);
        $received = $this->event('transfer.received', ['transfer_uuid' => $event['data']['transfer_uuid'], 'actor_id' => 8,
            'allocations' => [['line_uuid' => $line['line_uuid'], 'lot_id' => 99, 'presentation_id' => 3,
                'factor' => $manual ? 5 : 12, 'quantity_base' => 12, 'seccion_id' => 4]]]);
        if ($manual) {
            $received['data']['allocations'][0]['quantity_stock'] = 5;
            $received['data']['allocations'][0]['quantity_base_pos'] = 24;
            $this->sendEvent($received)->assertStatus(422);
            $this->assertSame('dispatched', DB::table('pos_integration_transfers')->value('status'));
            unset($received['data']['allocations'][0]['quantity_base_pos']);
            $this->sendEvent($received)->assertStatus(422);
            $received['data']['allocations'][0]['quantity_base_pos'] = 25;
            $received['data']['allocations'][0]['quantity_base'] = 11;
            $this->sendEvent($received)->assertStatus(422);
            $received['data']['allocations'][0]['quantity_base'] = 12;
        }
        if ($partial) {
            $received['data']['allocations'][0]['quantity_base'] = 4;
            $received['data']['complete'] = false;
            $received['data']['receipt_version'] = 1;
            $this->sendEvent($received)->assertOk();
            $this->sendEvent($received)->assertOk()->assertJsonPath('status', 'duplicate');
            $this->assertSame('partial', DB::table('pos_integration_transfers')->value('status'));
            $this->sendEvent($this->event('transfer.rejected', [
                'transfer_uuid' => $event['data']['transfer_uuid'], 'reason' => 'Reject after partial receipt',
            ]))->assertStatus(409);
            $earlier = $received['data'];
            $received = $this->event('transfer.received', $earlier);
            $received['data']['allocations'][] = [
                'line_uuid' => $line['line_uuid'], 'lot_id' => 100, 'presentation_id' => 3,
                'factor' => 5, 'quantity_base' => 8, 'quantity_stock' => 6, 'quantity_base_pos' => 30, 'seccion_id' => 4,
            ];
            $received['data']['complete'] = true;
            $received['data']['receipt_version'] = 2;
            $conflicting = $received;
            $conflicting['data']['allocations'][0]['quantity_base'] = 5;
            $conflicting['data']['allocations'][1]['quantity_base'] = 7;
            $this->sendEvent($conflicting)->assertStatus(409);
        }
        $this->sendEvent($received)->assertOk();
        $this->sendEvent($received)->assertOk()->assertJsonPath('status', 'duplicate');
        $this->assertSame('received', DB::table('pos_integration_transfers')->value('status'));
        if ($partial) {
            $this->sendEvent($this->event('transfer.received', $earlier))->assertOk();
            $this->assertSame('received', DB::table('pos_integration_transfers')->value('status'));
            $this->assertSame(2, DB::table('pos_integration_movements')->where('type', 'transfer.received')->count());
        }
        $this->assertSame(12, DB::table('recibido_bodega')->sum('cantidad_disponible'));
        $this->assertSame(0, DB::table('recibido_bodega')->where('seccion_id', 433)->count());
    }

    public function test_fractional_presentation_is_never_rounded_on_approval(): void
    {
        $data = $this->transfer();
        $this->sendEvent($this->event('transfer.dispatched', $data))->assertOk();
        DB::table('unidad_medida_venta')->where('id', 12)->update(['unidad_venta' => 12.5]);
        $allocation = ['line_uuid' => $data['lines'][0]['line_uuid'], 'quantity' => 1,
            'bodega_id' => 1, 'segmento_id' => 2, 'seccion_id' => 3, 'presentation_id' => 12];
        try {
            app(Receiving::class)->approve($data['transfer_uuid'], 7, [$allocation]);
            $this->fail('Fractional factor accepted');
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) { $this->assertSame(422, $e->getStatusCode()); }
        $this->assertSame(0, DB::table('recibido_bodega')->count());
    }

    public function test_disabled_integration_keeps_legacy_transfer_behavior(): void
    {
        config(['pos_integration.enabled' => false]);
        DB::table('recibido_bodega')->insert([
            'id' => 5, 'compra_id' => null, 'producto_id' => 10, 'seccion_id' => 3,
            'cantidad_compra_lote' => 12, 'cantidad_inicial_seccion' => 12, 'cantidad_disponible' => 12,
            'fecha_recibido' => now(), 'fecha_expiracion' => null, 'estado_recibido' => 4,
            'recibido_por' => 7, 'unidad_compra_id' => 1, 'unidades_compra' => 12, 'created_at' => now(), 'updated_at' => now(),
        ]);
        $user = new \App\Models\User(); $user->id = 7; $this->actingAs($user);
        $request = new \Illuminate\Http\Request([
            'arregloIdInputs' => '1', 'idRecibido1' => '5', 'unidadMedidaId1' => 12,
            'cantidad1' => 1, 'idSeccion1' => 433,
        ]);
        $response = app(\App\Livewire\Inventario\Translados::class)->ejectarTranslado($request);
        $this->assertSame('success', $response->getData(true)['icon']);
        $this->assertSame(12, DB::table('recibido_bodega')->where('seccion_id', 433)->value('cantidad_disponible'));
        $this->assertSame(0, DB::table('pos_integration_outbox')->count());
    }

    public function test_approval_requires_transfer_permission_on_server(): void
    {
        $data = $this->transfer();
        $this->sendEvent($this->event('transfer.dispatched', $data))->assertOk();
        $allowed = false;
        $user = \Mockery::mock(\App\Models\User::class)->makePartial();
        $user->id = 7;
        $user->shouldReceive('tienePermiso')->andReturnUsing(function ($url) use (&$allowed) {
            return $allowed && $url === 'inventario/translado';
        });
        $this->actingAs($user);
        $base = ['line_uuid' => $data['lines'][0]['line_uuid'], 'quantity' => 1, 'bodega_id' => 1, 'segmento_id' => 2, 'seccion_id' => 3];
        $payload = ['allocations' => [$base + ['presentation_id' => 12], $base + ['presentation_id' => 13]]];
        $url = '/translados/pos/'.$data['transfer_uuid'].'/aprobar';
        $this->postJson($url, $payload)->assertForbidden();
        $this->assertSame(0, DB::table('recibido_bodega')->count());
        $allowed = true;
        $this->postJson($url, $payload)->assertOk()->assertJsonPath('status', 'received');
        $this->assertSame(25, DB::table('recibido_bodega')->sum('cantidad_disponible'));
    }

    public function test_history_templates_compile(): void
    {
        foreach (['pos-integration.history', 'pos-integration.receipt', 'livewire.inventario.historial-translados'] as $view) {
            $source = file_get_contents(view()->getFinder()->find($view));
            $this->assertNotEmpty(\Illuminate\Support\Facades\Blade::compileString($source));
        }
    }

    public function test_history_panel_is_shown_only_when_enabled_and_permitted(): void
    {
        $allowed = true;
        $user = \Mockery::mock(\App\Models\User::class)->makePartial();
        $user->id = 7;
        $user->shouldReceive('tienePermiso')->andReturnUsing(function ($url) use (&$allowed) {
            return $allowed && $url === 'translados/historial';
        });
        $this->actingAs($user);
        $view = fn () => view('livewire.inventario.historial-translados', ['fechaInicio' => '2026-01-01'])->render();
        $html = $view();
        $this->assertStringContainsString('Historial de Traslados', $html);
        $this->assertStringContainsString('Historial de Traslados de Paperland', $html);
        $this->assertStringContainsString('Traslados entrantes Paperland', $html);
        $this->assertStringContainsString('Traslados enviados a Paperland', $html);
        $this->assertStringNotContainsString('id="pos-mirror"', $html);
        foreach (['Movimientos Paperland e integración', 'Estado de envíos', 'Recepciones pendientes de procesamiento',
            'pos-movements', 'pos-outbox', 'pos-inbox'] as $removed) {
            $this->assertStringNotContainsString($removed, $html);
        }
        $this->getJson('/translados/pos')->assertOk()->assertExactJson(['transfers' => [], 'outgoing' => []]);
        config(['pos_integration.enabled' => false]);
        $this->assertStringNotContainsString('Historial de Traslados de Paperland', $view());
        config(['pos_integration.enabled' => true]);
        $allowed = false;
        $this->assertStringNotContainsString('Historial de Traslados de Paperland', $view());
    }

    public function test_outgoing_print_reuses_standard_transfer_receipt_and_requires_original_document(): void
    {
        $allowed = true;
        $user = \Mockery::mock(\App\Models\User::class)->makePartial();
        $user->id = 7;
        $user->shouldReceive('tienePermiso')->andReturnUsing(function ($url) use (&$allowed) {
            return $allowed && $url === 'translados/historial';
        });
        $this->actingAs($user);
        DB::table('translado')->insert(['id' => 7, 'codigo' => '2026-7']);
        $data = $this->transfer();
        DB::table('pos_integration_transfers')->insert([
            'transfer_uuid' => $data['transfer_uuid'], 'direction' => 'out', 'status' => 'dispatched',
            'reference' => '2026-7', 'local_transfer_id' => 7, 'document_hash' => hash('sha256', json_encode($data)),
            'snapshot' => json_encode($data), 'created_at' => now(), 'updated_at' => now(),
        ]);
        $url = '/translados/pos/'.$data['transfer_uuid'].'/imprimir';
        $this->get($url)->assertRedirect('/translado/imprimir/7');
        $allowed = false;
        $this->get($url)->assertForbidden();
        $allowed = true;
        foreach ([999, null] as $id) {
            DB::table('pos_integration_transfers')->update(['local_transfer_id' => $id]);
            $this->getJson($url)->assertStatus(409)
                ->assertJsonPath('message', 'Falta el documento original del traslado enviado a Paperland.');
        }
        $this->assertSame(0, DB::table('recibido_bodega')->count());
        $this->assertSame(0, DB::table('pos_integration_outbox')->count());
    }

    public function test_incoming_print_preserves_integration_receipt_without_creating_stock(): void
    {
        $user = \Mockery::mock(\App\Models\User::class)->makePartial();
        $user->id = 7;
        $user->shouldReceive('tienePermiso')->with('translados/historial')->andReturn(true);
        $this->actingAs($user);
        $data = $this->transfer();
        $this->sendEvent($this->event('transfer.dispatched', $data))->assertOk();
        $initialEvents = DB::table('pos_integration_outbox')->count();
        $viewData = [];
        app('view')->composer('pos-integration.receipt', function (\Illuminate\View\View $view) use (&$viewData) {
            $viewData = $view->getData();
        });
        $url = '/translados/pos/'.$data['transfer_uuid'].'/imprimir';
        $response = $this->get($url)->assertOk()->assertHeader('Content-Type', 'application/pdf');
        $this->assertStringStartsWith('%PDF-', $response->getContent());
        $this->assertStringStartsWith('inline;', $response->headers->get('Content-Disposition'));
        $this->assertSame(1, preg_match('/\/MediaBox\s*\[\s*0(?:\.0+)?\s+0(?:\.0+)?\s+([\d.]+)\s+([\d.]+)\s*\]/', $response->getContent(), $box));
        $this->assertEqualsWithDelta(612, (float) $box[1], .01);
        $this->assertEqualsWithDelta(792, (float) $box[2], .01);
        $html = view('pos-integration.receipt', $viewData)->render();
        foreach (['Paperland → Distribuciones Valencia', 'Pendiente de recepción', $data['reference'], $data['transfer_uuid'],
            'Cód. Paperland', 'Cód. Distribuciones Valencia', 'Artículo', 'Total unidades base: 25', 'Entrega · Paperland', 'Recepción · Distribuciones Valencia',
            'alt="Distribuciones Valencia"'] as $text) {
            $this->assertStringContainsString($text, $html);
        }
        $this->assertStringNotContainsString('POS hacia PROFAC', $html);
        $this->assertStringNotContainsString('Distribución recibida en Distribuciones Valencia', $html);
        $this->assertDoesNotMatchRegularExpression('/\bprofac\b/i', strip_tags($html));
        $this->assertDatabaseHas('pos_integration_transfers', ['transfer_uuid' => $data['transfer_uuid'], 'status' => 'pending']);
        $this->assertSame(0, DB::table('recibido_bodega')->count());
        $this->assertSame($initialEvents, DB::table('pos_integration_outbox')->count());
    }

    public function test_received_print_keeps_distribution_details_and_rejects_unpermitted_access(): void
    {
        $allowed = true;
        $user = \Mockery::mock(\App\Models\User::class)->makePartial();
        $user->id = 7;
        $user->shouldReceive('tienePermiso')->andReturnUsing(function ($url) use (&$allowed) {
            return $allowed && $url === 'translados/historial';
        });
        $this->actingAs($user);
        $data = $this->transfer();
        $this->sendEvent($this->event('transfer.dispatched', $data))->assertOk();
        $id = DB::table('pos_integration_transfers')->value('id');
        DB::table('pos_integration_transfers')->where('id', $id)->update(['status' => 'received']);
        DB::table('pos_integration_movements')->insert([
            'transfer_id' => $id, 'type' => 'transfer.received', 'created_at' => now(), 'updated_at' => now(),
            'snapshot' => json_encode(['allocations' => [['line_uuid' => $data['lines'][0]['line_uuid'],
                'lot_id' => 100, 'presentation_id' => 1, 'factor' => 1, 'quantity_base' => 25, 'seccion_id' => 3]]]),
        ]);
        $viewData = [];
        app('view')->composer('pos-integration.receipt', function (\Illuminate\View\View $view) use (&$viewData) {
            $viewData = $view->getData();
        });
        $initialEvents = DB::table('pos_integration_outbox')->count();
        $url = '/translados/pos/'.$data['transfer_uuid'].'/imprimir';
        $this->get($url)->assertOk()->assertHeader('Content-Type', 'application/pdf');
        $html = view('pos-integration.receipt', $viewData)->render();
        $this->assertStringContainsString('Distribución recibida en Distribuciones Valencia', $html);
        $this->assertDoesNotMatchRegularExpression('/\bprofac\b/i', strip_tags($html));
        $this->assertStringContainsString('Recibido', $html);
        $this->assertStringContainsString('<td>Artículo</td>', $html);
        $this->assertStringContainsString('<td>100</td>', $html);
        $this->assertSame(0, DB::table('recibido_bodega')->count());
        $this->assertSame($initialEvents, DB::table('pos_integration_outbox')->count());
        $allowed = false;
        $this->get($url)->assertForbidden();
    }

    public function test_product_mirror_scopes_by_profac_id_preserves_segments_and_never_changes_local_stock(): void
    {
        DB::table('recibido_bodega')->insert([
            'id' => 1, 'producto_id' => 10, 'seccion_id' => 3, 'cantidad_compra_lote' => 7,
            'cantidad_inicial_seccion' => 7, 'cantidad_disponible' => 7, 'fecha_recibido' => now(),
            'estado_recibido' => 4, 'recibido_por' => 1, 'unidad_compra_id' => 1, 'unidades_compra' => 1,
        ]);
        $inventory = ['store_id' => 1, 'product_pos_id' => 11, 'product_profac_id' => 10, 'name' => 'Artículo <azul>',
            'version' => 1, 'physical_base' => 27, 'known_physical_base' => 27, 'unresolved_lots' => 0,
            'available_base' => 27, 'reserved_base' => 0, 'availability_status' => 'known',
            'pending_base' => 0, 'reason' => 'conteo', 'presentations' => [
                ['id' => 99, 'unit_id' => 999, 'name' => 'Caja Paperland', 'factor' => 12, 'quantity' => 2, 'quantity_base' => 24],
                ['id' => 98, 'unit_id' => 998, 'name' => 'Unidad Paperland', 'factor' => 1, 'quantity' => 3, 'quantity_base' => 3],
            ]];
        $this->sendEvent($this->event('inventory.changed', $inventory))->assertOk();
        $inventory['product_pos_id'] = 12;
        $this->sendEvent($this->event('inventory.changed', $inventory))->assertOk();
        $inventory['store_id'] = 2;
        $this->sendEvent($this->event('inventory.changed', $inventory))->assertOk();
        $inventory['product_pos_id'] = 13;
        $inventory['product_profac_id'] = 30;
        $this->sendEvent($this->event('inventory.changed', $inventory))->assertOk();
        $before = DB::table('recibido_bodega')->get()->all();
        $eventsBefore = DB::table('pos_integration_outbox')->get()->all();
        $mirror = app(\App\Services\PosIntegration\InventoryMirror::class);
        $rows = $mirror->forProduct(10);
        $this->assertSame([11, 12, 12], $rows->pluck('data.product_pos_id')->all());
        $html = view('pos-integration.product-availability', ['inventarioPaperland' => $rows])->render();
        $document = new \DOMDocument();
        $document->loadHTML('<meta charset="UTF-8">'.$html);
        $xpath = new \DOMXPath($document);
        $values = fn ($query) => array_map(fn ($node) => trim($node->textContent), iterator_to_array($xpath->query($query)));
        $this->assertSame(['Cód. Producto Paperland', 'Nombre', 'Bodega', 'Unidad de medida', 'Cantidad'], $values('//th'));
        $this->assertSame(['Caja Paperland', 'Unidad Paperland', 'Caja Paperland', 'Unidad Paperland',
            'Caja Paperland', 'Unidad Paperland'], $values('//tbody/tr/td[4]'));
        $this->assertSame(['2', '3', '2', '3', '2', '3'], $values('//tbody/tr/td[5]'));
        $this->assertStringContainsString('Artículo &lt;azul&gt;', $html);
        $this->assertStringContainsString('Paperland · Tienda 2', $html);
        $this->assertEquals($before, DB::table('recibido_bodega')->get()->all());
        $this->assertEquals($eventsBefore, DB::table('pos_integration_outbox')->get()->all());
        $this->assertTrue($mirror->forProduct(999)->isEmpty());
        config(['pos_integration.enabled' => false]);
        $this->assertTrue($mirror->forProduct(10)->isEmpty());
    }

    public function test_product_mirror_displays_unknown_units_without_turning_them_into_zero(): void
    {
        $inventory = ['store_id' => 1, 'product_pos_id' => 9, 'product_profac_id' => 10, 'name' => 'Artículo',
            'version' => 1, 'physical_base' => null, 'known_physical_base' => 5, 'unresolved_lots' => 2,
            'available_base' => null, 'reserved_base' => null, 'availability_status' => 'presentation_unit_pending',
            'pending_base' => 0, 'reason' => 'legacy', 'presentations' => []];
        $this->sendEvent($this->event('inventory.changed', $inventory))->assertOk();
        $html = view('pos-integration.product-availability', [
            'inventarioPaperland' => app(\App\Services\PosIntegration\InventoryMirror::class)->forProduct(10),
        ])->render();
        $this->assertStringContainsString('<td>Pendiente de regularizar</td>', $html);
        $this->assertStringNotContainsString('<td>0</td>', $html);
        $inventory['version'] = 2;
        $inventory['physical_base'] = $inventory['known_physical_base'] = $inventory['unresolved_lots'] = 0;
        $inventory['available_base'] = $inventory['reserved_base'] = 0;
        $inventory['availability_status'] = 'known';
        $this->sendEvent($this->event('inventory.changed', $inventory))->assertOk();
        $html = view('pos-integration.product-availability', [
            'inventarioPaperland' => app(\App\Services\PosIntegration\InventoryMirror::class)->forProduct(10),
        ])->render();
        $this->assertStringContainsString('<td>Unidades base</td>', $html);
        $this->assertStringContainsString('<td>0</td>', $html);
        $this->assertSame(0, DB::table('recibido_bodega')->count());
    }

    public function test_product_details_reload_persisted_integration_settings(): void
    {
        $this->installSettings();
        DB::table('integration_settings')->where('id', 1)->update([
            'enabled' => true, 'secret' => \Illuminate\Support\Facades\Crypt::encryptString(str_repeat('x', 32)),
            'peer_url' => 'http://localhost:8000', 'store_id' => 1,
        ]);
        foreach (['/producto/detalle/10', '/producto/apoyo/detalle/10'] as $path) {
            config(['pos_integration.enabled' => false]);
            app(\App\Http\Middleware\LoadPosIntegrationSettings::class)->handle(
                \Illuminate\Http\Request::create($path),
                function () {
                    $this->assertTrue(config('pos_integration.enabled'));
                    return response('ok');
                }
            );
        }
    }

    public function test_settings_migration_is_additive_idempotent_and_initially_disabled(): void
    {
        $this->installSettings();
        (require database_path('migrations/2026_11_24_210000_create_paperland_integration_settings.php'))->up();
        $this->assertSame(1, DB::table('integration_settings')->count());
        $this->assertSame(0, DB::table('integration_settings')->where('id', 1)->value('enabled'));
        $menu = DB::table('menu')->where('nombre_menu', 'Configuración')->first();
        $submenu = DB::table('sub_menu')->where('url', 'configuracion/api-paperland')->first();
        $this->assertSame($menu->id, $submenu->menu_id);
        $this->assertSame([1], DB::table('rol_submenu')->where('sub_menu_id', $submenu->id)->pluck('rol_id')->all());
        $this->assertTrue(DB::table('rol_submenu')->where('sub_menu_id', 4)->where('rol_id', 2)->exists());
    }

    public function test_configuration_navigation_merges_existing_links_and_respects_permissions(): void
    {
        $this->installSettings();
        Schema::create('rol', function (Blueprint $t) { $t->id(); });
        Schema::create('usuario_submenu', function (Blueprint $t) {
            $t->integer('usuario_id'); $t->integer('sub_menu_id'); $t->boolean('permitido');
        });
        DB::table('rol')->insert([['id' => 1], ['id' => 2]]);
        $submenu = DB::table('sub_menu')->where('url', 'configuracion/api-paperland')->first();
        DB::table('sub_menu')->insert(['id' => 50, 'url' => 'configuracion/otro', 'nombre' => 'Otro ajuste',
            'menu_id' => $submenu->menu_id, 'orden' => 2, 'estado_id' => 1]);
        DB::table('rol_submenu')->insert([['rol_id' => 1, 'sub_menu_id' => 50],
            ['rol_id' => 2, 'sub_menu_id' => $submenu->id]]);

        $admin = true;
        $roles = [1];
        $user = \Mockery::mock(\App\Models\User::class)->makePartial();
        $user->id = 7;
        $user->shouldReceive('rolesIds')->andReturnUsing(function () use (&$roles) { return $roles; });
        $user->shouldReceive('esAdministrador')->andReturnUsing(function () use (&$admin) { return $admin; });
        $this->actingAs($user);
        $render = fn () => view('partials.menu-dinamico')->render();

        $html = $render();
        $this->assertSame(1, substr_count($html, '>Configuración</span>'));
        $this->assertStringContainsString('Notificaciones', $html);
        $this->assertStringContainsString('Códigos de Autorización', $html);
        $this->assertStringContainsString('/configuracion/api-paperland', $html);
        $this->assertStringContainsString('Otro ajuste', $html);

        DB::table('usuario_submenu')->insert(['usuario_id' => 7, 'sub_menu_id' => $submenu->id, 'permitido' => 0]);
        $html = $render();
        $this->assertSame(1, substr_count($html, '>Configuración</span>'));
        $this->assertStringNotContainsString('/configuracion/api-paperland', $html);
        $this->assertStringContainsString('Otro ajuste', $html);
        $this->assertStringContainsString('Notificaciones', $html);

        DB::table('usuario_submenu')->delete();
        $admin = false;
        $roles = [2];
        $html = $render();
        $this->assertSame(1, substr_count($html, '>Configuración</span>'));
        $this->assertStringContainsString('/configuracion/api-paperland', $html);
        $this->assertStringNotContainsString('Notificaciones', $html);
        $this->assertStringNotContainsString('Códigos de Autorización', $html);
        $this->assertStringNotContainsString('Otro ajuste', $html);
    }

    public function test_profiles_migration_preserves_existing_settings_and_is_idempotent(): void
    {
        $this->installSettings();
        $cipher = \Illuminate\Support\Facades\Crypt::encryptString(str_repeat('x', 32));
        DB::table('integration_settings')->where('id', 1)->update([
            'enabled' => true, 'peer_url' => 'https://pos.example.test', 'store_id' => 7, 'secret' => $cipher,
        ]);
        $migration = require database_path('migrations/2026_11_24_220000_create_paperland_connection_runtime.php');
        $migration->up();
        $runtime = DB::table('integration_runtime')->where('id', 1)->first();
        $migration->up();
        $this->assertSame(1, DB::table('integration_connections')->count());
        $this->assertSame(1, DB::table('integration_runtime')->count());
        $this->assertSame($runtime->instance_id, DB::table('integration_runtime')->value('instance_id'));
        $this->assertTrue(Str::isUuid($runtime->instance_id));
        $this->assertFalse((bool) $runtime->worker_requested);
        $this->assertFalse((bool) $runtime->worker_busy);
        $profile = DB::table('integration_connections')->first();
        $settings = DB::table('integration_settings')->first();
        $this->assertSame($cipher, $profile->secret);
        $this->assertSame($profile->id, $settings->connection_id);
        $this->assertSame(1, (int) $profile->active_slot);
        $this->assertTrue((bool) $settings->enabled);
        $this->assertSame('https://pos.example.test', $profile->peer_url);
        $this->assertSame(7, (int) $profile->store_id);
    }

    public function test_profiles_activate_only_verified_peer_and_preserve_secret_and_permissions(): void
    {
        $this->installProfiles();
        $allowed = false;
        $this->settingsUser($allowed);
        $key = str_repeat('secret-value-', 3);
        $body = ['id' => 1, 'name' => 'Local', 'peer_url' => 'http://pos.test', 'store_id' => 1, 'secret' => $key];
        $this->postJson('/configuracion/api-paperland/perfiles', $body)->assertForbidden();
        $allowed = true;
        $this->postJson('/configuracion/api-paperland/perfiles', $body)->assertOk();
        $cipher = DB::table('integration_connections')->where('id', 1)->value('secret');
        $this->assertNotSame($key, $cipher);
        $this->assertSame($key, \Illuminate\Support\Facades\Crypt::decryptString($cipher));
        $body['secret'] = '';
        $this->postJson('/configuracion/api-paperland/perfiles', $body)->assertOk();
        $this->assertSame($cipher, DB::table('integration_connections')->where('id', 1)->value('secret'));

        \Livewire\Livewire::component('navigation-menu', PaperlandNavigationStub::class);
        $this->withoutVite();
        $page = $this->get('/configuracion/api-paperland')->assertOk()->assertDontSee($key)->assertDontSee($cipher);
        $document = new \DOMDocument;
        $document->loadHTML($page->getContent(), LIBXML_NONET | LIBXML_NOERROR | LIBXML_NOWARNING);
        $xpath = new \DOMXPath($document);
        $this->assertSame(1, $xpath->query('//*[@id="paperland-connection-panel"]//form[@id="profile-form"]')->length);
        $this->assertSame(2, $xpath->query('//*[@id="paperland-guide-panel"]//*[@role="tabpanel"]')->length);
        $this->assertSame(0, $xpath->query('//*[@id="paperland-guide-panel"]//form | //*[@id="paperland-guide-panel"]//input')->length);
        $responseBody = ['schema_version' => 1, 'source' => 'pos'];
        Http::fake(function () use (&$responseBody) { return Http::response($responseBody, 200); });
        $this->postJson('/configuracion/api-paperland/perfiles/1/activar')->assertStatus(502);
        $this->assertFalse((bool) DB::table('integration_settings')->value('enabled'));
        $peer = (string) Str::uuid();
        $responseBody = $this->peerHealth($peer);
        $this->postJson('/configuracion/api-paperland/perfiles/probar', ['id' => 1, 'peer_url' => 'http://pos.test', 'secret' => ''])
            ->assertOk()->assertJsonPath('instance_id', $peer)->assertJsonMissing(['secret' => $key]);
        Http::assertSent(function ($request) use ($key) {
            $timestamp = $request->header('X-Integration-Timestamp')[0];
            return $request->method() === 'GET' && $request->body() === ''
                && hash_equals(hash_hmac('sha256', $timestamp.'.', $key), $request->header('X-Integration-Signature')[0]);
        });
        $this->postJson('/configuracion/api-paperland/perfiles/1/activar')->assertOk();
        $this->assertSame($peer, DB::table('integration_runtime')->value('bound_peer_instance_id'));
        $this->assertTrue((bool) DB::table('integration_settings')->value('enabled'));
        $this->assertFalse((bool) DB::table('integration_runtime')->value('worker_requested'));
        $this->postJson('/configuracion/api-paperland/perfiles', array_merge($body, ['peer_url' => 'http://new.test']))->assertStatus(409);
        $this->postJson('/configuracion/api-paperland/guardar', ['enabled' => true])->assertStatus(410)
            ->assertJsonPath('message', 'La pantalla cambió: recargue la página y use los perfiles de conexión.');
        $this->postJson('/configuracion/api-paperland/desactivar')->assertOk();
        DB::table('integration_runtime')->where('id', 1)->update(['inventory_ready' => true]);
        $this->postJson('/configuracion/api-paperland/perfiles', array_merge($body, ['secret' => str_repeat('r', 32), 'store_id' => 2]))->assertOk();
        $this->assertNull(DB::table('integration_connections')->where('id', 1)->value('peer_instance_id'));
        $this->assertSame($peer, DB::table('integration_runtime')->value('bound_peer_instance_id'));
        $this->assertFalse((bool) DB::table('integration_runtime')->value('inventory_ready'));
        $this->postJson('/configuracion/api-paperland/control/workonce')->assertStatus(409);
        $this->postJson('/configuracion/api-paperland/perfiles/1/activar')->assertOk();
        $this->assertSame($peer, DB::table('integration_connections')->where('id', 1)->value('peer_instance_id'));
    }

    public function test_switch_blocks_different_peer_with_history_and_pending_or_busy_with_same_peer(): void
    {
        $this->installProfiles();
        $allowed = true; $this->settingsUser($allowed);
        $key = str_repeat('k', 32);
        $this->postJson('/configuracion/api-paperland/perfiles', ['id' => 1, 'name' => 'Uno',
            'peer_url' => 'http://one.test', 'store_id' => 1, 'secret' => $key])->assertOk();
        $this->postJson('/configuracion/api-paperland/perfiles', ['name' => 'Dos',
            'peer_url' => 'http://two.test', 'store_id' => 1, 'secret' => $key])->assertOk();
        $peer = (string) Str::uuid();
        $responseBody = $this->peerHealth($peer);
        Http::fake(function () use (&$responseBody) { return Http::response($responseBody, 200); });
        $this->postJson('/configuracion/api-paperland/perfiles/1/activar')->assertOk();
        DB::table('pos_integration_mirror')->insert(['store_id' => 1, 'product_pos_id' => 1, 'version' => 1,
            'snapshot' => '{}', 'created_at' => now(), 'updated_at' => now()]);
        $responseBody = $this->peerHealth((string) Str::uuid());
        $this->postJson('/configuracion/api-paperland/perfiles/2/activar')->assertStatus(409);
        $responseBody = $this->peerHealth($peer);
        DB::table('integration_runtime')->where('id', 1)->update(['worker_busy' => true]);
        $this->postJson('/configuracion/api-paperland/perfiles/2/activar')->assertStatus(409);
        DB::table('integration_runtime')->where('id', 1)->update(['worker_busy' => false]);
        $this->postJson('/configuracion/api-paperland/control/workonce')->assertOk();
        $this->postJson('/configuracion/api-paperland/perfiles/2/activar')->assertStatus(409);
        $this->postJson('/configuracion/api-paperland/perfiles/2/eliminar')->assertStatus(409);
        DB::table('integration_operations')->update(['status' => 'succeeded']);
        $this->postJson('/configuracion/api-paperland/perfiles/2/activar')->assertOk();
        $this->assertSame(2, (int) DB::table('integration_settings')->value('connection_id'));
        $this->postJson('/configuracion/api-paperland/perfiles/2/eliminar')->assertStatus(409);
    }

    public function test_stale_operation_owner_cannot_finish_another_process(): void
    {
        $this->installProfiles();
        $coordinator = app(\App\Services\PosIntegration\Coordinator::class);
        $token = (string) Str::uuid();
        $this->assertTrue($coordinator->acquire($token));
        $id = (string) Str::uuid();
        DB::table('integration_operations')->insert(['id' => $id, 'connection_id' => 1, 'action' => 'workonce',
            'status' => 'running', 'pid' => 222, 'created_at' => now(), 'updated_at' => now()]);
        DB::table('integration_runtime')->where('id', 1)->update(['current_operation_id' => $id, 'worker_busy' => true]);
        $coordinator->finish($token, $id, 111, true);
        $this->assertSame('running', DB::table('integration_operations')->where('id', $id)->value('status'));
        $this->assertTrue((bool) DB::table('integration_runtime')->value('worker_busy'));
        $coordinator->finish((string) Str::uuid(), $id, 222, true);
        $this->assertSame('running', DB::table('integration_operations')->where('id', $id)->value('status'));
        $coordinator->finish($token, $id, 222, true);
        $this->assertSame('succeeded', DB::table('integration_operations')->where('id', $id)->value('status'));
        $this->assertFalse((bool) DB::table('integration_runtime')->value('worker_busy'));
        $coordinator->release($token);
    }

    public function test_migrated_enabled_profile_cannot_process_before_peer_identity_is_verified(): void
    {
        $this->installSettings();
        $key = str_repeat('k', 32);
        DB::table('integration_settings')->where('id', 1)->update(['enabled' => true, 'peer_url' => 'http://pos.test',
            'secret' => \Illuminate\Support\Facades\Crypt::encryptString($key)]);
        (require database_path('migrations/2026_11_24_220000_create_paperland_connection_runtime.php'))->up();
        $allowed = true; $this->settingsUser($allowed);
        $this->postJson('/configuracion/api-paperland/control/start')->assertStatus(409);
        $this->postJson('/configuracion/api-paperland/control/workonce')->assertStatus(409);
        $this->sendEvent($this->event('transfer.dispatched', $this->transfer()))->assertStatus(503);
        $this->assertSame(0, DB::table('pos_integration_inbox')->count());
        $this->assertTrue((bool) DB::table('integration_settings')->value('enabled'));
    }

    public function test_coordinator_lease_pause_and_durable_round(): void
    {
        $this->installProfiles();
        $allowed = true; $this->settingsUser($allowed);
        $key = str_repeat('k', 32);
        $this->postJson('/configuracion/api-paperland/perfiles', ['id' => 1, 'name' => 'Uno',
            'peer_url' => 'http://one.test', 'store_id' => 1, 'secret' => $key])->assertOk();
        Http::fake(['*' => Http::response($this->peerHealth((string) Str::uuid()), 200)]);
        $this->postJson('/configuracion/api-paperland/perfiles/1/activar')->assertOk();
        $this->postJson('/configuracion/api-paperland/control/workonce')->assertOk();
        $this->assertSame(7, (int) DB::table('integration_operations')->value('requested_by'));
        $this->assertSame('pending', DB::table('integration_operations')->value('status'));
        $coordinator = app(\App\Services\PosIntegration\Coordinator::class);
        $token = (string) Str::uuid();
        $this->assertTrue($coordinator->acquire($token));
        $this->assertFalse($coordinator->acquire((string) Str::uuid()));
        $this->assertTrue($coordinator->heartbeat($token));
        $op = $coordinator->claim($token);
        $this->assertSame('running', DB::table('integration_operations')->value('status'));
        $this->postJson('/configuracion/api-paperland/control/pause')->assertOk();
        $runtime = DB::table('integration_runtime')->where('id', 1)->first();
        $this->assertTrue((bool) $runtime->worker_busy);
        $this->assertSame('stopping', \App\Services\PosIntegration\Coordinator::state($runtime));
        $this->postJson('/configuracion/api-paperland/desactivar')->assertStatus(409);
        $coordinator->run($token, $op, true);
        $this->assertSame('succeeded', DB::table('integration_operations')->value('status'));
        $this->assertSame('Ronda procesada.', DB::table('integration_operations')->value('output'));
        $this->assertFalse((bool) DB::table('integration_runtime')->value('worker_busy'));
        $coordinator->release($token);
        $this->postJson('/configuracion/api-paperland/control/workonce')->assertOk();
        $this->artisan('pos-integration:manage', ['--once' => true])->assertExitCode(0);
        $this->assertSame(2, DB::table('integration_operations')->where('status', 'succeeded')->count());
        $this->assertNull(DB::table('integration_runtime')->value('coordinator_token'));
        $this->assertFalse(\App\Services\PosIntegration\Coordinator::online(DB::table('integration_runtime')->first()));
    }

    public function test_stale_coordinator_recovers_running_operation_and_health_reports_real_lease(): void
    {
        $this->installProfiles();
        $key = str_repeat('k', 32);
        $cipher = \Illuminate\Support\Facades\Crypt::encryptString($key);
        DB::table('integration_settings')->where('id', 1)->update(['secret' => $cipher]);
        $id = (string) Str::uuid();
        DB::table('integration_operations')->insert(['id' => $id, 'connection_id' => 1,
            'action' => 'workonce', 'status' => 'running', 'created_at' => now(), 'updated_at' => now()]);
        DB::table('integration_runtime')->where('id', 1)->update(['coordinator_token' => (string) Str::uuid(),
            'coordinator_heartbeat_at' => now()->subMinute(), 'coordinator_lease_until' => now()->subSecond(),
            'current_operation_id' => $id, 'worker_busy' => true]);
        $timestamp = (string) time();
        $headers = ['HTTP_ACCEPT' => 'application/json', 'HTTP_X_INTEGRATION_TIMESTAMP' => $timestamp,
            'HTTP_X_INTEGRATION_SIGNATURE' => hash_hmac('sha256', $timestamp.'.', $key)];
        $this->call('GET', '/api/pos-integration/health', [], [], [], $headers, '')->assertOk()
            ->assertJsonPath('source', 'profac')->assertJsonPath('prepared', true)
            ->assertJsonPath('coordinator_online', false)->assertJsonPath('worker_state', 'offline')
            ->assertJsonPath('instance_id', DB::table('integration_runtime')->value('instance_id'));
        $token = (string) Str::uuid();
        $coordinator = app(\App\Services\PosIntegration\Coordinator::class);
        $this->assertTrue($coordinator->acquire($token));
        $this->assertSame('failed', DB::table('integration_operations')->where('id', $id)->value('status'));
        $this->assertFalse((bool) DB::table('integration_runtime')->value('worker_busy'));
        $this->call('GET', '/api/pos-integration/health', [], [], [], $headers, '')->assertOk()
            ->assertJsonPath('coordinator_online', true)->assertJsonPath('worker_state', 'paused');
        $this->artisan('pos-integration:work', ['--once' => true])->assertExitCode(1);
        $coordinator->release($token);
    }

    public function test_coordinator_executes_real_child_against_isolated_sqlite_file(): void
    {
        $this->installProfiles();
        $allowed = true; $this->settingsUser($allowed);
        $this->postJson('/configuracion/api-paperland/perfiles', ['id' => 1, 'name' => 'Uno',
            'peer_url' => 'http://pos.test', 'store_id' => 1, 'secret' => str_repeat('k', 32)])->assertOk();
        Http::fake(['*' => Http::response($this->peerHealth((string) Str::uuid()), 200)]);
        $this->postJson('/configuracion/api-paperland/perfiles/1/activar')->assertOk();
        $this->postJson('/configuracion/api-paperland/control/workonce')->assertOk();
        $path = base_path('sqlite-coordinator-'.Str::uuid().'.sqlite');
        try {
            DB::statement('VACUUM INTO ?', [$path]);
            $process = new \Symfony\Component\Process\Process([PHP_BINARY, base_path('artisan'), 'pos-integration:manage', '--once'],
                base_path(), ['DB_CONNECTION' => 'sqlite', 'DB_DATABASE' => $path, 'DATABASE_URL' => '',
                    'APP_ENV' => 'testing', 'APP_KEY' => 'base64:'.base64_encode(str_repeat('k', 32)),
                    'CACHE_STORE' => 'array', 'CACHE_DRIVER' => 'array']);
            $process->setTimeout(30);
            $process->run();
            $this->assertSame(0, $process->getExitCode(), $process->getErrorOutput());
            $pdo = new \PDO('sqlite:'.$path);
            $operation = $pdo->query('SELECT status, pid, output FROM integration_operations')->fetch(\PDO::FETCH_ASSOC);
            $this->assertSame('succeeded', $operation['status']);
            $this->assertGreaterThan(0, (int) $operation['pid']);
            $this->assertSame('Ronda procesada.', $operation['output']);
            $runtime = $pdo->query('SELECT coordinator_token, worker_busy FROM integration_runtime WHERE id=1')->fetch(\PDO::FETCH_ASSOC);
            $this->assertNull($runtime['coordinator_token']);
            $this->assertSame(0, (int) $runtime['worker_busy']);
            $pdo = null;
        } finally {
            $pdo = null;
            if (is_file($path)) { unlink($path); }
        }
    }

    public function test_service_download_and_control_require_permission(): void
    {
        $this->installProfiles();
        $allowed = false; $this->settingsUser($allowed);
        $this->get('/configuracion/api-paperland/servicio')->assertForbidden();
        $this->postJson('/configuracion/api-paperland/control/start')->assertForbidden();
        $allowed = true;
        $this->get('/configuracion/api-paperland/servicio')->assertOk()
            ->assertSee('pos-integration:manage')->assertSee('KillMode=mixed')->assertSee('User=www-data');
        $this->postJson('/configuracion/api-paperland/control/start')->assertStatus(409);
        $this->postJson('/configuracion/api-paperland/control/shell')->assertNotFound();
    }

    public function test_start_worker_requires_live_supervisor_on_vps_and_rejects_wrong_profile(): void
    {
        $this->installProfiles();
        $allowed = true; $this->settingsUser($allowed);
        $this->postJson('/configuracion/api-paperland/perfiles', ['id' => 1, 'name' => 'Local',
            'peer_url' => 'http://pos.test', 'store_id' => 1, 'secret' => str_repeat('k', 32)])->assertOk();
        Http::fake(['*' => Http::response($this->peerHealth((string) Str::uuid()), 200)]);
        $this->postJson('/configuracion/api-paperland/perfiles/1/activar')->assertOk();
        config(['app.env' => 'production']);
        $this->postJson('/configuracion/api-paperland/control/start', ['profile_id' => 1])->assertUnprocessable();
        $this->assertFalse((bool) DB::table('integration_runtime')->value('worker_requested'));
        $coordinator = app(\App\Services\PosIntegration\Coordinator::class);
        $token = (string) Str::uuid();
        $this->assertTrue($coordinator->acquire($token));
        $this->postJson('/configuracion/api-paperland/control/start', ['profile_id' => 2])->assertStatus(409);
        $this->assertFalse((bool) DB::table('integration_runtime')->value('worker_requested'));
        $this->postJson('/configuracion/api-paperland/control/start', ['profile_id' => 1])->assertOk();
        $this->assertTrue((bool) DB::table('integration_runtime')->value('worker_requested'));
        $this->postJson('/configuracion/api-paperland/control/pause')->assertOk();
        $coordinator->release($token);
    }

    public function test_local_supervisor_launch_is_idempotent_and_not_allowed_on_vps(): void
    {
        $supervisor = new class extends \App\Services\PosIntegration\LocalSupervisor {
            public bool $started = false;
            public int $launches = 0;
            protected function online(): bool { return $this->started; }
            protected function launch(): void { $this->launches++; $this->started = true; }
        };
        config(['app.env' => 'production', 'cache.default' => 'array']);
        $this->app->instance('env', 'production');
        try {
            $supervisor->ensureOnline();
            $this->fail('Un VPS sin supervisor no debe confirmar el inicio.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->assertSame(0, $supervisor->launches);
        }
        if (PHP_OS_FAMILY === 'Windows') {
            config(['app.env' => 'local']);
            $this->app->instance('env', 'local');
            $supervisor->ensureOnline();
            $supervisor->ensureOnline();
            $this->assertSame(1, $supervisor->launches);
        }
        $supervisor->started = true;
        config(['app.env' => 'production']);
        $this->app->instance('env', 'production');
        $before = $supervisor->launches;
        $supervisor->ensureOnline();
        $this->assertSame($before, $supervisor->launches);
    }

    public function test_environment_fallback_only_when_settings_row_is_absent(): void
    {
        $settings = app(\App\Services\PosIntegration\Settings::class);
        $settings->reload();
        $this->assertTrue((bool) config('pos_integration.enabled'));
        $this->assertSame('test-secret', config('pos_integration.secret'));
        $this->installSettings();
        $settings->reload();
        $this->assertFalse((bool) config('pos_integration.enabled'));
        $this->assertNull(config('pos_integration.secret'));
        DB::table('integration_settings')->where('id', 1)->delete();
        $settings->reload();
        $this->assertTrue((bool) config('pos_integration.enabled'));
        $this->assertSame('test-secret', config('pos_integration.secret'));
    }

    public function test_settings_save_requires_permission_encrypts_and_preserves_blank_secret(): void
    {
        $this->installSettings();
        $allowed = false; $this->settingsUser($allowed);
        $key = str_repeat('z', 40);
        $data = ['enabled' => false, 'peer_url' => 'http://127.0.0.1:8000', 'store_id' => 1, 'secret' => $key];
        $this->postJson('/configuracion/api-paperland/guardar', $data)->assertForbidden();
        $this->postJson('/configuracion/api-paperland/generar-clave')->assertForbidden();
        $allowed = true;
        $this->postJson('/configuracion/api-paperland/guardar', [
            'enabled' => true, 'peer_url' => 'http://127.0.0.1:8000', 'store_id' => 1, 'secret' => '',
        ])->assertStatus(422);
        $this->assertSame(0, DB::table('integration_settings')->where('id', 1)->value('enabled'));
        $response = $this->postJson('/configuracion/api-paperland/guardar', $data)->assertOk();
        $this->assertStringNotContainsString($key, $response->getContent());
        $encrypted = DB::table('integration_settings')->where('id', 1)->value('secret');
        $this->assertNotSame($key, $encrypted);
        $this->assertSame($key, \Illuminate\Support\Facades\Crypt::decryptString($encrypted));
        $this->assertSame(7, DB::table('integration_settings')->where('id', 1)->value('updated_by'));
        $data['secret'] = ''; $data['enabled'] = true;
        $this->postJson('/configuracion/api-paperland/guardar', $data)->assertOk();
        $this->assertSame($encrypted, DB::table('integration_settings')->where('id', 1)->value('secret'));
        $this->assertTrue((bool) config('pos_integration.enabled'));
        $data['peer_url'] = 'http://user:pass@localhost:8000';
        $this->postJson('/configuracion/api-paperland/guardar', $data)->assertStatus(422);
        $this->assertSame('http://127.0.0.1:8000', DB::table('integration_settings')->where('id', 1)->value('peer_url'));
        $generated = $this->postJson('/configuracion/api-paperland/generar-clave')->assertOk()->json('secret');
        $this->assertMatchesRegularExpression('/^[a-f0-9]{64}$/D', $generated);
        $this->assertSame($encrypted, DB::table('integration_settings')->where('id', 1)->value('secret'));
    }

    public function test_settings_page_never_renders_the_stored_secret(): void
    {
        $this->installSettings();
        $key = str_repeat('secret-fixture-', 3);
        $encrypted = \Illuminate\Support\Facades\Crypt::encryptString($key);
        DB::table('integration_settings')->where('id', 1)->update(['secret' => $encrypted]);
        \Livewire\Livewire::component('navigation-menu', PaperlandNavigationStub::class);
        $allowed = false; $this->settingsUser($allowed);
        $this->get('/configuracion/api-paperland')->assertForbidden();
        $allowed = true;
        $response = $this->get('/configuracion/api-paperland')->assertOk();
        $this->assertStringContainsString('no-store', $response->headers->get('Cache-Control'));
        $response->assertSee('Tiene clave guardada.')->assertDontSee($key, false)->assertDontSee($encrypted, false);
        preg_match('/<input id="paperland-secret"[^>]*>/', $response->getContent(), $input);
        $this->assertNotEmpty($input);
        $this->assertStringNotContainsString('value=', $input[0]);
    }

    public function test_signed_health_works_disabled_and_settings_reload_per_request(): void
    {
        $this->installSettings();
        $key = str_repeat('s', 40);
        DB::table('integration_settings')->where('id', 1)->update([
            'enabled' => 0, 'peer_url' => 'http://127.0.0.1:8000',
            'secret' => \Illuminate\Support\Facades\Crypt::encryptString($key),
        ]);
        $timestamp = (string) time();
        $headers = ['HTTP_ACCEPT' => 'application/json', 'HTTP_X_INTEGRATION_TIMESTAMP' => $timestamp,
            'HTTP_X_INTEGRATION_SIGNATURE' => hash_hmac('sha256', $timestamp.'.', $key)];
        $this->call('GET', '/api/pos-integration/health', [], [], [], $headers, '')
            ->assertOk()->assertJson(['schema_version' => 1, 'source' => 'profac', 'enabled' => false, 'worker_last_seen_at' => null]);
        $bad = $headers; $bad['HTTP_X_INTEGRATION_SIGNATURE'] = str_repeat('0', 64);
        $this->call('GET', '/api/pos-integration/health', [], [], [], $bad, '')->assertStatus(401);
        $expired = (string) (time() - 301);
        $old = $headers; $old['HTTP_X_INTEGRATION_TIMESTAMP'] = $expired;
        $old['HTTP_X_INTEGRATION_SIGNATURE'] = hash_hmac('sha256', $expired.'.', $key);
        $this->call('GET', '/api/pos-integration/health', [], [], [], $old, '')->assertStatus(401);
        $this->call('GET', '/api/pos-integration/health', [], [], [], ['HTTP_ACCEPT' => 'application/json'], '')->assertStatus(401);
        $this->call('GET', '/api/pos-integration/health', [], [], [], $headers, 'x')->assertStatus(401);
        $this->sendEvent($this->event('transfer.dispatched', $this->transfer()))->assertStatus(503);
        DB::table('integration_settings')->where('id', 1)->update(['enabled' => 1]);
        $this->call('GET', '/api/pos-integration/health', [], [], [], $headers, '')->assertOk()->assertJsonPath('enabled', true);
        DB::table('integration_settings')->where('id', 1)->update(['secret' => 'invalid ciphertext']);
        $this->call('GET', '/api/pos-integration/health', [], [], [], $headers, '')->assertStatus(503);
        $this->assertFalse((bool) config('pos_integration.enabled'));
    }

    public function test_probe_uses_unsaved_configuration_without_enabling_or_sending_stock(): void
    {
        $this->installSettings();
        $allowed = true; $this->settingsUser($allowed);
        $key = str_repeat('p', 40);
        Http::fake(['*' => Http::response(['schema_version' => 1, 'source' => 'pos',
            'enabled' => false, 'worker_last_seen_at' => null], 200)]);
        $this->postJson('/configuracion/api-paperland/probar', ['peer_url' => 'http://127.0.0.1:8000', 'secret' => $key])
            ->assertOk()->assertJsonPath('peer_enabled', false);
        Http::assertSent(function ($request) use ($key) {
            $timestamp = $request->header('X-Integration-Timestamp')[0];
            return $request->method() === 'GET' && $request->url() === 'http://127.0.0.1:8000/api/pos-integration/health'
                && $request->body() === ''
                && hash_equals(hash_hmac('sha256', $timestamp.'.', $key), $request->header('X-Integration-Signature')[0]);
        });
        $this->assertSame(0, DB::table('integration_settings')->where('id', 1)->value('enabled'));
        $this->assertNull(DB::table('integration_settings')->where('id', 1)->value('secret'));
        $this->assertSame(0, DB::table('pos_integration_outbox')->count());
    }

    public function test_worker_reloads_settings_and_heartbeats_when_disabled_then_enabled(): void
    {
        $this->installSettings();
        $key = str_repeat('w', 40);
        DB::table('integration_settings')->where('id', 1)->update([
            'enabled' => 0, 'peer_url' => 'http://127.0.0.1:8000', 'secret' => \Illuminate\Support\Facades\Crypt::encryptString($key),
        ]);
        app(\App\Services\PosIntegration\Outbox::class)->enqueue('transfer.rejected', [
            'transfer_uuid' => (string) Str::uuid(), 'actor_id' => 7, 'reason' => 'No corresponde',
        ]);
        Http::fake(['*' => Http::response(['status' => 'processed'], 200)]);
        $this->artisan('pos-integration:work', ['--once' => true])->assertSuccessful();
        Http::assertNothingSent();
        $this->assertNotNull(\Illuminate\Support\Facades\Cache::get('pos_integration.worker_last_seen_at'));
        $this->assertSame('pending', DB::table('pos_integration_outbox')->value('status'));
        DB::table('integration_settings')->where('id', 1)->update(['enabled' => 1]);
        $this->artisan('pos-integration:work', ['--once' => true])->assertSuccessful();
        $this->assertSame('sent', DB::table('pos_integration_outbox')->value('status'));
        Http::assertSent(function ($request) use ($key) {
            $timestamp = $request->header('X-Integration-Timestamp')[0];
            return hash_equals(hash_hmac('sha256', $timestamp.'.'.$request->body(), $key), $request->header('X-Integration-Signature')[0]);
        });
        app(\App\Services\PosIntegration\Outbox::class)->enqueue('transfer.rejected', [
            'transfer_uuid' => (string) Str::uuid(), 'actor_id' => 7, 'reason' => 'No corresponde',
        ]);
        DB::table('integration_settings')->where('id', 1)->update(['enabled' => 0]);
        $this->artisan('pos-integration:work', ['--once' => true])->assertSuccessful();
        $this->assertSame(1, DB::table('pos_integration_outbox')->where('status', 'pending')->count());
        Http::assertSentCount(1);
    }

    public function test_probe_refuses_redirect_and_wrong_peer_source(): void
    {
        $this->installSettings();
        $allowed = true; $this->settingsUser($allowed);
        $payload = ['peer_url' => 'http://127.0.0.1:8000', 'secret' => str_repeat('p', 40)];
        $calls = 0;
        Http::fake(function ($request, $options) use (&$calls) {
            $this->assertFalse($options['allow_redirects']);
            if (++$calls === 1) {
                return Http::response('', 302, ['Location' => 'http://elsewhere.test/']);
            }
            return Http::response(['schema_version' => 1, 'source' => 'profac',
                'enabled' => false, 'worker_last_seen_at' => null], 200);
        });
        $this->postJson('/configuracion/api-paperland/probar', $payload)->assertStatus(502);
        $this->postJson('/configuracion/api-paperland/probar', $payload)->assertStatus(502);
        $this->assertSame(2, $calls);
    }

    public function test_real_pos_contract_events_and_export_real_profac_envelope(): void
    {
        $input = getenv('POS_CONTRACT_EVENTS_FILE');
        $output = getenv('PROFAC_CONTRACT_EXPORT_FILE');
        if (!$input || !$output) {
            $this->markTestSkipped('Defina POS_CONTRACT_EVENTS_FILE y PROFAC_CONTRACT_EXPORT_FILE para esta prueba cruzada.');
        }
        $json = file_get_contents($input);
        $this->assertNotFalse($json);
        $events = $this->rawEvents($json);
        $this->assertCount(3, $events);
        DB::table('producto')->insert(['id' => 101, 'nombre' => 'Cuaderno', 'costo_promedio' => 2]);
        DB::table('unidad_medida')->insert(['id' => 202, 'unidad' => 'Caja', 'nombre' => 'Caja', 'simbolo' => 'cj']);
        foreach ($events as $raw) {
            $this->sendRawEvent($raw)->assertOk()->assertJsonPath('status', 'processed');
            $this->sendRawEvent($raw)->assertOk()->assertJsonPath('status', 'duplicate');
        }
        $this->assertSame(3, DB::table('pos_integration_inbox')->count());
        $this->assertSame('pending', DB::table('pos_integration_transfers')->value('status'));
        $this->assertSame(0, DB::table('recibido_bodega')->count());
        $mirror = json_decode(DB::table('pos_integration_mirror')->value('snapshot'), true, 512, JSON_THROW_ON_ERROR);
        $this->assertSame(2, $mirror['version']);
        $this->assertNull($mirror['physical_base']);
        $this->assertNull($mirror['available_base']);
        $this->assertNull($mirror['reserved_base']);
        $this->assertSame(24, $mirror['known_physical_base']);
        $this->assertSame(1, $mirror['unresolved_lots']);
        $this->assertSame('presentation_unit_pending', $mirror['availability_status']);
        $this->assertSame(2, $mirror['presentations'][0]['unit_id']);
        $this->assertSame(2, DB::table('pos_integration_movements')->where('type', 'inventory.changed')->count());

        DB::table('unidad_medida_venta')->insert(['id' => 303, 'producto_id' => 101, 'unidad_medida_id' => 202,
            'unidad_venta' => 12, 'estado_id' => 1, 'unidad_venta_defecto' => 1]);
        DB::table('translado')->insert(['id' => 50, 'codigo' => 'PROFAC-FIXTURE-50', 'comentario' => 'Contrato aislado']);
        DB::table('recibido_bodega')->insert(['id' => 41, 'compra_id' => null, 'producto_id' => 101, 'seccion_id' => 3,
            'cantidad_compra_lote' => 24, 'cantidad_inicial_seccion' => 24, 'cantidad_disponible' => 12,
            'fecha_recibido' => '2026-11-24', 'fecha_expiracion' => '2027-01-01', 'estado_recibido' => 4,
            'recibido_por' => 7, 'unidad_compra_id' => 202, 'unidades_compra' => 12, 'created_at' => now(), 'updated_at' => now()]);
        DB::table('log_translado')->insert(['id' => 51, 'translado_id' => 50, 'origen' => 41, 'destino' => null,
            'cantidad' => 12, 'unidad_medida_venta_id' => 303, 'descripcion' => 'Translado de bodega']);
        DB::transaction(fn () => app(\App\Services\PosIntegration\Outbox::class)->captureTransfer(50, 7, [51]));
        $payload = DB::table('pos_integration_outbox')->value('payload');
        $sent = json_decode($payload, true, 512, JSON_THROW_ON_ERROR);
        $this->assertSame('profac', $sent['source']);
        $this->assertSame('transfer.dispatched', $sent['type']);
        $this->assertSame(1, $sent['data']['destination_store_id']);
        $this->assertSame(101, $sent['data']['lines'][0]['product_profac_id']);
        $this->assertSame(202, $sent['data']['lines'][0]['unit_profac_id']);
        $this->assertSame(303, $sent['data']['lines'][0]['presentation_profac_id']);
        $this->assertSame(12, $sent['data']['lines'][0]['source_factor']);
        $this->assertSame(12, $sent['data']['lines'][0]['quantity_base']);
        $this->assertSame(41, $sent['data']['lines'][0]['source_lot_id']);
        $this->assertEquals('2', $sent['data']['lines'][0]['cost_base']);
        $this->assertSame('2027-01-01', $sent['data']['lines'][0]['expires_at']);
        $cwd = getcwd();
        try {
            $this->assertTrue(chdir(dirname($output)));
            $this->assertNotFalse(file_put_contents(basename($output), '['.$payload.']'));
        } finally {
            chdir($cwd);
        }
    }
}
