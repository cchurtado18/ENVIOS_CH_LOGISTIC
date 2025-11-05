<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Shipment extends Model
{
    protected $fillable = [
        'tracking_number',
        'reference_number',
        'user_id',
        'warehouse_id',
        'status',
        'internal_status',
        'origin_address',
        'destination_address',
        'origin_city',
        'destination_city',
        'wrh',
        'weight',
        'weight_unit',
        'dimensions_length',
        'dimensions_width',
        'dimensions_height',
        'dimensions_unit',
        'value',
        'currency',
        'description',
        'carrier',
        'service_type',
        'service_type_billing',
        'price_per_pound',
        'invoice_value',
        'invoice_id',
        'invoiced_at',
        'pickup_date',
        'delivery_date',
        'last_scan_date',
        'last_scan_location',
        'tracking_events',
        'metadata',
    ];

    protected $casts = [
        'weight' => 'decimal:2',
        'dimensions_length' => 'decimal:2',
        'dimensions_width' => 'decimal:2',
        'dimensions_height' => 'decimal:2',
        'value' => 'decimal:2',
        'price_per_pound' => 'decimal:2',
        'invoice_value' => 'decimal:2',
        'pickup_date' => 'datetime',
        'delivery_date' => 'datetime',
        'last_scan_date' => 'datetime',
        'invoiced_at' => 'datetime',
        'tracking_events' => 'array',
        'metadata' => 'array',
    ];

    /**
     * Get the user that owns the shipment.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the warehouse that owns the shipment.
     */
    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    /**
     * Get the invoice that contains this shipment.
     */
    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }
}
