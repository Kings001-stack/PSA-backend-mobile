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
            // Add prescription tracking only if it doesn't exist
            if (!Schema::hasColumn('refill_requests', 'prescription_id')) {
                $table->foreignId('prescription_id')->nullable()->after('medication_id')->constrained()->onDelete('set null');
            }
        });

        // Drop and recreate status column with new enum values
        if (Schema::hasColumn('refill_requests', 'status')) {
            Schema::table('refill_requests', function (Blueprint $table) {
                $table->dropColumn('status');
            });
        }

        Schema::table('refill_requests', function (Blueprint $table) {
            $table->enum('status', [
                'pending',
                'under_review',
                'approved',
                'rejected',
                'ready_for_pickup',
                'collected',
                'cancelled'
            ])->default('pending')->after('notes');
        });

        Schema::table('refill_requests', function (Blueprint $table) {
            // Add columns only if they don't exist
            if (!Schema::hasColumn('refill_requests', 'reviewed_by')) {
                $table->foreignId('reviewed_by')->nullable()->after('admin_notes')->constrained('users')->onDelete('set null');
            }
            
            if (!Schema::hasColumn('refill_requests', 'rejection_reason')) {
                $table->text('rejection_reason')->nullable()->after('admin_notes');
            }
            
            if (!Schema::hasColumn('refill_requests', 'ready_at')) {
                $table->timestamp('ready_at')->nullable()->after('reviewed_at');
            }
            
            if (!Schema::hasColumn('refill_requests', 'collected_at')) {
                $table->timestamp('collected_at')->nullable()->after('ready_at');
            }
            
            if (!Schema::hasColumn('refill_requests', 'collected_by')) {
                $table->foreignId('collected_by')->nullable()->after('collected_at')->constrained('users')->onDelete('set null');
            }
            
            if (!Schema::hasColumn('refill_requests', 'preferred_pickup_time')) {
                $table->timestamp('preferred_pickup_time')->nullable()->after('notes');
            }
            
            if (!Schema::hasColumn('refill_requests', 'is_urgent')) {
                $table->boolean('is_urgent')->default(false)->after('quantity');
            }
            
            // Indexes for performance
            $table->index(['status', 'created_at']);
            $table->index(['user_id', 'status']);
            $table->index('reviewed_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('refill_requests', function (Blueprint $table) {
            $table->dropForeign(['prescription_id']);
            $table->dropForeign(['reviewed_by']);
            $table->dropForeign(['collected_by']);
            
            $table->dropColumn([
                'prescription_id',
                'reviewed_by',
                'rejection_reason',
                'ready_at',
                'collected_at',
                'collected_by',
                'preferred_pickup_time',
                'is_urgent'
            ]);
        });
    }
};
