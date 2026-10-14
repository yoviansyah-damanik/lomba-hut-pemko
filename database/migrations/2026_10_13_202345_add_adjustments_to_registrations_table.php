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
        Schema::table('registrations', function (Blueprint $table) {
            $table->unsignedInteger('score_addition')->default(0)->after('tie_break_order');
            $table->unsignedInteger('score_deduction')->default(0)->after('score_addition');
            $table->text('adjustment_notes')->nullable()->after('score_deduction');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('registrations', function (Blueprint $table) {
            $table->dropColumn(['score_addition', 'score_deduction', 'adjustment_notes']);
        });
    }
};
