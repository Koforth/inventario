<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReceiptType extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'prefix',
        'current_number',
        'padding',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function nextCorrelative(): string
    {
        $next = (int) $this->current_number + 1;
        $number = str_pad((string) $next, max(1, (int) $this->padding), '0', STR_PAD_LEFT);

        return ($this->prefix ? $this->prefix . '-' : '') . $number;
    }
}

