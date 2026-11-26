<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('integration_connections')) {
            Schema::create('integration_connections', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('peer_url', 2048);
                $table->unsignedInteger('store_id')->default(1);
                $table->text('secret')->nullable();
                $table->unsignedTinyInteger('active_slot')->nullable()->unique();
                $table->uuid('peer_instance_id')->nullable();
                $table->unsignedBigInteger('updated_by')->nullable();
                $table->timestamps();
            });
        }
        if (!Schema::hasTable('integration_runtime')) {
            Schema::create('integration_runtime', function (Blueprint $table) {
                $table->unsignedTinyInteger('id')->primary();
                $table->uuid('instance_id')->unique();
                $table->uuid('bound_peer_instance_id')->nullable();
                $table->boolean('worker_requested')->default(false);
                $table->boolean('worker_busy')->default(false);
                $table->uuid('coordinator_token')->nullable();
                $table->timestamp('coordinator_heartbeat_at')->nullable();
                $table->unsignedInteger('coordinator_pid')->nullable();
                $table->timestamp('coordinator_lease_until')->nullable();
                $table->uuid('current_operation_id')->nullable();
                $table->text('last_error')->nullable();
                $table->timestamp('catalog_watermark')->nullable();
                $table->timestamp('catalog_next_at')->nullable();
                $table->boolean('bootstrap_ready')->default(false);
                $table->boolean('catalog_ready')->default(false);
                $table->boolean('inventory_ready')->default(false);
                $table->timestamps();
            });
        }
        if (!Schema::hasTable('integration_operations')) {
            Schema::create('integration_operations', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->unsignedBigInteger('connection_id');
                $table->string('action', 30);
                $table->unsignedBigInteger('requested_by')->nullable();
                $table->string('status', 20)->default('pending')->index();
                $table->unsignedInteger('pid')->nullable();
                $table->timestamp('started_at')->nullable();
                $table->timestamp('finished_at')->nullable();
                $table->text('output')->nullable();
                $table->text('error')->nullable();
                $table->timestamps();
            });
        }
        if (!Schema::hasColumn('integration_settings', 'connection_id')) {
            Schema::table('integration_settings', function (Blueprint $table) {
                $table->unsignedBigInteger('connection_id')->nullable();
            });
        }
        DB::transaction(function () {
            DB::table('integration_runtime')->insertOrIgnore([
                'id' => 1, 'instance_id' => (string) Str::uuid(), 'worker_requested' => false,
                'created_at' => now(), 'updated_at' => now(),
            ]);
            $settings = DB::table('integration_settings')->where('id', 1)->lockForUpdate()->first();
            if ($settings && !$settings->connection_id) {
                $connection = DB::table('integration_connections')->where('active_slot', 1)->first();
                $id = $connection ? $connection->id : DB::table('integration_connections')->insertGetId([
                    'name' => 'Default/actual', 'peer_url' => $settings->peer_url,
                    'store_id' => $settings->store_id, 'secret' => $settings->secret,
                    'active_slot' => 1, 'updated_by' => $settings->updated_by,
                    'created_at' => now(), 'updated_at' => now(),
                ]);
                DB::table('integration_settings')->where('id', 1)->update(['connection_id' => $id]);
            }
        });
    }

    public function down(): void
    {
        // Connection identity and queued operations must not be discarded.
    }
};
