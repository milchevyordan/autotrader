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
        Schema::create('mails', function (Blueprint $table) {
            $table->id();

            $table
                ->foreignId('creator_id')
                ->references('id')
                ->on('users');

            $table
                ->foreignId('receiver_id')
                ->references('id')
                ->on('users');

            $table->morphs('mailable');

            $table->foreignId('mail_content_id')
                ->references('id')
                ->on('mail_contents');

            $table->string('subject');
            $table->string('attached_file_unique_name')->nullable();

            $table->timestamp('created_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mails');
    }
};
