<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Layaway extends Model
{
    use HasFactory;

    protected $fillable = [
        'number',
        'customer_id',
        'user_id',
        'pickup_at',
        'subtotal',
        'discount_total',
        'total',
        'initial_payment',
        'balance',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'pickup_at' => 'datetime',
            'subtotal' => 'decimal:2',
            'discount_total' => 'decimal:2',
            'total' => 'decimal:2',
            'initial_payment' => 'decimal:2',
            'balance' => 'decimal:2',
        ];
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(LayawayItem::class);
    }

    public function payments()
    {
        return $this->hasMany(LayawayPayment::class);
    }
}

