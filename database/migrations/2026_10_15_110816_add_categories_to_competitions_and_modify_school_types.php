<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('competitions', function (Blueprint $table) {
            $table->json('categories')->nullable()->after('name');
        });

        // Ubah school_type dari ENUM ke VARCHAR(50) pada tabel terkait
        if (in_array(DB::getDriverName(), ['mysql', 'mariadb'], true)) {
            DB::statement("ALTER TABLE participants MODIFY COLUMN school_type VARCHAR(50) NOT NULL");
            DB::statement("ALTER TABLE registrations MODIFY COLUMN school_type VARCHAR(50) NOT NULL");
            DB::statement("ALTER TABLE criterion_user MODIFY COLUMN school_type VARCHAR(50) NOT NULL");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('competitions', function (Blueprint $table) {
            $table->dropColumn('categories');
        });

        if (in_array(DB::getDriverName(), ['mysql', 'mariadb'], true)) {
            DB::statement("ALTER TABLE participants MODIFY COLUMN school_type ENUM('SD', 'SMP', 'SMA', 'Umum') NOT NULL");
            DB::statement("ALTER TABLE registrations MODIFY COLUMN school_type ENUM('SD', 'SMP', 'SMA', 'Umum') NOT NULL");
            DB::statement("ALTER TABLE criterion_user MODIFY COLUMN school_type ENUM('SD', 'SMP', 'SMA', 'Umum') NOT NULL");
        }
    }
};
