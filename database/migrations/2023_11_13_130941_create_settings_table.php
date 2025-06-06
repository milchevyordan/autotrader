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
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')
                ->unique()
                ->constrained('companies')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->integer('costs_of_damages')->nullable();
            $table->integer('transport_inbound')->nullable();
            $table->integer('transport_outbound')->nullable();
            $table->integer('costs_of_taxation')->nullable();
            $table->integer('recycling_fee')->nullable();
            $table->integer('sales_margin')->nullable();
            $table->integer('leges_vat')->nullable();

            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->nullable()->useCurrentOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
