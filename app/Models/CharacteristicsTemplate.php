<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CharacteristicsTemplate extends Model
{
    use HasFactory;

    protected $table = 'characteristics_templates';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id', 'name', 'category', 'purpose', 'budget',
        'year', 'month', 'created_date', 'created_by', 'specs',
    ];

    protected $casts = [
        'specs' => 'array',
        'budget' => 'decimal:2',
    ];

    public function histories()
    {
        return $this->hasMany(CharacteristicsTemplateHistory::class, 'characteristics_template_id')->orderBy('id');
    }
}
