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
        Schema::create('internal_remark_user', function (Blueprint $table) {
            $table->foreignId('internal_remark_id')
                ->references('id')
                ->on('internal_remarks');

            $table
                ->foreignId('user_id')
                ->references('id')
                ->on('users');

            $table->primary(['internal_remark_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('internal_remarks_user');
    }
};
