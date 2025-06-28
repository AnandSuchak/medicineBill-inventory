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
        Schema::create('stock_batches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('medicine_id')->constrained()->cascadeOnDelete(); // Link to Medicine
            $table->foreignId('purchase_bill_id')->nullable()->constrained()->nullOnDelete(); // Link to PurchaseBill
            $table->string('batch_number');
            $table->date('expiry_date');
            $table->integer('quantity'); // Current available quantity in this batch
            $table->decimal('purchase_price', 10, 2); // Price per unit/pack at purchase
            $table->decimal('ptr', 10, 2); // Price to Retail
            $table->decimal('discount_percentage', 5, 2)->default(0.00); // Discount received from supplier for this specific batch
            $table->timestamps();

            // Unique constraint to ensure same medicine + batch number is unique per purchase bill (or unique overall if purchase_bill_id is null)
            // For simplicity, let's make batch_number unique per medicine for now. If a medicine can have the same batch number from different suppliers, we'd need to adjust.
            $table->unique(['medicine_id', 'batch_number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_batches');
    }
};
