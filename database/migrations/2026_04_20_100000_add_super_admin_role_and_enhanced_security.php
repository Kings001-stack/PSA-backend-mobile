<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Update users table role enum to include super_admin
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('super_admin', 'admin', 'pharmacist', 'user') DEFAULT 'user'");
        
        // Add enhanced security and tracking fields to users table
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'account_status')) {
                $table->enum('account_status', ['active', 'suspended', 'deleted'])->default('active')->after('role');
            }
            if (!Schema::hasColumn('users', 'last_login_at')) {
                $table->timestamp('last_login_at')->nullable()->after('avatar_path');
            }
            if (!Schema::hasColumn('users', 'last_login_ip')) {
                $table->string('last_login_ip', 45)->nullable()->after('last_login_at');
            }
            if (!Schema::hasColumn('users', 'mfa_enabled')) {
                $table->boolean('mfa_enabled')->default(false)->after('last_login_ip');
            }
            if (!Schema::hasColumn('users', 'mfa_secret')) {
                $table->string('mfa_secret')->nullable()->after('mfa_enabled');
            }
            if (!Schema::hasColumn('users', 'created_by')) {
                $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null')->after('mfa_secret');
            }
            if (!Schema::hasColumn('users', 'updated_by')) {
                $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null')->after('created_by');
            }
            
            if (!Schema::hasColumn('users', 'account_status')) {
                $table->index('account_status');
            }
            if (!Schema::hasColumn('users', 'last_login_at')) {
                $table->index('last_login_at');
            }
        });

        // Create permissions table
        if (!Schema::hasTable('permissions')) {
            Schema::create('permissions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
                $table->enum('role', ['super_admin', 'admin', 'pharmacist', 'user']);
                $table->string('resource'); // e.g., 'users', 'admins', 'pharmacists', 'inventory'
                $table->string('action'); // e.g., 'create', 'read', 'update', 'delete', 'suspend'
                $table->boolean('allowed')->default(true);
                $table->json('conditions')->nullable(); // Additional permission conditions
                $table->timestamps();
                
                $table->unique(['tenant_id', 'role', 'resource', 'action']);
                $table->index(['role', 'resource', 'action']);
            });
        }

        // Create system_activity_logs table for detailed activity tracking
        if (!Schema::hasTable('system_activity_logs')) {
            Schema::create('system_activity_logs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
                $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
                $table->string('event_type'); // login, logout, create_user, suspend_user, etc.
                $table->string('event_category'); // auth, user_management, security, system
                $table->text('description');
                $table->string('ip_address', 45)->nullable();
                $table->text('user_agent')->nullable();
                $table->json('context')->nullable(); // Additional event context
                $table->enum('severity', ['info', 'warning', 'critical'])->default('info');
                $table->timestamps();
                
                $table->index(['tenant_id', 'created_at']);
                $table->index(['user_id', 'event_type']);
                $table->index(['event_category', 'severity']);
            });
        }

        // Create notifications table
        if (!Schema::hasTable('notifications')) {
            Schema::create('notifications', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
                $table->string('type'); // security_alert, user_created, failed_login, etc.
                $table->string('title');
                $table->text('message');
                $table->enum('severity', ['info', 'warning', 'critical'])->default('info');
                $table->timestamp('read_at')->nullable();
                $table->json('metadata')->nullable();
                $table->timestamps();
                
                $table->index(['user_id', 'read_at']);
                $table->index(['tenant_id', 'created_at']);
                $table->index('type');
            });
        }

        // Create rate_limits table for tracking API rate limiting
        if (!Schema::hasTable('rate_limits')) {
            Schema::create('rate_limits', function (Blueprint $table) {
                $table->id();
                $table->string('key'); // IP address or user ID
                $table->string('endpoint'); // API endpoint
                $table->integer('attempts')->default(0);
                $table->timestamp('reset_at');
                $table->timestamp('blocked_until')->nullable();
                $table->timestamps();
                
                $table->unique(['key', 'endpoint']);
                $table->index('reset_at');
                $table->index('blocked_until');
            });
        }

        // Enhance sessions table for better session management
        Schema::table('sessions', function (Blueprint $table) {
            $table->string('device_type', 50)->nullable()->after('user_agent');
            $table->string('device_name')->nullable()->after('device_type');
            $table->timestamp('created_at')->nullable()->after('last_activity');
            
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::table('sessions', function (Blueprint $table) {
            $table->dropColumn(['device_type', 'device_name', 'created_at']);
        });
        
        Schema::dropIfExists('rate_limits');
        Schema::dropIfExists('notifications');
        Schema::dropIfExists('system_activity_logs');
        Schema::dropIfExists('permissions');
        
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['created_by']);
            $table->dropForeign(['updated_by']);
            $table->dropColumn([
                'account_status',
                'last_login_at',
                'last_login_ip',
                'mfa_enabled',
                'mfa_secret',
                'created_by',
                'updated_by'
            ]);
        });
        
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('admin', 'pharmacist', 'user') DEFAULT 'user'");
    }
};
