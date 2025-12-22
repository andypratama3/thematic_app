<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FertilizerTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'dataset_id', 'farmer_id', 'transaction_code', 'transaction_number',
        'nik', 'farmer_name', 'transaction_date', 'redemption_date',
        'urea', 'npk', 'sp36', 'za', 'npk_formula', 'organic', 'organic_liquid',
        'urea_color', 'npk_color', 'sp36_color', 'za_color',
        'npk_formula_color', 'organic_color', 'organic_liquid_color',
        'notes', 'proof_url', 'address', 'village', 'district',
        'regency', 'province', 'latitude', 'longitude'
    ];

    protected $casts = [
        'transaction_date' => 'date',
        'redemption_date' => 'date',
        'urea' => 'decimal:2',
        'npk' => 'decimal:2',
        'sp36' => 'decimal:2',
        'za' => 'decimal:2',
        'npk_formula' => 'decimal:2',
        'organic' => 'decimal:2',
        'organic_liquid' => 'decimal:2',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
    ];

    public function dataset()
    {
        return $this->belongsTo(Dataset::class);
    }

    public function farmer()
    {
        return $this->belongsTo(Farmer::class);
    }

    public function getTotalFertilizerAttribute()
    {
        return $this->urea + $this->npk + $this->sp36 + $this->za +
               $this->npk_formula + $this->organic + $this->organic_liquid;
    }
}

