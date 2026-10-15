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
        Schema::create('registrations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('competition_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('participant_id')->nullable()->constrained()->nullOnDelete();
            $table->string('school_type', 50);
            $table->string('npp');
            $table->string('label')->nullable();
            $table->unsignedTinyInteger('tie_break_order')->nullable();
            $table->timestamps();
            $table->unique(['competition_id', 'school_type', 'npp']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('registrations');
    }
};
