<?php

declare(strict_types=1);

use App\Enums\DocumentLineType;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('document_lines', function (Blueprint $table) {
            $table->id();

            $table->foreignId('document_id')
                ->constrained('documents');

            $table->string('name', 500);
            $table->integer('vat_percentage')->nullable();
            $table->integer('price_exclude_vat')->nullable();
            $table->integer('vat')->nullable();
            $table->integer('price_include_vat')->nullable();
            $table->integer('documentable_id')->nullable();

            $table->unsignedTinyInteger('type')->default(DocumentLineType::Other->value);

            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->nullable()->useCurrentOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('document_lines');
    }
};
