<?php

declare(strict_types=1);

use App\Enums\PurchaseOrderStatus;
use App\Enums\SupplierOrIntermediary;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('purchase_orders', function (Blueprint $table) {
            $table->id();

            $table->foreignId('creator_id')
                ->references('id')
                ->on('users');

            $table
                ->foreignId('supplier_company_id')
                ->references('id')
                ->on('companies');

            $table
                ->foreignId('supplier_id')
                ->nullable()
                ->references('id')
                ->on('users');

            $table
                ->foreignId('intermediary_company_id')
                ->nullable()
                ->references('id')
                ->on('companies');

            $table
                ->foreignId('intermediary_id')
                ->nullable()
                ->references('id')
                ->on('users');

            $table
                ->foreignId('purchaser_id')
                ->references('id')
                ->on('users');

            $table
                ->unsignedTinyInteger('status')
                ->default(PurchaseOrderStatus::Concept->value)
                ->index();

            $table
                ->unsignedTinyInteger('type')
                ->index();

            $table
                ->unsignedTinyInteger('document_from_type')
                ->default(SupplierOrIntermediary::Supplier->value);

            $table->unsignedTinyInteger('papers')->nullable();
            $table->unsignedTinyInteger('payment_condition')->nullable();
            $table->unsignedTinyInteger('currency_po');

            $table->integer('vat_percentage')->nullable();
            $table->integer('down_payment_amount')->nullable();
            $table->integer('vat_deposit_amount')->nullable();
            $table->integer('total_purchase_price')->nullable();
            $table->integer('total_purchase_price_eur')->nullable();
            $table->integer('total_fee_intermediate_supplier')->nullable();
            $table->integer('total_purchase_price_exclude_vat')->nullable();
            $table->integer('total_transport')->nullable();
            $table->integer('total_vat')->nullable();
            $table->integer('total_bpm')->nullable();
            $table->integer('total_purchase_price_include_vat')->nullable();
            $table->integer('total_payment_amount')->nullable();

            $table->decimal('currency_rate')->default(1)->nullable();

            $table->boolean('transport_included')->default(true);
            $table->boolean('vat_deposit')->default(false);
            $table->boolean('down_payment')->default(false);

            $table->string('payment_condition_free_text')->nullable();
            $table->text('sales_restriction')->nullable();
            $table->text('contact_notes')->nullable();

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
        Schema::dropIfExists('purchase_orders');
    }
};
