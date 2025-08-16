<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserBadge extends Model
{
    protected $table = 'user_badges';

    protected $fillable = ['user_id', 'badge_id', 'awarded_at', 'progress', 'completed'];

    protected $casts = [
        'awarded_at' => 'datetime',
        'completed' => 'boolean',
        'progress' => 'integer',
    ];

    public function badge(): BelongsTo
    {
        return $this->belongsTo(Badge::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
