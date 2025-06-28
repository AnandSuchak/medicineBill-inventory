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
        Schema::create('medicines', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // This is the missing 'name' column!
            $table->string('hsn_code')->nullable();
            $table->text('description')->nullable();
            $table->string('unit'); // e.g., 'Tablet', 'Bottle', 'Box'
            $table->decimal('gst_rate', 5, 2); // e.g., 5.00, 12.00, 18.00
            $table->string('pack'); // e.g., 'Strip of 10', 'Bottle of 100'
            $table->string('company_name')->nullable(); // Manufacturing company
            $table->timestamps();

            // Unique constraint to avoid duplicate medicine entries with the same name, pack, and company
            // If you intend to have same medicine name for different packs/companies, remove this unique constraint.
            // For now, let's keep it flexible, but this is a common choice for product masters.
            // $table->unique(['name', 'pack', 'company_name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('medicines');
    }
};
