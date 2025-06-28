<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
     use HasFactory;

    protected $fillable = [
        'shop_name',
        'email',
        'phone',
        'address',
        'gst_number', // Updated from previous `gst_pan`
        'pan_number', // New field
    ];
}
