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
        Schema::create('refill_audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('refill_request_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            
            // Action details
            $table->string('action'); // created, reviewed, approved, rejected, ready, collected, cancelled
            $table->string('previous_status')->nullable();
            $table->string('new_status')->nullable();
            
            // Additional context
            $table->text('notes')->nullable();
            $table->json('metadata')->nullable(); // Store additional data like inventory changes
            
            // IP and user agent for security
            $table->string('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            
            $table->timestamps();
            
            // Indexes
            $table->index(['refill_request_id', 'created_at']);
            $table->index(['user_id', 'action']);
            $table->index('action');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('refill_audit_logs');
    }
};
