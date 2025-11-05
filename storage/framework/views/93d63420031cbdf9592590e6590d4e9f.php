<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Envios CH Logistic</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background: #1262b4;
            min-height: 100vh;
            padding: 20px;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .header {
            background: white;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            padding: 30px 40px;
            margin-bottom: 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .header h1 {
            color: #333;
            font-size: 28px;
        }
        
        .header-info {
            display: flex;
            align-items: center;
            gap: 20px;
        }
        
        .user-info {
            text-align: right;
        }
        
        .user-name {
            font-weight: 600;
            color: #333;
            font-size: 16px;
        }
        
        .user-email {
            color: #666;
            font-size: 14px;
        }
        
        .logout-btn {
            padding: 10px 20px;
            background: #1262b4;
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s ease;
        }
        
        .logout-btn:hover {
            transform: translateY(-2px);
        }
        
        .main-content {
            display: grid;
            grid-template-columns: 1fr 2fr;
            gap: 30px;
        }
        
        .card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            padding: 30px;
        }
        
        .card h2 {
            color: #333;
            margin-bottom: 20px;
            font-size: 24px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 600;
            font-size: 14px;
        }
        
        input[type="text"] {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            font-size: 16px;
            transition: border-color 0.3s ease;
        }
        
        input[type="text"]:focus {
            outline: none;
            border-color: #1262b4;
        }
        
        button {
            width: 100%;
            padding: 15px;
            background: #ff751f;
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        
        button:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(255, 117, 31, 0.4);
        }
        
        .shipments-list {
            max-height: 600px;
            overflow-y: auto;
        }
        
        .shipment-item {
            background: #f8f9fa;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 15px;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        
        a .shipment-item:hover {
            transform: translateX(5px);
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.1);
            cursor: pointer;
        }
        
        a:hover .shipment-item {
            background: #e8f4fd;
        }
        
        .shipment-header {
            display: flex;
            justify-content: space-between;
            align-items: start;
            margin-bottom: 10px;
        }
        
        .tracking-number {
            font-size: 18px;
            font-weight: 600;
            color: #333;
        }
        
        .status-badge {
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }
        
        .status-pending {
            background: #fff3cd;
            color: #856404;
        }
        
        .status-in_transit {
            background: #d1ecf1;
            color: #0c5460;
        }
        
        .status-delivered {
            background: #d4edda;
            color: #155724;
        }
        
        .status-exception {
            background: #f8d7da;
            color: #721c24;
        }
        
        .shipment-details {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            margin-top: 10px;
        }
        
        .detail-item {
            font-size: 14px;
        }
        
        .detail-label {
            color: #666;
            font-weight: 600;
        }
        
        .detail-value {
            color: #333;
        }
        
        .text-muted {
            color: #999;
            font-style: italic;
        }
        
        .empty-state {
            text-align: center;
            padding: 40px;
            color: #999;
        }
        
        .empty-state-icon {
            font-size: 64px;
            margin-bottom: 20px;
        }
        
        .success-message,
        .error-message,
        .info-message {
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
        }
        
        .success-message {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .error-message {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .info-message {
            background: #d1ecf1;
            color: #0c5460;
            border: 1px solid #bee5eb;
        }
        
        .tabs {
            display: flex;
            border-bottom: 2px solid #e0e0e0;
            margin-bottom: 20px;
        }
        
        .tab {
            flex: 1;
            padding: 12px 20px;
            text-align: center;
            cursor: pointer;
            background: transparent;
            border: none;
            border-bottom: 3px solid transparent;
            font-size: 16px;
            font-weight: 600;
            color: #666;
            transition: all 0.3s ease;
        }
        
        .tab:hover {
            color: #1262b4;
            background: #f8f9fa;
        }
        
        .tab.active {
            color: #1262b4;
            border-bottom-color: #ff751f;
        }
        
        .tab-content {
            display: none;
        }
        
        .tab-content.active {
            display: block;
        }
        
        .tab-count {
            display: inline-block;
            background: #1262b4;
            color: white;
            border-radius: 10px;
            padding: 2px 8px;
            font-size: 12px;
            margin-left: 5px;
        }
        
        .tab.active .tab-count {
            background: #ff751f;
        }
        
        .search-filter-bar {
            background: #f8f9fa;
            border-radius: 12px;
            padding: 15px;
            margin-bottom: 20px;
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
            align-items: center;
        }
        
        .search-box {
            flex: 1;
            min-width: 200px;
            position: relative;
        }
        
        .search-box input {
            width: 100%;
            padding: 10px 40px 10px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 14px;
        }
        
        .search-box input:focus {
            border-color: #1262b4;
            outline: none;
        }
        
        .search-icon {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: #999;
            font-size: 16px;
        }
        
        .filter-select {
            padding: 10px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 14px;
            background: white;
            cursor: pointer;
            transition: border-color 0.3s ease;
        }
        
        .filter-select:focus {
            border-color: #1262b4;
            outline: none;
        }
        
        .clear-filters-btn {
            padding: 10px 20px;
            background: #e0e0e0;
            color: #666;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .clear-filters-btn:hover {
            background: #d0d0d0;
            color: #333;
        }
        
        .no-results {
            text-align: center;
            padding: 40px;
            color: #999;
        }
        
        .no-results-icon {
            font-size: 64px;
            margin-bottom: 20px;
        }
        
        @media (max-width: 768px) {
            .main-content {
                grid-template-columns: 1fr;
            }
            
            .header {
                flex-direction: column;
                gap: 20px;
            }
            
            .header-info {
                width: 100%;
                justify-content: space-between;
            }
            
            .shipment-details {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Panel de Control</h1>
            <div class="header-info">
                <div class="user-info">
                    <div class="user-name"><?php echo e($user['name'] ?? 'Usuario'); ?></div>
                    <div class="user-email"><?php echo e($user['email'] ?? ''); ?></div>
                </div>
                <div style="display: flex; gap: 10px;">
                    <?php if(isset($user['role']) && $user['role'] === 'admin'): ?>
                        <a href="<?php echo e(route('admin.index')); ?>" style="padding: 10px 20px; background: #ff751f; color: white; border: none; border-radius: 10px; font-size: 14px; font-weight: 600; cursor: pointer; transition: transform 0.2s ease; text-decoration: none; display: inline-block;">🔐 Admin</a>
                    <?php endif; ?>
                    <form method="POST" action="<?php echo e(route('logout')); ?>" style="display: inline;">
                        <?php echo csrf_field(); ?>
                        <button type="submit" class="logout-btn">Cerrar Sesión</button>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="main-content">
            <div class="card">
                <h2>Rastrear Nuevo Envío</h2>
                
                <?php if(session('success')): ?>
                    <div class="success-message">
                        <?php echo e(session('success')); ?>

                    </div>
                <?php endif; ?>
                
                <?php if(session('error')): ?>
                    <div class="error-message">
                        <?php echo e(session('error')); ?>

                    </div>
                <?php endif; ?>
                
                <?php if(session('info')): ?>
                    <div class="info-message">
                        <?php echo e(session('info')); ?>

                    </div>
                <?php endif; ?>
                
                <form method="POST" action="<?php echo e(route('dashboard.track')); ?>">
                    <?php echo csrf_field(); ?>
                    <div class="form-group">
                        <label for="tracking_number">Número de Tracking:</label>
                        <input type="text" id="tracking_number" name="tracking_number" placeholder="Ingresa el tracking" required>
                    </div>
                    <button type="submit">Rastrear y Guardar</button>
                </form>
            </div>
            
            <div class="card">
                <h2>Mis Envíos</h2>
                
                <!-- Tabs -->
                <div class="tabs">
                    <button class="tab active" onclick="switchTab('active')">
                        En Tránsito <span class="tab-count"><?php echo e($activeShipments->count()); ?></span>
                    </button>
                    <button class="tab" onclick="switchTab('pending')">
                        En Verificación <span class="tab-count"><?php echo e($pendingTrackings->count()); ?></span>
                    </button>
                    <button class="tab" onclick="switchTab('delivered')">
                        Historial <span class="tab-count"><?php echo e($deliveredShipments->count()); ?></span>
                    </button>
                </div>
                
                <!-- Search and Filters -->
                <div class="search-filter-bar">
                    <div class="search-box">
                        <input type="text" id="searchInput" placeholder="🔍 Buscar por tracking, WRH o descripción..." onkeyup="filterShipments()">
                        <span class="search-icon">🔍</span>
                    </div>
                    <select class="filter-select" id="statusFilter" onchange="filterShipments()">
                        <option value="">Todos los estados</option>
                        <option value="pending">Pendiente</option>
                        <option value="in_transit">En Tránsito</option>
                        <option value="exception">Excepción</option>
                        <option value="delivered">Entregado</option>
                    </select>
                    <button class="clear-filters-btn" onclick="clearFilters()">Limpiar Filtros</button>
                </div>
                
                <!-- Active Shipments Tab -->
                <div id="tab-active" class="tab-content active">
                    <div id="shipments-list-active" class="shipments-list">
                        <?php if($activeShipments->count() > 0): ?>
                            <?php $__currentLoopData = $activeShipments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $shipment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <?php echo $__env->make('dashboard.partials.shipment-item', ['shipment' => $shipment], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        <?php else: ?>
                            <div class="empty-state">
                                <div class="empty-state-icon">🚚</div>
                                <p>No tienes envíos en tránsito</p>
                                <p style="margin-top: 10px; font-size: 14px;">Usa el formulario para rastrear tu primer envío</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Pending Trackings Tab -->
                <div id="tab-pending" class="tab-content">
                    <div id="shipments-list-pending" class="shipments-list">
                        <?php if($pendingTrackings->count() > 0): ?>
                            <?php $__currentLoopData = $pendingTrackings; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $pending): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <div class="shipment-item" style="border-left: 4px solid #ff751f;">
                                    <div class="shipment-header">
                                        <div class="tracking-number"><?php echo e($pending->tracking_number); ?></div>
                                        <div style="display: flex; gap: 10px; align-items: center;">
                                            <span class="status-badge" style="background: #fff3cd; color: #856404;">
                                                ⏳ En Verificación
                                            </span>
                                            <form action="<?php echo e(route('pending-tracking.delete', $pending->id)); ?>" method="POST" style="display: inline;" onsubmit="return confirm('¿Estás seguro de eliminar este tracking de verificación?')">
                                                <?php echo csrf_field(); ?>
                                                <?php echo method_field('DELETE'); ?>
                                                <button type="submit" style="background: #dc3545; color: white; border: none; padding: 6px 12px; border-radius: 8px; cursor: pointer; font-size: 12px; font-weight: 600;">🗑️ Eliminar</button>
                                            </form>
                                        </div>
                                    </div>
                                    <div class="shipment-details">
                                        <div class="detail-item" style="grid-column: 1 / -1;">
                                            <span class="detail-label">Estado:</span>
                                            <span class="detail-value">
                                                Estamos monitoreando este tracking y te notificaremos cuando esté disponible.
                                            </span>
                                        </div>
                                        <div class="detail-item">
                                            <span class="detail-label">Agregado:</span>
                                            <span class="detail-value">
                                                <?php echo e($pending->created_at->format('d/m/Y H:i')); ?>

                                            </span>
                                        </div>
                                        <div class="detail-item">
                                            <span class="detail-label">Intentos:</span>
                                            <span class="detail-value">
                                                <?php echo e($pending->attempts); ?> verificación(es)
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        <?php else: ?>
                            <div class="empty-state">
                                <div class="empty-state-icon">✅</div>
                                <p>No hay trackings en verificación</p>
                                <p style="margin-top: 10px; font-size: 14px;">Todos tus trackings están disponibles</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Delivered Shipments Tab -->
                <div id="tab-delivered" class="tab-content">
                    <div id="shipments-list-delivered" class="shipments-list">
                        <?php if($deliveredShipments->count() > 0): ?>
                            <?php $__currentLoopData = $deliveredShipments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $shipment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <?php echo $__env->make('dashboard.partials.shipment-item', ['shipment' => $shipment], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        <?php else: ?>
                            <div class="empty-state">
                                <div class="empty-state-icon">📬</div>
                                <p>No tienes envíos entregados aún</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        function switchTab(tab) {
            // Hide all tab contents
            document.querySelectorAll('.tab-content').forEach(content => {
                content.classList.remove('active');
            });
            
            // Remove active class from all tabs
            document.querySelectorAll('.tab').forEach(tabBtn => {
                tabBtn.classList.remove('active');
            });
            
            // Show selected tab content
            document.getElementById('tab-' + tab).classList.add('active');
            
            // Add active class to clicked tab
            event.target.classList.add('active');
            
            // Re-apply filters when switching tabs
            filterShipments();
        }
        
        function filterShipments() {
            const searchTerm = document.getElementById('searchInput').value.toLowerCase();
            const statusFilter = document.getElementById('statusFilter').value;
            const activeTab = document.querySelector('.tab.active').getAttribute('onclick');
            
            // Determine which list to filter
            let listId = 'shipments-list-active';
            if (activeTab.includes('delivered')) {
                listId = 'shipments-list-delivered';
            } else if (activeTab.includes('pending')) {
                listId = 'shipments-list-pending';
            }
            const shipmentItems = document.querySelectorAll(`#${listId} .shipment-item`);
            
            let visibleCount = 0;
            
            shipmentItems.forEach(item => {
                const trackingNumber = item.querySelector('.tracking-number').textContent.toLowerCase();
                const status = item.querySelector('.status-badge').className;
                const detailLabels = item.querySelectorAll('.detail-label');
                const detailValues = item.querySelectorAll('.detail-value');
                
                // Collect all searchable text
                let searchableText = trackingNumber;
                detailLabels.forEach((label, index) => {
                    if (label.textContent.trim() === 'WRH:') {
                        searchableText += ' ' + detailValues[index].textContent.toLowerCase();
                    }
                });
                
                // Apply search filter
                const matchesSearch = searchTerm === '' || searchableText.includes(searchTerm);
                
                // Apply status filter
                const matchesStatus = statusFilter === '' || status.includes(statusFilter);
                
                // Show/hide item based on filters
                if (matchesSearch && matchesStatus) {
                    item.style.display = 'block';
                    visibleCount++;
                } else {
                    item.style.display = 'none';
                }
            });
            
            // Show no results message if needed
            showNoResults(listId, visibleCount === 0 && shipmentItems.length > 0);
        }
        
        function showNoResults(listId, show) {
            let noResultsDiv = document.querySelector(`#${listId} .no-results`);
            
            if (show && !noResultsDiv) {
                noResultsDiv = document.createElement('div');
                noResultsDiv.className = 'no-results';
                noResultsDiv.innerHTML = '<div class="no-results-icon">🔍</div><p>No se encontraron resultados con estos filtros</p>';
                document.getElementById(listId).appendChild(noResultsDiv);
            } else if (!show && noResultsDiv) {
                noResultsDiv.remove();
            }
        }
        
        function clearFilters() {
            document.getElementById('searchInput').value = '';
            document.getElementById('statusFilter').value = '';
            filterShipments();
        }
    </script>
</body>
</html>
<?php /**PATH /Users/carloshurtado/Documents/ch-logistic-api/resources/views/dashboard/index.blade.php ENDPATH**/ ?>