<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add missing columns to system_activity_logs table
        Schema::table('system_activity_logs', function (Blueprint $table) {
            if (!Schema::hasColumn('system_activity_logs', 'event_type')) {
                $table->string('event_type')->after('user_id');
            }
            
            if (!Schema::hasColumn('system_activity_logs', 'event_category')) {
                $table->string('event_category')->after('event_type');
            }
            
            if (!Schema::hasColumn('system_activity_logs', 'description')) {
                $table->text('description')->after('event_category');
            }
            
            if (!Schema::hasColumn('system_activity_logs', 'ip_address')) {
                $table->string('ip_address', 45)->nullable()->after('description');
            }
            
            if (!Schema::hasColumn('system_activity_logs', 'user_agent')) {
                $table->text('user_agent')->nullable()->after('ip_address');
            }
            
            if (!Schema::hasColumn('system_activity_logs', 'context')) {
                $table->json('context')->nullable()->after('user_agent');
            }
            
            if (!Schema::hasColumn('system_activity_logs', 'severity')) {
                $table->enum('severity', ['info', 'warning', 'critical'])->default('info')->after('context');
            }
        });

        // Add indexes (Laravel will skip if they already exist)
        try {
            Schema::table('system_activity_logs', function (Blueprint $table) {
                $table->index(['tenant_id', 'created_at']);
            });
        } catch (\Exception $e) {
            // Index already exists, skip
        }

        try {
            Schema::table('system_activity_logs', function (Blueprint $table) {
                $table->index(['user_id', 'event_type']);
            });
        } catch (\Exception $e) {
            // Index already exists, skip
        }

        try {
            Schema::table('system_activity_logs', function (Blueprint $table) {
                $table->index(['event_category', 'severity']);
            });
        } catch (\Exception $e) {
            // Index already exists, skip
        }
    }

    public function down(): void
    {
        Schema::table('system_activity_logs', function (Blueprint $table) {
            $table->dropColumn([
                'event_type',
                'event_category',
                'description',
                'ip_address',
                'user_agent',
                'context',
                'severity'
            ]);
        });
    }
};
