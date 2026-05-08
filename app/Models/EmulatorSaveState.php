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
        'game_slug',
        'slot',
        'label',
        'disk_path',
        'backup_disk_path',
        'size_bytes',
        'checksum',
        'backup_size_bytes',
        'backup_checksum',
        'backup_updated_at',
    ];

    protected $casts = [
        'slot' => 'integer',
        'size_bytes' => 'integer',
        'backup_size_bytes' => 'integer',
        'backup_updated_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
