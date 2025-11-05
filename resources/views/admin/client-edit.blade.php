<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Cliente - CH Logistic</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #1262b4;
            min-height: 100vh;
            padding: 20px;
        }
        .container { max-width: 800px; margin: 0 auto; }
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
        .back-btn {
            padding: 10px 20px;
            background: #666;
            color: white;
            border: none;
            border-radius: 10px;
            text-decoration: none;
            font-weight: 600;
        }
        .card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            padding: 40px;
            margin-bottom: 20px;
        }
        .card h2 {
            color: #333;
            margin-bottom: 25px;
            font-size: 24px;
            border-bottom: 2px solid #e0e0e0;
            padding-bottom: 10px;
        }
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group-full { grid-column: 1 / -1; }
        label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 600;
            font-size: 14px;
        }
        input {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            font-size: 16px;
        }
        input:focus {
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
            margin-top: 10px;
        }
        button:hover {
            transform: translateY(-2px);
        }
        .error-message {
            background: #fee;
            color: #c33;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .success-message {
            background: #dfe;
            color: #155724;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
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

        <div class="header">
            <h1>Editar Cliente</h1>
            <a href="{{ route('admin.client', $client->id) }}" class="back-btn">← Volver</a>
        </div>

        <div class="card">
            <h2>📋 Editar Información</h2>
            @if ($errors->any())
                <div class="error-message">
                    @foreach ($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </div>
            @endif

            <form method="POST" action="{{ route('admin.client.update', $client->id) }}">
                @csrf
                @method('PUT')
                <div class="form-row">
                    <div class="form-group">
                        <label for="name">Nombre:</label>
                        <input type="text" id="name" name="name" value="{{ old('name', $client->name) }}" required>
                    </div>
                    <div class="form-group">
                        <label for="email">Email:</label>
                        <input type="email" id="email" name="email" value="{{ old('email', $client->email) }}" required>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="phone">Teléfono:</label>
                        <input type="tel" id="phone" name="phone" value="{{ old('phone', $client->phone) }}" required>
                    </div>
                    <div class="form-group">
                        <label for="department">Departamento:</label>
                        <input type="text" id="department" name="department" value="{{ old('department', $client->department) }}" required>
                    </div>
                </div>
                <div class="form-group form-group-full">
                    <label for="address">Dirección:</label>
                    <input type="text" id="address" name="address" value="{{ old('address', $client->address) }}" required>
                </div>
                <button type="submit">💾 Guardar Cambios</button>
            </form>
        </div>

        <div class="card">
            <h2>🔑 Cambiar Contraseña</h2>
            <form method="POST" action="{{ route('admin.client.password.update', $client->id) }}">
                @csrf
                @method('PUT')
                <div class="form-group form-group-full">
                    <label for="password">Nueva Contraseña:</label>
                    <input type="password" id="password" name="password" required minlength="8">
                </div>
                <div class="form-group form-group-full">
                    <label for="password_confirmation">Confirmar Contraseña:</label>
                    <input type="password" id="password_confirmation" name="password_confirmation" required minlength="8">
                </div>
                <button type="submit">🔑 Cambiar Contraseña</button>
            </form>
        </div>
    </div>
</body>
</html>
