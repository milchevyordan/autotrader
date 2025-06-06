<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('vehicle_transfer_tokens', function (Blueprint $table) {
            $table->foreignId('vehicle_id')->primary()->constrained('vehicles')->cascadeOnDelete();
            $table->string('token', 64);
            $table->timestamp('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vehicle_transfer_tokens');
    }
};
