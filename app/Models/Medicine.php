<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Medicine extends Model
{
     use HasFactory;

    protected $fillable = [
        'name',
        'hsn_code',
        'description',
        'unit',
        'gst_rate',
        'pack',
        'company_name',
    ];

    /**
     * A medicine can have many stock batches.
     */
    public function stockBatches()
    {
        return $this->hasMany(StockBatch::class);
    }
    
}
