<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmulatorSaveState extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'console',
        'game_id',
        'emulator',
        'slot',
        'label',
        'disk_path',
        'size_bytes',
        'checksum',
    ];

    protected $casts = [
        'slot' => 'integer',
        'size_bytes' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
