<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'sku',
        'barcode',
        'nombre',
        'descripcion',
        'image_path',
        'precio',
        'stock',
        'stock_minimo',
        'proveedor',
        'category_id',
        'brand_id',
    ];

    protected function casts(): array
    {
        return [
            'precio' => 'decimal:2',
        ];
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    public function movements()
    {
        return $this->hasMany(Movement::class);
    }

    public function isLowStock(): bool
    {
        return $this->stock <= $this->stock_minimo;
    }
}
