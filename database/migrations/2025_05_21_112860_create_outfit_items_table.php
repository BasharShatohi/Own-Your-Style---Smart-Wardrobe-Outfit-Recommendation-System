<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('outfit_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('outfit_id');
            $table->unsignedBigInteger('clothing_item_id');
            $table->float('x');
            $table->float('y');
            $table->string('layer')->nullable();
            $table->timestamps();

            $table->foreign('outfit_id')->references('id')->on('outfits')->onDelete('cascade');
            $table->foreign('clothing_item_id')->references('id')->on('clothing_items')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('outfit_items');
    }
};
