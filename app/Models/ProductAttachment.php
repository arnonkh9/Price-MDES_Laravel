<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductAttachment extends Model
{
    protected $fillable = ['product_id', 'file_path', 'original_name', 'file_size', 'uploaded_at'];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * True when the attachment is an image (jpg/png/gif/webp)
     */
    public function getIsImageAttribute(): bool
    {
        return in_array(
            strtolower(pathinfo($this->file_path, PATHINFO_EXTENSION)),
            ['jpg', 'jpeg', 'png', 'gif', 'webp']
        );
    }

    /**
     * Get formatted file size
     */
    public function getFormattedSizeAttribute(): string
    {
        $bytes = $this->file_size;
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= (1 << (10 * $pow));

        return round($bytes, 2) . ' ' . $units[$pow];
    }
}
