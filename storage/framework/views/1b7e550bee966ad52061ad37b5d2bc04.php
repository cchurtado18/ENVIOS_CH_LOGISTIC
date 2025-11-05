<a href="<?php echo e(route('dashboard.shipment', $shipment->id)); ?>" style="text-decoration: none; color: inherit; cursor: pointer;">
    <div class="shipment-item">
        <div class="shipment-header">
            <div class="tracking-number"><?php echo e($shipment->tracking_number); ?></div>
            <span class="status-badge status-<?php echo e($shipment->status); ?>">
                <?php echo e(ucfirst(str_replace('_', ' ', $shipment->status))); ?>

            </span>
        </div>
        <div class="shipment-details">
            <?php if($shipment->wrh): ?>
                <div class="detail-item">
                    <span class="detail-label">WRH:</span>
                    <span class="detail-value <?php if($shipment->wrh === 'pendiente'): ?> text-muted <?php endif; ?>">
                        <?php echo e($shipment->wrh); ?>

                    </span>
                </div>
            <?php endif; ?>
            <?php if($shipment->carrier): ?>
                <div class="detail-item">
                    <span class="detail-label">Transportista:</span>
                    <span class="detail-value"><?php echo e($shipment->carrier); ?></span>
                </div>
            <?php endif; ?>
            <?php if($shipment->pickup_date): ?>
                <div class="detail-item">
                    <span class="detail-label">Registrado:</span>
                    <span class="detail-value">
                        <?php echo e($shipment->pickup_date->format('d/m/Y H:i')); ?>

                    </span>
                </div>
            <?php endif; ?>
            <?php if($shipment->delivery_date): ?>
                <div class="detail-item">
                    <span class="detail-label">Entregado:</span>
                    <span class="detail-value">
                        <?php echo e($shipment->delivery_date->format('d/m/Y H:i')); ?>

                    </span>
                </div>
            <?php endif; ?>
        </div>
    </div>
</a>

<?php /**PATH /Users/carloshurtado/Documents/ch-logistic-api/resources/views/dashboard/partials/shipment-item.blade.php ENDPATH**/ ?>