<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseBill extends Model
{
    use HasFactory;

    protected $fillable = [
        'supplier_id',
        'bill_date',
        'bill_number',
        'status',
        'total_amount',
        'total_gst_amount',
        'notes',
    ];

    protected $casts = [
        'bill_date' => 'date',
    ];

    /**
     * A Purchase Bill belongs to a Supplier.
     */
    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    /**
     * A Purchase Bill has many Stock Batches (items purchased in this bill).
     */
    public function stockBatches()
    {
        return $this->hasMany(StockBatch::class);
    }
}