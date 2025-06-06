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
        Schema::create('service_level_services', function (Blueprint $table) {
            $table->id();

            $table->foreignId('service_level_id')
                ->references('id')
                ->on('service_levels');

            $table->string('name');
            $table->integer('purchase_price')->nullable();
            $table->integer('sale_price')->nullable();
            $table->boolean('in_output')->default(false);

            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->nullable()->useCurrentOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_level_services');
    }
};
