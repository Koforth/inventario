<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    use HasFactory;

    protected $fillable = [
        'number',
        'customer_id',
        'user_id',
        'sale_type',
        'payment_method',
        'receipt_type',
        'receipt_type_id',
        'receipt_number',
        'subtotal',
        'discount_total',
        'total',
        'paid_amount',
        'cash_amount',
        'card_amount',
        'change_amount',
        'credit_balance',
    ];

    protected function casts(): array
    {
        return [
            'subtotal' => 'decimal:2',
            'discount_total' => 'decimal:2',
            'total' => 'decimal:2',
            'paid_amount' => 'decimal:2',
            'cash_amount' => 'decimal:2',
            'card_amount' => 'decimal:2',
            'change_amount' => 'decimal:2',
            'credit_balance' => 'decimal:2',
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
        return $this->hasMany(SaleItem::class);
    }

    public function payments()
    {
        return $this->hasMany(SalePayment::class);
    }

    public function receiptType()
    {
        return $this->belongsTo(ReceiptType::class);
    }
}
