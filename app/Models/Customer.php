<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'customer_type',
        'company_name',
        'dni',
        'ruc',
        'phone',
        'email',
        'business_line',
        'credit_limit',
        'address',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'credit_limit' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function typeLabel(): string
    {
        return $this->customer_type === 'empresa' ? 'Empresa' : 'Cliente';
    }

    public function sales()
    {
        return $this->hasMany(Sale::class);
    }

    public function layaways()
    {
        return $this->hasMany(Layaway::class);
    }
}
