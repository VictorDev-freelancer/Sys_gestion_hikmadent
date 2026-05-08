<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CatalogItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'category',
        'name',
        'price_regular',
        'price_student',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'price_regular' => 'decimal:2',
            'price_student' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }
}
