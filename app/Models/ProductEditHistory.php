<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductEditHistory extends Model
{
    protected $fillable = ['product_id', 'date', 'user', 'action', 'detail', 'source', 'url'];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
