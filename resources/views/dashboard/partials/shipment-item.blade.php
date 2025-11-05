<a href="{{ route('dashboard.shipments.show', $shipment->id) }}" style="text-decoration: none; color: inherit; cursor: pointer;">
    <div class="shipment-item">
        <div class="shipment-header">
            <div class="tracking-number">{{ $shipment->tracking_number }}</div>
            <span class="status-badge status-{{ $shipment->status }}">
                {{ ucfirst(str_replace('_', ' ', $shipment->status)) }}
            </span>
        </div>
        <div class="shipment-details">
            @if ($shipment->wrh)
                <div class="detail-item">
                    <span class="detail-label">WRH:</span>
                    <span class="detail-value @if($shipment->wrh === 'pendiente') text-muted @endif">
                        {{ $shipment->wrh }}
                    </span>
                </div>
            @endif
            @if ($shipment->carrier)
                <div class="detail-item">
                    <span class="detail-label">Transportista:</span>
                    <span class="detail-value">{{ $shipment->carrier }}</span>
                </div>
            @endif
            @if ($shipment->pickup_date)
                <div class="detail-item">
                    <span class="detail-label">Registrado:</span>
                    <span class="detail-value">
                        {{ $shipment->pickup_date->format('d/m/Y H:i') }}
                    </span>
                </div>
            @endif
            @if ($shipment->delivery_date)
                <div class="detail-item">
                    <span class="detail-label">Entregado:</span>
                    <span class="detail-value">
                        {{ $shipment->delivery_date->format('d/m/Y H:i') }}
                    </span>
                </div>
            @endif
        </div>
    </div>
</a>

