<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // First, update any existing 'staff' users to 'user' role
        DB::table('users')->where('role', 'staff')->update(['role' => 'pharmacist']);
        
        // Now modify the enum to replace 'staff' with 'user'
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('admin', 'pharmacist', 'user') NOT NULL DEFAULT 'user'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // First, update any 'user' role back to 'pharmacist' (temporary)
        DB::table('users')->where('role', 'user')->update(['role' => 'pharmacist']);
        
        // Revert back to the original enum values
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('admin', 'pharmacist', 'staff') NOT NULL DEFAULT 'staff'");
        
        // Restore the original 'staff' role
        DB::table('users')->where('role', 'pharmacist')->whereNotIn('id', function($query) {
            $query->select('id')->from('users')->where('role', 'admin');
        })->update(['role' => 'staff']);
    }
};
