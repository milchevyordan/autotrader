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
        Schema::create('transportables', function (Blueprint $table) {
            $table
                ->foreignId('transport_order_id')
                ->references('id')
                ->on('transport_orders');

            $table->morphs('transportable');

            $table->foreignId('location_id')
                ->nullable()
                ->constrained('company_addresses');

            $table->string('location_free_text', 500)->nullable();
            $table->integer('price')->nullable();

            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->nullable()->useCurrentOnUpdate();

            $table->primary(['transport_order_id', 'transportable_id', 'transportable_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transportables');
    }
};
