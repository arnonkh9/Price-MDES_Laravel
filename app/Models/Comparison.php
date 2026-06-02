<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Comparison extends Model
{
    use HasFactory;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id', 'name', 'category', 'year', 'month',
        'characteristics_template_id', 'notes', 'status', 'created_date', 'created_by',
    ];

    public function vendors()
    {
        return $this->hasMany(ComparisonVendor::class)->orderBy('position');
    }

    public function characteristicsTemplate()
    {
        return $this->belongsTo(CharacteristicsTemplate::class, 'characteristics_template_id');
    }

    // Backward compatibility alias
    public function specTemplate()
    {
        return $this->characteristicsTemplate();
    }
}
