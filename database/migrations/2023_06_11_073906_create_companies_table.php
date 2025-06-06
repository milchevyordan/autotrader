<?php

declare(strict_types=1);

use App\Enums\Locale;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('companies', function (Blueprint $table) {
            $table->id();

            $table
                ->foreignId('creator_id')
                ->references('id')
                ->on('users')
                ->onDelete('restrict');

            $table->foreignId('company_group_id')
                ->nullable()
                ->references('id')
                ->on('company_groups')
                ->nullOnDelete();

            $table->foreignId('main_user_id')
                ->nullable()
                ->references('id')
                ->on('users')
                ->nullOnDelete();

            $table->foreignId('billing_contact_id')
                ->nullable()
                ->references('id')
                ->on('users')
                ->nullOnDelete();

            $table->foreignId('logistics_contact_id')
                ->nullable()
                ->references('id')
                ->on('users')
                ->nullOnDelete();

            $table
                ->unsignedTinyInteger('type')
                ->index('company_type_index');

            $table->unsignedTinyInteger('default_currency');
            $table->unsignedTinyInteger('country');
            $table->unsignedTinyInteger('purchase_type')->nullable();
            $table->unsignedTinyInteger('locale')->nullable();

            $table->integer('vat_percentage')->nullable();

            $table->string('name')->index();
            $table->string('number')->nullable();
            $table->string('number_addition')->nullable();
            $table->string('postal_code', 25);
            $table->string('city');
            $table->string('address');
            $table->string('street')->nullable();
            $table->string('address_number')->nullable();
            $table->string('address_number_addition')->nullable();
            $table->string('province')->nullable();
            $table->string('vat_number')->nullable()->unique();
            $table->string('company_number_accounting_system')->nullable();
            $table->string('debtor_number_accounting_system')->nullable();
            $table->string('creditor_number_accounting_system')->nullable();
            $table->string('website', 550)->nullable();
            $table->string('email')->index()->nullable();
            $table->string('phone', 25)->nullable();
            $table->string('iban', 34)->index()->nullable();
            $table->string('swift_or_bic', 11)->nullable();
            $table->string('bank_name')->nullable();
            $table->string('kvk_number')->index()->nullable();
            $table->string('image_path')->nullable();
            $table->string('logistics_times')->nullable();

            $table->text('billing_remarks')->nullable();
            $table->text('logistics_remarks')->nullable();

            $table->date('kvk_expiry_date')->nullable();
            $table->date('vat_expiry_date')->nullable();

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
        Schema::dropIfExists('companies');
    }
};
