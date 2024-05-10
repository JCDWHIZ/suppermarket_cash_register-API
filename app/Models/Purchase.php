<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Purchase extends Model
{
    use HasFactory;

    protected $fillable = ['total_price', 'transaction_date', 'payment_method', 'customer_id', 'reference_number'];

    public function customer()
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function PurchaseDetails()
    {
        return $this->hasMany(PurchaseDetails::class, 'purchase_id');
    }
}