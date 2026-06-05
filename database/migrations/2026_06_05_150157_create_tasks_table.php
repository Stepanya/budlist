<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The `tasks` table holds the items within a list. Mapped from the old
     * `list_items` table, preserving money fields (amount/quantity/note) and
     * the loan due-date.
     */
    public function up(): void
    {
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('list_id')->constrained('lists')->cascadeOnDelete();
            $table->string('text');
            $table->decimal('amount', 10, 2)->default(0);
            $table->unsignedInteger('quantity')->nullable()->default(1);
            $table->text('note')->nullable();
            $table->date('due_date')->nullable();
            $table->boolean('done')->default(false);
            $table->unsignedInteger('position')->default(0);
            $table->timestamps();

            $table->index(['list_id', 'position']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
