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
         Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('shop_name')->unique(); // Customer shop name - Unique identifier
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->text('address')->nullable();
            $table->string('gst_number')->nullable()->unique(); // GST Number - Can be null
            $table->string('pan_number')->nullable()->unique(); // PAN Number - Can be null
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
