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
        Schema::create('service_vehicles', function (Blueprint $table) {
            $table->id();

            $table->foreignId('creator_id')
                ->references('id')
                ->on('users');

            $table->foreignId('make_id')
                ->references('id')
                ->on('makes');

            $table->foreignId('vehicle_model_id')
                ->references('id')
                ->on('vehicle_models');

            $table->foreignId('variant_id')
                ->nullable()
                ->references('id')
                ->on('variants')
                ->nullOnDelete();

            $table->unsignedTinyInteger('current_registration');
            $table->unsignedTinyInteger('vehicle_type');
            $table->unsignedTinyInteger('co2_type');

            $table->integer('co2_value', false, true);
            $table->integer('kilometers');

            $table->string('nl_registration_number')->nullable();
            $table->string('vin', 17)->index();

            $table->date('first_registration_date');

            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->nullable()->useCurrentOnUpdate();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_vehicles');
    }
};
