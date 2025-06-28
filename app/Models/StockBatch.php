<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockBatch extends Model
{
    use HasFactory;

    protected $fillable = [
        'medicine_id',
        'purchase_bill_id',
        'batch_number',
        'expiry_date',
        'quantity',
        'purchase_price',
        'ptr',
        'discount_percentage',
    ];

    protected $casts = [
        'expiry_date' => 'date',
    ];

    /**
     * A Stock Batch belongs to a Medicine.
     */
    public function medicine()
    {
        return $this->belongsTo(Medicine::class);
    }

    /**
     * A Stock Batch optionally belongs to a Purchase Bill.
     */
    public function purchaseBill()
    {
        return $this->belongsTo(PurchaseBill::class);
    }
}
