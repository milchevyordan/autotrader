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
        Schema::create('followables', function (Blueprint $table) {
            $table->foreignId('user_id')->constrained('users');
            $table->morphs('followable');

            $table->primary(['user_id', 'followable_id', 'followable_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('followables');
    }
};
