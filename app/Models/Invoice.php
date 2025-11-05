<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Invoice extends Model
{
    protected $fillable = [
        'user_id',
        'invoice_number',
        'act_number',
        'invoice_date',
        'sender_name',
        'sender_location',
        'sender_phone',
        'recipient_name',
        'recipient_location',
        'recipient_phone',
        'subtotal_maritime',
        'subtotal_aerial',
        'total_maritime_lbs',
        'total_aerial_lbs',
        'package_count',
        'delivery_cost',
        'total_amount',
        'note',
    ];

    protected $casts = [
        'invoice_date' => 'date',
        'subtotal_maritime' => 'decimal:2',
        'subtotal_aerial' => 'decimal:2',
        'total_maritime_lbs' => 'decimal:2',
        'total_aerial_lbs' => 'decimal:2',
        'delivery_cost' => 'decimal:2',
        'total_amount' => 'decimal:2',
    ];

    /**
     * Get the user (client) that owns the invoice.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the shipments for this invoice.
     */
    public function shipments(): HasMany
    {
        return $this->hasMany(Shipment::class);
    }
}
