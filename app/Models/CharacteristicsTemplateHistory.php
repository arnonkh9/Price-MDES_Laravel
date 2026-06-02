<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CharacteristicsTemplateHistory extends Model
{
    protected $table = 'characteristics_template_histories';
    protected $fillable = ['characteristics_template_id', 'date', 'user', 'action', 'detail'];

    public function characteristicsTemplate()
    {
        return $this->belongsTo(CharacteristicsTemplate::class, 'characteristics_template_id');
    }
}
