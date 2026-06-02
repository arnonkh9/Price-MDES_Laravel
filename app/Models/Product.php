<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id', 'category', 'brand', 'model', 'price',
        'price_unit', 'price_date', 'specs',
        'price_source', 'price_url',
    ];

    protected $casts = [
        'specs' => 'array',
        'price' => 'decimal:2',
    ];

    public function histories()
    {
        return $this->hasMany(ProductEditHistory::class)->orderBy('id');
    }

    public function categoryModel()
    {
        return $this->belongsTo(Category::class, 'category', 'slug');
    }

    public function attachments()
    {
        return $this->hasMany(ProductAttachment::class)->orderBy('uploaded_at', 'desc');
    }
}
