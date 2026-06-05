<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('list_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('list_id')->constrained('user_lists')->onDelete('cascade');
            $table->string('item_name');
            $table->decimal('price', 10, 2)->default(0.00);
            $table->integer('quantity')->nullable();
            $table->text('note')->nullable();
            $table->datetime('date')->nullable();
            $table->boolean('checked')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('list_items');
    }
};
