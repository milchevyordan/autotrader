<?php

declare(strict_types=1);

use App\Enums\TransportOrderStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('transport_orders', function (Blueprint $table) {
            $table->id();

            $table->foreignId('creator_id')
                ->references('id')
                ->on('users');

            $table
                ->foreignId('transport_company_id')
                ->nullable()
                ->references('id')
                ->on('companies');

            $table
                ->foreignId('transporter_id')
                ->nullable()
                ->references('id')
                ->on('users');

            $table
                ->foreignId('pick_up_company_id')
                ->nullable()
                ->references('id')
                ->on('companies');

            $table
                ->foreignId('pick_up_location_id')
                ->nullable()
                ->references('id')
                ->on('company_addresses');

            $table
                ->foreignId('delivery_company_id')
                ->nullable()
                ->references('id')
                ->on('companies');

            $table
                ->foreignId('delivery_location_id')
                ->nullable()
                ->references('id')
                ->on('company_addresses');

            $table
                ->unsignedTinyInteger('status')
                ->default(TransportOrderStatus::Concept->value)
                ->index();

            $table
                ->unsignedTinyInteger('transport_type')
                ->index();

            $table
                ->unsignedTinyInteger('vehicle_type')
                ->index();

            $table->boolean('transport_company_use')->default(false);
            $table->string('delivery_location_free_text', 500)->nullable();
            $table->string('pick_up_location_free_text', 500)->nullable();
            $table->string('pick_up_notes')->nullable();
            $table->string('delivery_notes')->nullable();
            $table->integer('total_transport_price')
                ->index()
                ->nullable();

            $table->timestamp('pick_up_after_date')->nullable();
            $table->timestamp('deliver_before_date')->nullable();
            $table->timestamp('planned_delivery_date')->nullable();

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
        Schema::dropIfExists('transport_orders');
    }
};
