<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Farmer extends Model
{
    use HasFactory;

    protected $table = 'farmers';

    /**
     * Mass assignable
     */
    protected $fillable = [
        'dataset_id',
        'nik',
        'name',
        'address',
        'village',
        'district',
        'regency',
        'province',
        'latitude',
        'longitude',
    ];

    /**
     * Cast attributes
     */
    protected $casts = [
        'latitude'  => 'float',
        'longitude' => 'float',
    ];

    /*
    |--------------------------------------------------------------------------
    | RELATIONS
    |--------------------------------------------------------------------------
    */

    /**
     * Farmer belongs to Dataset
     */
    public function dataset()
    {
        return $this->belongsTo(Dataset::class);
    }

    /**
     * Farmer has many Fertilizer Transactions
     */
    public function transactions()
    {
        return $this->hasMany(FertilizerTransaction::class, 'nik', 'nik');
    }
}
