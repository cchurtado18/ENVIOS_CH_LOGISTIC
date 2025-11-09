<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Clientes - CH Logistic</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #ffffff;
            min-height: 100vh;
            padding: 20px;
        }
        .container { max-width: 1400px; margin: 0 auto; }
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
        .card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            padding: 30px;
        }
        .search-form {
            margin-bottom: 20px;
            display: flex;
            gap: 10px;
        }
        .search-form input {
            flex: 1;
            padding: 12px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            font-size: 16px;
        }
        .search-form button {
            padding: 12px 25px;
            background: #1262b4;
            color: white;
            border: none;
            border-radius: 10px;
            font-weight: 600;
            cursor: pointer;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th {
            background: #f8f9fa;
            padding: 15px;
            text-align: left;
            font-weight: 600;
            color: #333;
            border-bottom: 2px solid #e0e0e0;
        }
        td {
            padding: 15px;
            border-bottom: 1px solid #e0e0e0;
        }
        tr:hover { background: #f8f9fa; }
        .view-btn {
            padding: 8px 15px;
            background: #1262b4;
            color: white;
            border: none;
            border-radius: 8px;
            text-decoration: none;
            display: inline-block;
        }
        .pagination {
            margin-top: 20px;
            display: flex;
            justify-content: center;
            gap: 10px;
        }
        .pagination a, .pagination span {
            padding: 10px 15px;
            background: #f8f9fa;
            color: #333;
            text-decoration: none;
            border-radius: 8px;
        }
        .pagination .active { background: #1262b4; color: white; }
    </style>
</head>
<body>
    @include('admin.partials.nav')

    <div class="container">
        <div class="header">
            <h1>Clientes</h1>
            <div class="header-actions">
                <a href="{{ route('admin.index') }}" class="btn btn-secondary">← Volver</a>
                <a href="{{ route('admin.client.create') }}" class="btn btn-primary">➕ Crear Cliente</a>
            </div>
        </div>

        <div class="card">
            <form method="GET" action="{{ route('admin.clients') }}" class="search-form">
                <input type="text" name="search" value="{{ $search }}" placeholder="Buscar por nombre o email...">
                <button type="submit">Buscar</button>
            </form>

            <table>
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Email</th>
                        <th>Teléfono</th>
                        <th>Departamento</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($clients as $client)
                    <tr>
                        <td>{{ $client->name }}</td>
                        <td>{{ $client->email }}</td>
                        <td>{{ $client->phone }}</td>
                        <td>{{ $client->department }}</td>
                        <td>
                            <a href="{{ route('admin.client', $client->id) }}" class="view-btn">Ver</a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" style="text-align: center; padding: 40px; color: #666;">
                            No se encontraron clientes
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>

            <div class="pagination">
                {{ $clients->links() }}
            </div>
        </div>
    </div>
</body>
</html>
