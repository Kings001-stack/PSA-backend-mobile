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
        Schema::create('prescriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('medication_id')->constrained()->onDelete('cascade');
            
            // Prescription details
            $table->string('prescription_number')->unique();
            $table->string('prescriber_name');
            $table->string('prescriber_license')->nullable();
            $table->date('prescribed_date');
            $table->date('expiration_date');
            
            // Refill information
            $table->integer('refills_allowed')->default(0);
            $table->integer('refills_remaining')->default(0);
            
            // Dosage information
            $table->string('dosage');
            $table->string('frequency');
            $table->text('instructions')->nullable();
            
            // Controlled substance tracking
            $table->boolean('is_controlled')->default(false);
            $table->string('controlled_schedule')->nullable(); // Schedule II, III, IV, V
            
            // Document storage
            $table->string('document_path')->nullable();
            
            // Status
            $table->enum('status', ['active', 'expired', 'cancelled'])->default('active');
            
            // Verification
            $table->boolean('is_verified')->default(false);
            $table->foreignId('verified_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('verified_at')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index(['user_id', 'status']);
            $table->index(['medication_id', 'status']);
            $table->index('prescription_number');
            $table->index('expiration_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('prescriptions');
    }
};
