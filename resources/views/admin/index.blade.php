<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Admin - CH Logistic</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #1262b4;
            min-height: 100vh;
            padding: 20px;
        }
        .container { max-width: 1200px; margin: 0 auto; }
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
        .header h1 { color: #333; font-size: 28px; }
        .header-actions { display: flex; gap: 15px; }
        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 10px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            transition: transform 0.2s ease;
        }
        .btn:hover { transform: translateY(-2px); }
        .btn-primary { background: #ff751f; color: white; }
        .btn-secondary { background: #666; color: white; }
        .btn-logout { background: #1262b4; color: white; }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .stat-card {
            background: white;
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
        }
        .stat-card h3 {
            color: #666;
            font-size: 14px;
            margin-bottom: 10px;
            text-transform: uppercase;
        }
        .stat-card .value {
            color: #333;
            font-size: 36px;
            font-weight: 700;
        }
        .actions-card {
            background: white;
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
        }
        .actions-card h2 {
            color: #333;
            margin-bottom: 20px;
            font-size: 24px;
        }
        .menu-links {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
        }
        .menu-link {
            padding: 20px;
            background: #f8f9fa;
            border-radius: 10px;
            text-decoration: none;
            color: #333;
            font-weight: 600;
            transition: background 0.2s ease;
        }
        .menu-link:hover {
            background: #e9ecef;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Panel de Administración</h1>
            <div class="header-actions">
                <a href="{{ route('admin.client.create') }}" class="btn btn-primary">➕ Crear Cliente</a>
                <form action="{{ route('logout') }}" method="POST" style="display: inline;">
                    @csrf
                    <button type="submit" class="btn btn-logout">Cerrar Sesión</button>
                </form>
            </div>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <h3>Total Clientes</h3>
                <div class="value">{{ number_format($stats['total_clients']) }}</div>
            </div>
            <div class="stat-card">
                <h3>Total Paquetes</h3>
                <div class="value">{{ number_format($stats['total_shipments']) }}</div>
            </div>
            <div class="stat-card">
                <h3>Total Facturas</h3>
                <div class="value">{{ number_format($stats['total_invoices']) }}</div>
            </div>
            <div class="stat-card">
                <h3>Ingresos Totales</h3>
                <div class="value">${{ number_format($stats['total_revenue'], 2) }}</div>
            </div>
        </div>

        <div class="actions-card">
            <h2>Accesos Rápidos</h2>
            <div class="menu-links">
                <a href="{{ route('admin.clients') }}" class="menu-link">👥 Clientes</a>
                <a href="{{ route('admin.inventory') }}" class="menu-link">📦 Inventario</a>
                <a href="{{ route('admin.invoices') }}" class="menu-link">💰 Facturas</a>
            </div>
        </div>
    </div>
</body>
</html>
