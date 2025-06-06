<?php

declare(strict_types=1);

use App\Enums\UnitType;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('creator_id')
                ->references('id')
                ->on('users');

            $table->unsignedTinyInteger('unit_type')
                ->default(UnitType::Pcs->value)
                ->index();

            $table->unsignedTinyInteger('type')
                ->index();

            $table->boolean('is_vat')->default(true);
            $table->integer('vat_percentage')->nullable();
            $table->string('shortcode');
            $table->string('description')->nullable();
            $table->integer('purchase_price')->nullable();
            $table->integer('sale_price')->nullable();

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
        Schema::dropIfExists('items');
    }
};
