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
        Schema::table('criteria', function (Blueprint $table) {
            $table->unsignedInteger('min_score')->default(0)->after('school_types');
            $table->unsignedInteger('max_score')->default(100)->after('min_score');
        });

        Schema::table('evaluations', function (Blueprint $table) {
            $table->unsignedInteger('score')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('evaluations', function (Blueprint $table) {
            $table->unsignedTinyInteger('score')->change();
        });

        Schema::table('criteria', function (Blueprint $table) {
            $table->dropColumn(['min_score', 'max_score']);
        });
    }
};
