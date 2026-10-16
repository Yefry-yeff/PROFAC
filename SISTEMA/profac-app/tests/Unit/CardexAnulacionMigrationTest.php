<?php

namespace Tests\Unit;

use Illuminate\Container\Container;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Facade;
use Mockery;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class CardexAnulacionMigrationTest extends TestCase
{
    private $previousApplication;
    private $database;

    protected function setUp(): void
    {
        parent::setUp();
        $this->previousApplication = Facade::getFacadeApplication();
        Facade::clearResolvedInstances();
        Facade::setFacadeApplication(new Container);
        $this->database = Mockery::mock();
        DB::swap($this->database);
    }

    protected function tearDown(): void
    {
        try {
            Mockery::close();
        } finally {
            Facade::clearResolvedInstances();
            Facade::setFacadeApplication($this->previousApplication);
            parent::tearDown();
        }
    }

    private function migration()
    {
        return require __DIR__ . '\..\..\database\migrations\2026_10_15_000003_correct_factura_anulada_cardex_sign.php';
    }

    private function trigger(string $sign = '-'): string
    {
        return "CREATE DEFINER=`root`@`localhost` TRIGGER `trg_after_insert_log_translado_unificado`
AFTER INSERT ON log_translado FOR EACH ROW BEGIN
IF NEW.descripcion = 'Factura Anulada' AND NEW.destino IS NOT NULL THEN
INSERT INTO cardex SELECT CONCAT(NEW.descripcion, ' ($sign)');
END IF;
IF NEW.descripcion = 'Venta de producto' THEN
INSERT INTO cardex SELECT CONCAT(NEW.descripcion, ' (-)');
END IF;
END";
    }

    private function expectTrigger(string $definition): void
    {
        $this->database->shouldReceive('selectOne')
            ->with('SHOW CREATE TRIGGER trg_after_insert_log_translado_unificado')
            ->once()->andReturn((object) [
                'SQL Original Statement' => $definition,
                'sql_mode' => 'NO_AUTO_VALUE_ON_ZERO',
            ]);
    }

    private function expectReplacementSession(): void
    {
        $this->database->shouldReceive('selectOne')->with('SELECT @@SESSION.sql_mode AS sql_mode')
            ->once()->andReturn((object) ['sql_mode' => 'STRICT_TRANS_TABLES']);
        $this->database->shouldReceive('statement')->with('SET SESSION sql_mode = ?', ['NO_AUTO_VALUE_ON_ZERO'])
            ->once()->ordered()->andReturnTrue();
        $this->database->shouldReceive('unprepared')->with('DROP TRIGGER `trg_after_insert_log_translado_unificado`')
            ->once()->ordered()->andReturnTrue();
    }

    private function expectSessionRestore(): void
    {
        $this->database->shouldReceive('statement')->with('SET SESSION sql_mode = ?', ['STRICT_TRANS_TABLES'])
            ->once()->ordered()->andReturnTrue();
    }

    private function expectHistoricalCorrection(): void
    {
        $query = Mockery::mock();
        $this->database->shouldReceive('table')->with('cardex')->once()->andReturn($query);
        $query->shouldReceive('where')->with('descripcion', 'Factura Anulada (-)')->once()->andReturnSelf();
        $query->shouldReceive('update')->with(['descripcion' => 'Factura Anulada (+)'])->once()->andReturn(3507);
    }

    public function test_corrects_only_cancellation_branch_and_historical_description(): void
    {
        $this->expectTrigger($this->trigger());
        $this->expectReplacementSession();
        $this->database->shouldReceive('unprepared')->with($this->trigger('+'))
            ->once()->ordered()->andReturnTrue();
        $this->expectSessionRestore();
        $this->expectHistoricalCorrection();

        $this->migration()->up();
        $this->addToAssertionCount(1);
    }

    public function test_already_correct_trigger_is_not_recreated(): void
    {
        $this->expectTrigger($this->trigger('+'));
        $this->expectHistoricalCorrection();

        $this->migration()->up();
        $this->addToAssertionCount(1);
    }

    public function test_unexpected_trigger_fails_before_any_write(): void
    {
        $this->expectTrigger(str_replace('NEW.destino IS NOT NULL', 'NEW.origen IS NOT NULL', $this->trigger()));
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('una unica rama');

        $this->migration()->up();
    }

    public function test_ambiguous_trigger_fails_before_any_write(): void
    {
        $this->expectTrigger($this->trigger() . $this->trigger());
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('una unica rama');

        $this->migration()->up();
    }

    public function test_failed_creation_restores_original_trigger_and_session_without_backfill(): void
    {
        $this->expectTrigger($this->trigger());
        $this->expectReplacementSession();
        $this->database->shouldReceive('unprepared')->with($this->trigger('+'))
            ->once()->ordered()->andThrow(new RuntimeException('Error de recreacion'));
        $this->database->shouldReceive('unprepared')->with($this->trigger())
            ->once()->ordered()->andReturnTrue();
        $this->expectSessionRestore();
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Error de recreacion');

        $this->migration()->up();
    }
}
