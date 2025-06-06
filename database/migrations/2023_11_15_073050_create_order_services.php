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
        Schema::create('order_services', function (Blueprint $table) {
            $table->id();

            $table->morphs('orderable'); // This will add 'orderable_id' and 'orderable_type' columns
            $table->string('name');
            $table->integer('purchase_price')->nullable();
            $table->integer('sale_price')->nullable();
            $table->boolean('in_output')->default(false);
            $table->boolean('is_service_level')->default(false);

            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->nullable()->useCurrentOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_services');
    }
};
