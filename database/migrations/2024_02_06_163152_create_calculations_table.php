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
        Schema::create('calculations', function (Blueprint $table) {
            $table->id();
            $table->morphs('vehicleable');

            $table->boolean('is_vat')->default(true);
            $table->boolean('is_locked')->default(false);
            $table->boolean('intermediate')->default(false);

            $table->unsignedTinyInteger('original_currency')->nullable();
            $table->integer('selling_price_supplier')->nullable();
            $table->integer('sell_price_currency_euro')->nullable();
            $table->integer('vat_percentage')->nullable();
            $table->integer('net_purchase_price')->nullable();
            $table->integer('fee_intermediate')->nullable();
            $table->integer('total_purchase_price')->index()->nullable();
            $table->integer('costs_of_damages')->nullable();
            $table->integer('transport_inbound')->nullable();
            $table->integer('transport_outbound')->nullable();
            $table->integer('costs_of_taxation')->nullable();
            $table->integer('recycling_fee')->nullable();
            $table->integer('purchase_cost_items_services')->nullable();
            $table->integer('sale_price_net_including_services_and_products')->nullable();
            $table->integer('sale_price_services_and_products')->nullable();
            $table->integer('discount')->nullable();
            $table->integer('sales_margin')->nullable();
            $table->integer('total_costs_with_fee')->nullable();
            $table->integer('sales_price_net')->nullable();
            $table->integer('vat')->nullable();
            $table->integer('sales_price_incl_vat_or_margin')->nullable();
            $table->integer('rest_bpm_indication')->nullable();
            $table->integer('leges_vat')->nullable();
            $table->integer('sales_price_total')->index()->nullable();
            $table->integer('gross_bpm')->nullable();
            $table->integer('bpm_percent')->nullable();
            $table->integer('bpm')->nullable();
            $table->decimal('currency_exchange_rate')->nullable();

            $table->unique(['vehicleable_id', 'vehicleable_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('calculations');
    }
};
