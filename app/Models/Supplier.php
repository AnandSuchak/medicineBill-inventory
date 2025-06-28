<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'gst_number',
        'address',
        'dln',
    ];

      /**
     * A supplier can have many purchase bills.
     */
    public function purchaseBills()
    {
        return $this->hasMany(PurchaseBill::class);
    }
    
}
