<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'dni',
        'ruc',
        'email',
        'phone',
        'address',
        'contact_name',
    ];

    public function products()
    {
        return $this->belongsToMany(Product::class)->withTimestamps();
    }

    public function purchases()
    {
        return $this->hasMany(Purchase::class);
    }
}
