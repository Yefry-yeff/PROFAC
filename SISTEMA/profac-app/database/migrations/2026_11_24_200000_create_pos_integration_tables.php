<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('pos_integration_inbox', function (Blueprint $table) {
            $table->id();
            $table->uuid('event_id')->unique();
            $table->string('payload_hash', 64);
            $table->longText('payload');
            $table->string('status', 20);
            $table->text('error')->nullable();
            $table->timestamps();
        });
        Schema::create('pos_integration_outbox', function (Blueprint $table) {
            $table->id();
            $table->uuid('event_id')->unique();
            $table->longText('payload');
            $table->string('status', 20)->default('pending')->index();
            $table->unsignedInteger('attempts')->default(0);
            $table->timestamp('next_attempt_at')->nullable()->index();
            $table->timestamp('locked_until')->nullable();
            $table->uuid('lock_token')->nullable();
            $table->text('error')->nullable();
            $table->timestamps();
        });
        Schema::create('pos_integration_transfers', function (Blueprint $table) {
            $table->id();
            $table->uuid('transfer_uuid')->unique();
            $table->string('direction', 10);
            $table->string('status', 20);
            $table->string('reference');
            $table->unsignedBigInteger('local_transfer_id')->nullable()->unique();
            $table->string('document_hash', 64);
            $table->longText('snapshot');
            $table->unsignedBigInteger('actor_id')->nullable();
            $table->text('reason')->nullable();
            $table->timestamps();
        });
        Schema::create('pos_integration_lines', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('transfer_id')->index();
            $table->uuid('line_uuid')->unique();
            $table->unsignedBigInteger('local_log_id')->nullable()->unique();
            $table->longText('snapshot');
            $table->timestamps();
        });
        Schema::create('pos_integration_movements', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('transfer_id')->nullable()->index();
            $table->uuid('event_id')->nullable()->index();
            $table->string('type', 40);
            $table->longText('snapshot');
            $table->timestamps();
        });
        Schema::create('pos_integration_mirror', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('store_id');
            $table->unsignedBigInteger('product_pos_id');
            $table->unsignedBigInteger('version');
            $table->longText('snapshot');
            $table->timestamps();
            $table->unique(['store_id', 'product_pos_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pos_integration_mirror');
        Schema::dropIfExists('pos_integration_movements');
        Schema::dropIfExists('pos_integration_lines');
        Schema::dropIfExists('pos_integration_transfers');
        Schema::dropIfExists('pos_integration_outbox');
        Schema::dropIfExists('pos_integration_inbox');
    }
};
