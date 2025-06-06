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
        Schema::disableForeignKeyConstraints();

        Schema::create('users', function (Blueprint $table) {
            $table->id();

            $table
                ->foreignId('company_id')
                ->nullable()
                ->constrained('companies');

            $table
                ->foreignId('creator_id')
                ->nullable()
                ->constrained('users');

            $table->unsignedTinyInteger('gender');
            $table->unsignedTinyInteger('language');

            $table->string('prefix')->nullable();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('full_name');
            $table->string('password');
            $table->string('mobile', 35);
            $table->string('other_phone', 35)->nullable();
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('image_path')->nullable();
            $table->rememberToken();

            $table->date('id_card_expiry_date')->nullable();

            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->nullable()->useCurrentOnUpdate();
        });

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
