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
        Schema::create('workflow_finished_steps', function (Blueprint $table) {
            $table->id();

            $table->foreignId('creator_id')
                ->references('id')
                ->on('users');

            $table->foreignId('workflow_id')
                ->references('id')
                ->on('workflows');

            $table->string('workflow_step_namespace');
            $table->string('additional_value')->nullable();

            $table->timestamp('finished_at')->useCurrent();

            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->nullable()->useCurrentOnUpdate();

            $table->unique(['workflow_id', 'workflow_step_namespace'], 'wf_steps_wf_step_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('workflow_finished_steps');
    }
};
