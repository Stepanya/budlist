<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The `lists` table holds the user's named lists, grouped by type
     * (budget | loan | shopping). Mapped from the old `user_lists` table.
     */
    public function up(): void
    {
        Schema::create('lists', function (Blueprint $table) {
            $table->id();
            $table->enum('list_type', ['budget', 'loan', 'shopping'])->index();
            $table->string('title');
            $table->decimal('budget', 10, 2)->nullable();
            $table->unsignedInteger('position')->default(0);
            $table->timestamps();

            $table->index(['list_type', 'position']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lists');
    }
};
