<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('item_service_level', function (Blueprint $table) {
            $table->foreignId('item_id')
                ->references('id')
                ->on('items');

            $table->foreignId('service_level_id')
                ->references('id')
                ->on('service_levels');

            $table->boolean('in_output')->default(false);

            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->nullable()->useCurrentOnUpdate();

            $table->primary(['item_id', 'service_level_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('item_service_level');
    }
};
