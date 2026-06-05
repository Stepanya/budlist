<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Scope lists (and, through them, tasks) to a user. Nullable so the legacy
     * imported rows remain valid; the first account to verify claims them.
     */
    public function up(): void
    {
        Schema::table('lists', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->after('id')
                ->constrained('users')->nullOnDelete();
            $table->index(['user_id', 'list_type', 'position']);
        });
    }

    public function down(): void
    {
        Schema::table('lists', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'list_type', 'position']);
            $table->dropConstrainedForeignId('user_id');
        });
    }
};
