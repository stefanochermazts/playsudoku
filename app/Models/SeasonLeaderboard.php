<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SeasonLeaderboard extends Model
{
    protected $fillable = ['season_id','user_id','points','wins','participations'];

    public function season(): BelongsTo { return $this->belongsTo(Season::class); }
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
}



