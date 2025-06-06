<?php

declare(strict_types=1);

use App\Enums\DocumentStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('documents', function (Blueprint $table) {
            $table->id();

            $table->foreignId('creator_id')
                ->references('id')
                ->on('users');

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
                ->unsignedTinyInteger('status')
                ->default(DocumentStatus::Concept->value)
                ->index();

            $table
                ->unsignedTinyInteger('documentable_type')
                ->index();

            $table->unsignedTinyInteger('payment_condition');

            $table->string('number')->nullable();
            $table->string('payment_condition_free_text')->nullable();
            $table->text('notes')->nullable();

            $table->integer('total_price_exclude_vat')->nullable();
            $table->integer('total_vat')->nullable();
            $table->integer('total_price_include_vat')->nullable();

            $table->date('date')->nullable();
            $table->date('paid_at')->nullable();

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
        Schema::dropIfExists('documents');
    }
};
