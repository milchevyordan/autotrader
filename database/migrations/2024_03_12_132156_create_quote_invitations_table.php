<?php

declare(strict_types=1);

use App\Enums\QuoteInvitationStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('quote_invitations', function (Blueprint $table) {
            $table->id();

            $table
                ->foreignId('creator_id')
                ->references('id')
                ->on('users');

            $table
                ->foreignId('quote_id')
                ->references('id')
                ->on('quotes');

            $table
                ->foreignId('customer_id')
                ->references('id')
                ->on('users');

            $table
                ->unsignedTinyInteger('status')
                ->default(QuoteInvitationStatus::Concept->value)
                ->index();

            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->nullable()->useCurrentOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quote_invitations');
    }
};
