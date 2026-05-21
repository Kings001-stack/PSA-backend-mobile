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
        Schema::table('system_activity_logs', function (Blueprint $table) {
            // Make action, model_type, model_id, old_values, new_values nullable
            // These are legacy columns that may not always be needed
            $table->string('action')->nullable()->change();
            $table->string('model_type')->nullable()->change();
            $table->unsignedBigInteger('model_id')->nullable()->change();
            $table->json('old_values')->nullable()->change();
            $table->json('new_values')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('system_activity_logs', function (Blueprint $table) {
            // Revert to non-nullable (if needed)
            $table->string('action')->nullable(false)->change();
            $table->string('model_type')->nullable(false)->change();
            $table->unsignedBigInteger('model_id')->nullable(false)->change();
            $table->json('old_values')->nullable(false)->change();
            $table->json('new_values')->nullable(false)->change();
        });
    }
};
