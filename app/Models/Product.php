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
        'purchase_price',
        'sale_price_1',
        'sale_price_2',
        'sale_price_3',
        'stock',
        'stock_minimo',
        'proveedor',
        'category_id',
        'brand_id',
        'presentation_id',
        'taxable',
        'perishable',
        'inventoryable',
        'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'precio' => 'decimal:2',
            'purchase_price' => 'decimal:2',
            'sale_price_1' => 'decimal:2',
            'sale_price_2' => 'decimal:2',
            'sale_price_3' => 'decimal:2',
            'taxable' => 'boolean',
            'perishable' => 'boolean',
            'inventoryable' => 'boolean',
            'expires_at' => 'date',
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

    public function presentation()
    {
        return $this->belongsTo(Presentation::class);
    }

    public function movements()
    {
        return $this->hasMany(Movement::class);
    }

    public function images()
    {
        return $this->hasMany(ProductImage::class)->orderBy('sort_order')->orderBy('id');
    }

    public function suppliers()
    {
        return $this->belongsToMany(Supplier::class)->withTimestamps();
    }

    public function supplierNames(): string
    {
        $suppliers = $this->relationLoaded('suppliers') ? $this->suppliers : $this->suppliers()->get();

        return $suppliers->pluck('name')->join(', ') ?: ($this->proveedor ?: 'Sin proveedor');
    }

    public function isLowStock(): bool
    {
        return $this->stock <= $this->stock_minimo;
    }
}
