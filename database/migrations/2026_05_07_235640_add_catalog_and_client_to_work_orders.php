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
        Schema::table('work_orders', function (Blueprint $table) {
            $table->enum('client_type', ['regular', 'student'])->default('regular')->after('client_id');
            $table->foreignId('catalog_item_id')->nullable()->after('patient_age')->constrained('catalog_items')->nullOnDelete();
            $table->decimal('unit_price', 10, 2)->default(0)->after('catalog_item_id');
            $table->decimal('total_price', 10, 2)->default(0)->after('quantity');
            $table->string('prosthetic_type')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('work_orders', function (Blueprint $table) {
            $table->dropForeign(['catalog_item_id']);
            $table->dropColumn(['client_type', 'catalog_item_id', 'unit_price', 'total_price']);
            $table->string('prosthetic_type')->nullable(false)->change();
        });
    }
};
