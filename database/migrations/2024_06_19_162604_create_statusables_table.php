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
        Schema::create('statusables', function (Blueprint $table) {
            $table->id();
            $table->integer('status')->unsigned();
            $table->morphs('statusable');

            $table->timestamp('created_at')->useCurrent();

            $table->unique(['status', 'statusable_id', 'statusable_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('statusables');
    }
};
