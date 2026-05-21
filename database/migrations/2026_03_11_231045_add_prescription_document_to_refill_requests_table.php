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
        Schema::table('refill_requests', function (Blueprint $table) {
            $table->string('prescription_document_path')->nullable()->after('notes');
            $table->timestamp('viewed_at')->nullable()->after('reviewed_at');
            $table->unsignedBigInteger('viewed_by')->nullable()->after('viewed_at');
            
            $table->foreign('viewed_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('refill_requests', function (Blueprint $table) {
            $table->dropForeign(['viewed_by']);
            $table->dropColumn(['prescription_document_path', 'viewed_at', 'viewed_by']);
        });
    }
};
