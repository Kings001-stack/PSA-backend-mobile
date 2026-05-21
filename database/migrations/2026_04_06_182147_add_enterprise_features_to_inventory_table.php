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
        Schema::table('inventory', function (Blueprint $table) {
            // Check and add columns that don't exist
            if (!Schema::hasColumn('inventory', 'supplier')) {
                $table->string('supplier')->nullable()->after('batch_number');
            }
            
            if (!Schema::hasColumn('inventory', 'reorder_quantity')) {
                $table->integer('reorder_quantity')->default(50)->after('reorder_level');
            }
            
            if (!Schema::hasColumn('inventory', 'low_stock_alert_sent')) {
                $table->boolean('low_stock_alert_sent')->default(false)->after('reorder_quantity');
            }
            
            if (!Schema::hasColumn('inventory', 'last_restocked_at')) {
                $table->timestamp('last_restocked_at')->nullable()->after('low_stock_alert_sent');
            }
            
            // Pricing columns
            if (!Schema::hasColumn('inventory', 'unit_cost')) {
                $table->decimal('unit_cost', 10, 2)->nullable()->after('last_restocked_at');
            }
            
            if (!Schema::hasColumn('inventory', 'selling_price')) {
                $table->decimal('selling_price', 10, 2)->nullable()->after('unit_cost');
            }
            
            if (!Schema::hasColumn('inventory', 'markup_percentage')) {
                $table->decimal('markup_percentage', 5, 2)->default(30.00)->after('selling_price');
            }
            
            // Status
            if (!Schema::hasColumn('inventory', 'status')) {
                $table->enum('status', ['active', 'discontinued', 'out_of_stock', 'expired'])->default('active')->after('markup_percentage');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inventory', function (Blueprint $table) {
            $columns = [
                'supplier',
                'reorder_quantity',
                'low_stock_alert_sent',
                'last_restocked_at',
                'unit_cost',
                'selling_price',
                'markup_percentage',
                'status'
            ];
            
            foreach ($columns as $column) {
                if (Schema::hasColumn('inventory', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
