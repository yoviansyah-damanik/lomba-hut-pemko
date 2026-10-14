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
        Schema::create('criterion_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('criterion_id')->constrained('criteria')->cascadeOnDelete();
            $table->string('name');
            $table->integer('min_score')->default(0);
            $table->integer('max_score')->default(100);
            $table->integer('order')->default(0);
            $table->timestamps();
        });

        Schema::create('evaluation_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('evaluation_id')->constrained('evaluations')->cascadeOnDelete();
            $table->foreignUuid('criterion_item_id')->constrained('criterion_items')->cascadeOnDelete();
            $table->integer('score')->default(0);
            $table->timestamps();

            $table->unique(['evaluation_id', 'criterion_item_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('evaluation_items');
        Schema::dropIfExists('criterion_items');
    }
};
