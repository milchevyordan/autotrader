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
        Schema::create('service_levels', function (Blueprint $table) {
            $table->id();

            $table->foreignId('creator_id')
                ->references('id')
                ->on('users');

            $table->unsignedTinyInteger('type')->index();
            $table->unsignedTinyInteger('payment_condition');
            $table->unsignedTinyInteger('damage');
            $table->unsignedTinyInteger('type_of_sale');

            $table->integer('discount')->nullable();

            $table->boolean('discount_in_output')->default(false);
            $table->boolean('transport_included')->default(false);

            $table->string('payment_condition_free_text')->nullable();
            $table->string('name')->index();
            $table->string('rdw_company_number')->nullable();
            $table->string('login_autotelex')->nullable();
            $table->string('api_japie')->nullable();
            $table->string('bidder_name_autobid')->nullable();
            $table->string('bidder_number_autobid')->nullable();
            $table->string('api_vwe')->nullable();

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
        Schema::dropIfExists('service_levels');
    }
};
