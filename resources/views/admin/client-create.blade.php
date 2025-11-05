<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Cliente - CH Logistic</title>
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
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Crear Cliente</h1>
            <a href="{{ route('admin.clients') }}" class="back-btn">← Volver</a>
        </div>

        <div class="card">
            @if ($errors->any())
                <div class="error-message">
                    @foreach ($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </div>
            @endif

            <form method="POST" action="{{ route('admin.client.store') }}">
                @csrf
                <div class="form-row">
                    <div class="form-group">
                        <label for="name">Nombre:</label>
                        <input type="text" id="name" name="name" value="{{ old('name') }}" required>
                    </div>
                    <div class="form-group">
                        <label for="email">Email:</label>
                        <input type="email" id="email" name="email" value="{{ old('email') }}" required>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="phone">Teléfono:</label>
                        <input type="tel" id="phone" name="phone" value="{{ old('phone') }}" required>
                    </div>
                    <div class="form-group">
                        <label for="department">Departamento:</label>
                        <input type="text" id="department" name="department" value="{{ old('department') }}" required>
                    </div>
                </div>
                <div class="form-group form-group-full">
                    <label for="address">Dirección:</label>
                    <input type="text" id="address" name="address" value="{{ old('address') }}" required>
                </div>
                <div class="form-group form-group-full">
                    <label for="password">Contraseña:</label>
                    <input type="password" id="password" name="password" required minlength="8">
                </div>
                <button type="submit">Crear Cliente</button>
            </form>
        </div>
    </div>
</body>
</html>
