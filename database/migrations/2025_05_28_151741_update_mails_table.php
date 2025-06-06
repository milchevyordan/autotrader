<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('mails', function (Blueprint $table) {
            $table->dropForeign(['receiver_id']);

            $table->renameColumn('receiver_id', 'receivable_id');

            $table->string('receivable_type')->after('creator_id');

            $table->index(['receivable_type', 'receivable_id'], 'notifications_receivable_index');
        });

        DB::table('mails')->update(['receivable_type' => User::class]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mails', function (Blueprint $table) {
            $table->dropIndex('notifications_receivable_index');
            $table->dropColumn('receivable_type');
            $table->renameColumn('receivable_id', 'receiver_id');

            $table
                ->foreign('receiver_id')
                ->references('id')
                ->on('users');
        });
    }
};
