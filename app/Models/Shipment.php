<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class Shipment extends Model
{
    // Constantes de estado para evitar errores de tipeo
    const STATUS_PENDING = 'pending';
    const STATUS_IN_TRANSIT = 'in_transit';
    const STATUS_DELIVERED = 'delivered';
    const STATUS_EXCEPTION = 'exception';

    // Constantes de estado interno
    const INTERNAL_STATUS_EN_TRANSITO = 'en_transito';
    const INTERNAL_STATUS_RECIBIDO_CH = 'recibido_ch';
    const INTERNAL_STATUS_FACTURADO = 'facturado';
    const INTERNAL_STATUS_ENTREGADO = 'entregado';

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
     * Boot the model - Set up event listeners
     */
    protected static function boot()
    {
        parent::boot();

        // Automatically sync status when delivery_date is set
        static::saving(function ($shipment) {
            $shipment->syncDeliveryStatus();
        });
    }

    /**
     * Sync delivery status based on delivery_date
     * This ensures consistency: if delivery_date exists, status should be 'delivered'
     */
    public function syncDeliveryStatus(): void
    {
        // If delivery_date is set but status is not 'delivered', update it
        if ($this->delivery_date && $this->status !== self::STATUS_DELIVERED) {
            $this->status = self::STATUS_DELIVERED;
        }

        // If status is 'delivered' but no delivery_date, set it to now
        if ($this->status === self::STATUS_DELIVERED && !$this->delivery_date) {
            $this->delivery_date = now();
        }
    }

    /**
     * Check if shipment is delivered
     * A shipment is considered delivered if:
     * 1. status === 'delivered' OR
     * 2. delivery_date is not null
     */
    public function isDelivered(): bool
    {
        return $this->status === self::STATUS_DELIVERED || $this->delivery_date !== null;
    }

    /**
     * Check if shipment is in transit
     */
    public function isInTransit(): bool
    {
        return !$this->isDelivered() && $this->status !== self::STATUS_EXCEPTION;
    }

    /**
     * Check if shipment is pending
     */
    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    /**
     * Check if shipment has exception
     */
    public function hasException(): bool
    {
        return $this->status === self::STATUS_EXCEPTION;
    }

    /**
     * Scope: Only delivered shipments
     */
    public function scopeDelivered(Builder $query): Builder
    {
        return $query->where(function($q) {
            $q->where('status', self::STATUS_DELIVERED)
              ->orWhereNotNull('delivery_date');
        });
    }

    /**
     * Scope: Only in-transit shipments (not delivered)
     */
    public function scopeInTransit(Builder $query): Builder
    {
        return $query->where(function($q) {
            $q->where('status', '!=', self::STATUS_DELIVERED)
              ->whereNull('delivery_date');
        });
    }

    /**
     * Scope: Only pending shipments
     */
    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    /**
     * Scope: Shipments with exceptions
     */
    public function scopeWithException(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_EXCEPTION);
    }

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
