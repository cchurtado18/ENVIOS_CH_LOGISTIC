<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Facturas - CH Logistic</title>
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
        
        .header-actions {
            display: flex;
            align-items: center;
            gap: 20px;
        }
        
        .back-btn, .create-btn, .logout-btn {
            padding: 10px 20px;
            background: #666;
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s ease;
            text-decoration: none;
            display: inline-block;
        }
        
        .back-btn:hover, .logout-btn:hover {
            transform: translateY(-2px);
        }
        
        .create-btn {
            background: #ff751f;
        }
        
        .logout-btn {
            background: #1262b4;
        }
        
        .card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            padding: 30px;
        }
        
        .invoices-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .invoices-table th {
            background: #f8f9fa;
            padding: 15px;
            text-align: left;
            font-weight: 600;
            color: #333;
            border-bottom: 2px solid #e0e0e0;
        }
        
        .invoices-table td {
            padding: 15px;
            border-bottom: 1px solid #e0e0e0;
        }
        
        .invoices-table tr:hover {
            background: #f8f9fa;
        }
        
        .view-btn {
            padding: 8px 15px;
            background: #1262b4;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s ease;
            text-decoration: none;
            display: inline-block;
        }
        
        .view-btn:hover {
            transform: translateY(-2px);
        }
        
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #666;
        }
        
        .empty-state-icon {
            font-size: 64px;
            margin-bottom: 20px;
        }
        
        .error-message {
            background: #fee;
            color: #c33;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
            font-size: 14px;
            border: 1px solid #fcc;
        }
        
        .success-message {
            background: #dfe;
            color: #155724;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
            font-size: 14px;
            border: 1px solid #c3e6cb;
        }
    </style>
</head>
<body>
    <div class="container">
        @if (session('success'))
            <div class="success-message">
                {{ session('success') }}
            </div>
        @endif
        
        @if (session('error'))
            <div class="error-message">
                {{ session('error') }}
            </div>
        @endif
        
        <div class="header">
            <h1>📄 Facturas</h1>
            <div class="header-actions">
                <a href="{{ route('admin.index') }}" class="back-btn">← Panel Admin</a>
                <a href="{{ route('admin.invoice.create') }}" class="create-btn">➕ Nueva Factura</a>
                <form method="POST" action="{{ route('logout') }}" style="display: inline;">
                    @csrf
                    <button type="submit" class="logout-btn">Cerrar Sesión</button>
                </form>
            </div>
        </div>
        
        <div class="card">
            @if($invoices->count() > 0)
                <table class="invoices-table">
                    <thead>
                        <tr>
                            <th>N° Factura</th>
                            <th>Cliente</th>
                            <th>Fecha</th>
                            <th>Paquetes</th>
                            <th>Total</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($invoices as $invoice)
                            <tr>
                                <td style="font-weight: 600;">#{{ $invoice->invoice_number }}</td>
                                <td>{{ $invoice->user->name }}</td>
                                <td>{{ $invoice->invoice_date->format('d/m/Y') }}</td>
                                <td>{{ $invoice->package_count }}</td>
                                <td style="font-weight: 600; color: #1262b4;">${{ number_format($invoice->total_amount, 2) }}</td>
                                <td>
                                    <a href="{{ route('admin.invoice.show', $invoice->id) }}" class="view-btn">Ver Factura</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <div class="empty-state">
                    <div class="empty-state-icon">📄</div>
                    <p>No hay facturas creadas aún</p>
                </div>
            @endif
        </div>
    </div>
</body>
</html>

