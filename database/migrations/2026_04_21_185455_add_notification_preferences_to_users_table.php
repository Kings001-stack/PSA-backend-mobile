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
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('notify_refill_status')->default(true)->after('updated_by');
            $table->boolean('notify_prescription_reminders')->default(true)->after('notify_refill_status');
            $table->boolean('notify_pharmacy_updates')->default(false)->after('notify_prescription_reminders');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['notify_refill_status', 'notify_prescription_reminders', 'notify_pharmacy_updates']);
        });
    }
};
