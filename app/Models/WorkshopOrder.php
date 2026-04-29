<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkshopOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'number',
        'customer_id',
        'technician_id',
        'device_name',
        'brand',
        'model',
        'serial',
        'issue',
        'observations',
        'service_cost',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'service_cost' => 'decimal:2',
        ];
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function technician()
    {
        return $this->belongsTo(Technician::class);
    }
}

