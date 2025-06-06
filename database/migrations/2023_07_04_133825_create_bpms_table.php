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
        Schema::create('bpms', function (Blueprint $table) {
            $table->id();

            $table
                ->unsignedTinyInteger('type_id')
                ->index('type_id');

            $table->unsignedTinyInteger('fuel_type');

            $table->year('year')->index();
            $table->integer('co2_min', false, true)->index();
            $table->integer('co2_max', false, true)->index()->nullable();
            $table->integer('sum_amount', false, true);
            $table->integer('multiply_amount', false, true);
            $table->integer('min_co2_to_add_diesel_tax', false, true)->nullable();
            $table->decimal('additional_diesel_tax', 10)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bpms');
    }
};
