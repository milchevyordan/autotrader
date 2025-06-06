<?php

declare(strict_types=1);

use App\Enums\SalesOrderStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('sales_orders', function (Blueprint $table) {
            $table->id();

            $table
                ->foreignId('creator_id')
                ->references('id')
                ->on('users');

            $table
                ->foreignId('customer_company_id')
                ->nullable()
                ->references('id')
                ->on('companies');

            $table
                ->foreignId('customer_id')
                ->nullable()
                ->references('id')
                ->on('users');

            $table
                ->foreignId('seller_id')
                ->nullable()
                ->references('id')
                ->on('users');

            $table
                ->foreignId('service_level_id')
                ->nullable()
                ->references('id')
                ->on('service_levels');

            $table
                ->unsignedTinyInteger('status')
                ->default(SalesOrderStatus::Concept->value)
                ->index();

            $table
                ->unsignedTinyInteger('type_of_sale')
                ->nullable()
                ->index();

            $table->unsignedTinyInteger('currency')
                ->nullable();

            $table->unsignedTinyInteger('payment_condition')->nullable();
            $table->unsignedTinyInteger('damage')->nullable();

            $table->boolean('is_brutto')->default(false);
            $table->boolean('calculation_on_sales_order')->default(false);
            $table->boolean('transport_included')->default(false);
            $table->boolean('vat_deposit')->default(false);
            $table->boolean('down_payment')->default(false);
            $table->boolean('discount_in_output')->default(false);

            $table->integer('discount')->nullable();
            $table->integer('down_payment_amount')->nullable();
            $table->integer('vat_percentage')->nullable();
            $table->integer('total_vehicles_purchase_price')->nullable();
            $table->integer('total_costs')->nullable();
            $table->integer('total_sales_price_service_items')->nullable();
            $table->integer('total_sales_margin')->nullable();
            $table->integer('total_fee_intermediate_supplier')->nullable();
            $table->integer('total_sales_price_exclude_vat')->nullable();
            $table->integer('total_sales_excl_vat_with_items')->nullable();
            $table->integer('total_registration_fees')->nullable();
            $table->integer('total_vat')->nullable();
            $table->integer('total_bpm')->nullable();
            $table->integer('total_sales_price_include_vat')->nullable();
            $table->integer('total_sales_price')->nullable();
            $table->decimal('currency_rate')->default(1)->nullable();

            $table->string('payment_condition_free_text')->nullable();
            $table->string('reference')->nullable();
            $table->string('delivery_week')->nullable();
            $table->string('number')->nullable();

            $table->text('additional_info_conditions')->nullable();

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
        Schema::dropIfExists('sales_orders');
    }
};
