<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LayawayPayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'layaway_id',
        'user_id',
        'amount',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
        ];
    }

    public function layaway()
    {
        return $this->belongsTo(Layaway::class);
    }
}

