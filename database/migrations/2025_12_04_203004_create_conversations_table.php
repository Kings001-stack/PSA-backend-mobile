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
        Schema::create('conversations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->string('session_id')->unique();
            $table->string('customer_name')->nullable();
            $table->boolean('requires_escalation')->default(false);
            $table->string('escalation_reason')->nullable();
            $table->timestamp('escalated_at')->nullable();
            $table->foreignId('assigned_to')->nullable()->constrained('users')->onDelete('set null');
            $table->enum('status', ['active', 'resolved', 'escalated'])->default('active');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['tenant_id', 'session_id']);
            $table->index('requires_escalation');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('conversations');
    }
};
