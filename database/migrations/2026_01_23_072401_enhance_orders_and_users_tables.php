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
        // Add payment_status to orders table
        Schema::table('orders', function (Blueprint $table) {
            $table->enum('payment_status', ['pending', 'paid', 'failed', 'refunded'])->default('pending')->after('status');
            $table->string('payment_method')->default('cash_on_delivery')->after('payment_status');
            $table->text('notes')->nullable()->after('shipping_address');
        });

        // Add customer tracking fields to users table
        Schema::table('users', function (Blueprint $table) {
            $table->enum('status', ['active', 'inactive', 'suspended'])->default('active')->after('role');
            $table->integer('total_orders')->default(0)->after('status');
            $table->decimal('total_spent', 10, 2)->default(0)->after('total_orders');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['payment_status', 'payment_method', 'notes']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['status', 'total_orders', 'total_spent']);
        });
    }
};
