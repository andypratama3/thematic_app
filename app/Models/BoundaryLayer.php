<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BoundaryLayer extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'code', 'type', 'region_id', 'geojson',
        'fill_color', 'border_color', 'opacity', 'is_active', 'metadata'
    ];

    protected $casts = [
        'geojson' => 'array',
        'metadata' => 'array',
        'is_active' => 'boolean',
        'opacity' => 'decimal:2',
    ];
}
