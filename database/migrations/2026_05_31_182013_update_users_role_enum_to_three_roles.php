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
        // First, update any 'admin' roles to 'pharmacist'
        DB::table('users')
            ->where('role', 'admin')
            ->update(['role' => 'pharmacist']);

        // Modify the role column to only accept three values
        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['user', 'pharmacist', 'super_admin'])
                ->default('user')
                ->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['user', 'admin', 'pharmacist', 'super_admin'])
                ->default('user')
                ->change();
        });
    }
};
