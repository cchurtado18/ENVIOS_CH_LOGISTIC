<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PendingTracking extends Model
{
    protected $fillable = [
        'user_id',
        'tracking_number',
        'status',
        'attempts',
        'found_at',
        'error_message',
    ];

    protected $casts = [
        'found_at' => 'datetime',
    ];

    /**
     * Get the user that owns the pending tracking.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
