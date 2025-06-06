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
        Schema::create('work_order_tasks', function (Blueprint $table) {
            $table->id();

            $table->foreignId('creator_id')
                ->references('id')
                ->on('users');

            $table->foreignId('assigned_to_id')
                ->nullable()
                ->references('id')
                ->on('users');

            $table->foreignId('work_order_id')
                ->references('id')
                ->on('work_orders');

            $table->unsignedTinyInteger('type');
            $table->unsignedTinyInteger('status');

            $table->string('name');
            $table->string('description')->nullable();

            $table->unsignedInteger('estimated_price');
            $table->unsignedInteger('actual_price')->nullable();

            $table->date('planned_date')->nullable();

            $table->timestamp('completed_at')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->nullable()->useCurrentOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('work_order_tasks');
    }
};
