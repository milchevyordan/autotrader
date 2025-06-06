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
        Schema::create('company_service_level', function (Blueprint $table) {
            $table->foreignId('service_level_id')
                ->references('id')
                ->on('service_levels');

            $table->foreignId('company_id')
                ->references('id')
                ->on('companies');

            $table->primary(['service_level_id', 'company_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('company_service_level');
    }
};
