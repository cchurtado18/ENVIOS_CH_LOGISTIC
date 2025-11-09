# API Pública de Tracking - CH Logistic

## Endpoint Principal (Recomendado)

### GET `/api/public/track/{trackingNumber}`

**Descripción:** Rastrea un paquete sin guardar información en la base de datos. Ideal para uso externo.

**URL Base:** `https://TU_DOMINIO/api/public/track/{trackingNumber}`

**Ejemplo:**
```
GET https://TU_DOMINIO/api/public/track/GFUS01012542646273
```

**Respuesta Exitosa (200 OK):**
```json
{
  "success": true,
  "data": {
    "tracking_number": "GFUS01012542646273",
    "status": "delivered",
    "wrh": "WRH123456",
    "carrier": "CH Logistics",
    "pickup_date": "2024-01-15 10:30:00",
    "delivery_date": "2024-01-20 14:00:00",
    "tracking_events": [
      {
        "status": "En almacén fiscal de Nicaragua",
        "date": "2024-01-15",
        "time": "10:30"
      },
      {
        "status": "En tránsito",
        "date": "2024-01-18",
        "time": "08:00"
      },
      {
        "status": "En oficina",
        "date": "2024-01-20",
        "time": "14:00"
      }
    ],
    "description": "Descripción del paquete",
    "weight": "2.5",
    "origin_address": "Miami, FL",
    "destination_address": "Managua, Nicaragua"
  }
}
```

**Respuesta de Error (404 Not Found):**
```json
{
  "success": false,
  "message": "Track not found"
}
```

**Respuesta de Error (500 Internal Server Error):**
```json
{
  "success": false,
  "message": "Failed to track shipment",
  "error": "Mensaje de error detallado"
}
```

## Endpoint Alternativo

### GET `/api/track/{trackingNumber}`

**Descripción:** Rastrea un paquete (puede guardar información en la base de datos).

**URL Base:** `https://TU_DOMINIO/api/track/{trackingNumber}`

**Ejemplo:**
```
GET https://TU_DOMINIO/api/track/GFUS01012542646273
```

**Respuesta:** Similar a la anterior, pero incluye un mensaje adicional.

## Cómo Usar en Otro Sitio Web

### Opción 1: JavaScript (AJAX/Fetch)

```javascript
// Ejemplo con Fetch API
async function trackPackage(trackingNumber) {
    try {
        const response = await fetch(`https://TU_DOMINIO/api/public/track/${trackingNumber}`);
        const result = await response.json();
        
        if (result.success) {
            console.log('Paquete encontrado:', result.data);
            // Mostrar información del paquete
        } else {
            console.error('Error:', result.message);
        }
    } catch (error) {
        console.error('Error de conexión:', error);
    }
}

// Usar
trackPackage('GFUS01012542646273');
```

### Opción 2: PHP (cURL)

```php
<?php
function trackPackage($trackingNumber) {
    $url = "https://TU_DOMINIO/api/public/track/{$trackingNumber}";
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Accept: application/json'
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode === 200) {
        $data = json_decode($response, true);
        if ($data['success']) {
            return $data['data'];
        }
    }
    
    return null;
}

// Usar
$package = trackPackage('GFUS01012542646273');
if ($package) {
    echo "Estado: " . $package['status'];
    echo "WRH: " . $package['wrh'];
}
?>
```

### Opción 3: HTML (iframe o embed)

```html
<!-- Puedes usar un iframe para mostrar el resultado -->
<iframe 
    src="https://TU_DOMINIO/track/GFUS01012542646273" 
    width="100%" 
    height="600px"
    frameborder="0">
</iframe>
```

## Notas Importantes

1. **No requiere autenticación**: Esta API es completamente pública
2. **No guarda datos**: El endpoint `/api/public/track` no guarda información en la base de datos
3. **Límites de uso**: Ten en cuenta los límites de velocidad del servidor
4. **Formato de respuesta**: Siempre retorna JSON cuando se accede vía API

## Ejemplo Completo de Integración

```html
<!DOCTYPE html>
<html>
<head>
    <title>Tracking Ejemplo</title>
</head>
<body>
    <input type="text" id="trackingInput" placeholder="Número de tracking">
    <button onclick="track()">Rastrear</button>
    <div id="result"></div>

    <script>
        async function track() {
            const trackingNumber = document.getElementById('trackingInput').value;
            const resultDiv = document.getElementById('result');
            
            resultDiv.innerHTML = 'Rastreando...';
            
            try {
                const response = await fetch(`https://TU_DOMINIO/api/public/track/${trackingNumber}`);
                const data = await response.json();
                
                if (data.success) {
                    const shipment = data.data;
                    resultDiv.innerHTML = `
                        <h3>Estado: ${shipment.status}</h3>
                        <p>WRH: ${shipment.wrh || 'Pendiente'}</p>
                        <p>Transportista: ${shipment.carrier || 'CH Logistics'}</p>
                        ${shipment.delivery_date ? `<p>Entregado: ${shipment.delivery_date}</p>` : ''}
                        <h4>Historial:</h4>
                        <ul>
                            ${shipment.tracking_events.map(event => 
                                `<li>${event.status} - ${event.date} ${event.time || ''}</li>`
                            ).join('')}
                        </ul>
                    `;
                } else {
                    resultDiv.innerHTML = `<p style="color: red;">Error: ${data.message}</p>`;
                }
            } catch (error) {
                resultDiv.innerHTML = `<p style="color: red;">Error de conexión: ${error.message}</p>`;
            }
        }
    </script>
</body>
</html>
```



