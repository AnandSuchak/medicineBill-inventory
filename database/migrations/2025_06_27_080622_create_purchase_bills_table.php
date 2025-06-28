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
          Schema::create('purchase_bills', function (Blueprint $table) {
            $table->id();
            $table->foreignId('supplier_id')->constrained()->cascadeOnDelete(); // Link to Supplier
            $table->date('bill_date');
            $table->string('bill_number')->nullable()->unique(); // Optional, but good for reference
            $table->string('status')->default('Generated'); // e.g., Generated, Received, Paid, Unpaid
            $table->decimal('total_amount', 10, 2)->default(0.00); // Total amount of the bill (calculated)
            $table->decimal('total_gst_amount', 10, 2)->default(0.00); // Total GST amount (calculated)
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_bills');
    }
};
