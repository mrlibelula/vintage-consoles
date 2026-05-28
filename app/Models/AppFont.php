<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AppFont extends Model
{
    use HasFactory;

    protected $fillable = [
        'label',
        'family_name',
        'relative_path',
        'format',
        'is_bundled',
    ];

    protected $casts = [
        'is_bundled' => 'boolean',
    ];
}
