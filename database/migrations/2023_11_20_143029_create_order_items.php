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
        Schema::create('order_items', function (Blueprint $table) {
            $table
                ->foreignId('id')
                ->references('id')
                ->on('items');

            $table->morphs('orderable'); // This will add 'orderable_id' and 'orderable_type' columns
            $table->integer('sale_price')->nullable();
            $table->boolean('in_output')->default(false);

            $table->primary(['id', 'orderable_id', 'orderable_type']);

            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->nullable()->useCurrentOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
