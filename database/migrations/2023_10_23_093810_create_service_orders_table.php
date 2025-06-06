<?php

declare(strict_types=1);

use App\Enums\ServiceOrderStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('service_orders', function (Blueprint $table) {
            $table->id();

            $table->foreignId('creator_id')
                ->references('id')
                ->on('users');

            $table
                ->foreignId('service_vehicle_id')
                ->nullable()
                ->references('id')
                ->on('service_vehicles');

            $table
                ->foreignId('customer_company_id')
                ->references('id')
                ->on('companies');

            $table
                ->foreignId('customer_id')
                ->nullable()
                ->references('id')
                ->on('users');

            $table
                ->foreignId('seller_id')
                ->references('id')
                ->on('users');

            $table
                ->foreignId('service_level_id')
                ->nullable()
                ->references('id')
                ->on('service_levels');

            $table
                ->unsignedTinyInteger('status')
                ->default(ServiceOrderStatus::Concept->value)
                ->index();

            $table->unsignedTinyInteger('type_of_service');
            $table->unsignedTinyInteger('payment_condition');

            $table->string('payment_condition_free_text')->nullable();

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
        Schema::dropIfExists('service_orders');
    }
};
