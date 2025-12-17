<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Dataset extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'name', 'slug', 'description', 'type',
        'total_records', 'total_parameters', 'import_status',
        'import_file_path', 'import_error', 'is_active', 'imported_at'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'imported_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function transactions()
    {
        return $this->hasMany(FertilizerTransaction::class);
    }
}
