<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (in_array(DB::getDriverName(), ['mysql', 'mariadb'], true)) {
            DB::statement("ALTER TABLE participants MODIFY COLUMN school_type ENUM('SD', 'SMP', 'SMA', 'Umum') NOT NULL");
            DB::statement("ALTER TABLE criterion_user MODIFY COLUMN school_type ENUM('SD', 'SMP', 'SMA', 'Umum') NOT NULL");
            DB::statement("ALTER TABLE registrations MODIFY COLUMN school_type ENUM('SD', 'SMP', 'SMA', 'Umum') NOT NULL");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (in_array(DB::getDriverName(), ['mysql', 'mariadb'], true)) {
            DB::statement("ALTER TABLE participants MODIFY COLUMN school_type ENUM('SD', 'SMP', 'SMA') NOT NULL");
            DB::statement("ALTER TABLE criterion_user MODIFY COLUMN school_type ENUM('SD', 'SMP', 'SMA') NOT NULL");
            DB::statement("ALTER TABLE registrations MODIFY COLUMN school_type ENUM('SD', 'SMP', 'SMA') NOT NULL");
        }
    }
};
