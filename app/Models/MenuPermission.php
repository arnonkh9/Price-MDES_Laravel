<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MenuPermission extends Model
{
    protected $fillable = [
        'role_id', 'menu_key',
        'can_see', 'can_add', 'can_edit', 'can_delete', 'can_import', 'can_export',
    ];

    protected $casts = [
        'can_see'    => 'boolean',
        'can_add'    => 'boolean',
        'can_edit'   => 'boolean',
        'can_delete' => 'boolean',
        'can_import' => 'boolean',
        'can_export' => 'boolean',
    ];

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }
}
