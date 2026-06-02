<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ComparisonVendor extends Model
{
    use HasFactory;
    protected $fillable = ['comparison_id', 'position', 'name', 'brand', 'model', 'price', 'specs'];

    protected $casts = [
        'specs' => 'array',
        'price' => 'decimal:2',
    ];

    public function comparison()
    {
        return $this->belongsTo(Comparison::class);
    }
}
