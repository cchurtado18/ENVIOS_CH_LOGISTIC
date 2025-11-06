# Estructura JSON de la API Pública de Tracking

## Endpoint
```
GET /api/public/track/{trackingNumber}
```

## Respuesta Exitosa (200 OK)

```json
{
  "success": true,
  "data": {
    "tracking_number": "GFUS01012542646273",
    "status": "delivered",
    "wrh": "731138",
    "weight": 2.5,
    "weight_unit": "lbs",
    "description": "1 bulto(s) con 2.5 lbs",
    "carrier": "CH Logistics",
    "pickup_date": "2024-01-15 13:09:00",
    "delivery_date": "2024-01-20 14:38:27",
    "tracking_events": [
      {
        "date": "01/20/2025",
        "time": "2:38:27 PM",
        "status": "Delivered",
        "description": "Delivered"
      },
      {
        "date": "01/18/2025",
        "time": "8:00:00 AM",
        "status": "In Transit",
        "description": "In Transit"
      },
      {
        "date": "01/15/2025",
        "time": "1:09:00 PM",
        "status": "Recibido en oficina Metrocentro",
        "description": "Recibido en oficina Metrocentro"
      }
    ]
  }
}
```

## Campos de la Respuesta

### Campo Principal: `data`

| Campo | Tipo | Descripción | Ejemplo |
|-------|------|-------------|---------|
| `tracking_number` | string | Número de tracking del paquete | `"GFUS01012542646273"` |
| `status` | string | Estado del paquete: `pending`, `in_transit`, `delivered`, `exception` | `"delivered"` |
| `wrh` | string | Número WRH (Warehouse Receipt Number). Si no está disponible, será `"pendiente"` | `"731138"` o `"pendiente"` |
| `weight` | float\|null | Peso del paquete en libras | `2.5` o `null` |
| `weight_unit` | string | Unidad de peso (siempre `"lbs"`) | `"lbs"` |
| `description` | string\|null | Descripción del paquete (ej: "1 bulto(s) con 2.5 lbs") | `"1 bulto(s) con 2.5 lbs"` |
| `carrier` | string | Nombre del transportista (siempre `"CH Logistics"`) | `"CH Logistics"` |
| `pickup_date` | string\|null | Fecha y hora de recogida en formato `YYYY-MM-DD HH:MM:SS` | `"2024-01-15 13:09:00"` |
| `delivery_date` | string\|null | Fecha y hora de entrega en formato `YYYY-MM-DD HH:MM:SS`. Solo existe si el paquete fue entregado | `"2024-01-20 14:38:27"` |
| `tracking_events` | array | Array de eventos de seguimiento del paquete (ordenados del más reciente al más antiguo) | Ver abajo |

### Array: `tracking_events`

Cada evento en el array tiene la siguiente estructura:

```json
{
  "date": "01/20/2025",
  "time": "2:38:27 PM",
  "status": "Delivered",
  "description": "Delivered"
}
```

| Campo | Tipo | Descripción | Ejemplo |
|-------|------|-------------|---------|
| `date` | string | Fecha del evento en formato `MM/DD/YYYY` | `"01/20/2025"` |
| `time` | string | Hora del evento en formato `H:MM:SS AM/PM` | `"2:38:27 PM"` |
| `status` | string | Texto del estado del evento | `"Delivered"`, `"In Transit"`, `"Recibido en oficina Metrocentro"` |
| `description` | string | Descripción del evento (igual que `status`) | `"Delivered"` |

## Respuestas de Error

### Paquete No Encontrado (404 Not Found)

```json
{
  "success": false,
  "message": "Track not found"
}
```

### Error del Servidor (500 Internal Server Error)

```json
{
  "success": false,
  "message": "Failed to track shipment",
  "error": "Mensaje de error detallado aquí"
}
```

## Ejemplos de Respuestas Reales

### Ejemplo 1: Paquete Entregado

```json
{
  "success": true,
  "data": {
    "tracking_number": "GFUS01012542646273",
    "status": "delivered",
    "wrh": "731138",
    "weight": 2.5,
    "weight_unit": "lbs",
    "description": "1 bulto(s) con 2.5 lbs",
    "carrier": "CH Logistics",
    "pickup_date": "2024-01-15 13:09:00",
    "delivery_date": "2024-01-20 14:38:27",
    "tracking_events": [
      {
        "date": "01/20/2025",
        "time": "2:38:27 PM",
        "status": "Delivered",
        "description": "Delivered"
      },
      {
        "date": "01/18/2025",
        "time": "8:00:00 AM",
        "status": "In Transit",
        "description": "In Transit"
      },
      {
        "date": "01/15/2025",
        "time": "1:09:00 PM",
        "status": "Recibido en oficina Metrocentro",
        "description": "Recibido en oficina Metrocentro"
      }
    ]
  }
}
```

### Ejemplo 2: Paquete En Tránsito

```json
{
  "success": true,
  "data": {
    "tracking_number": "GFUS01012542646274",
    "status": "in_transit",
    "wrh": "pendiente",
    "weight": 1.2,
    "weight_unit": "lbs",
    "description": "1 bulto(s) con 1.2 lbs",
    "carrier": "CH Logistics",
    "pickup_date": "2024-01-20 10:00:00",
    "delivery_date": null,
    "tracking_events": [
      {
        "date": "01/20/2025",
        "time": "10:00:00 AM",
        "status": "Recibido en oficina Metrocentro",
        "description": "Recibido en oficina Metrocentro"
      }
    ]
  }
}
```

### Ejemplo 3: Paquete Pendiente (sin eventos)

```json
{
  "success": true,
  "data": {
    "tracking_number": "GFUS01012542646275",
    "status": "pending",
    "wrh": "pendiente",
    "weight": null,
    "weight_unit": "lbs",
    "description": null,
    "carrier": "CH Logistics",
    "pickup_date": null,
    "delivery_date": null,
    "tracking_events": []
  }
}
```

## Notas Importantes

1. **Formato de Fechas:**
   - `pickup_date` y `delivery_date`: Formato `YYYY-MM-DD HH:MM:SS` (24 horas)
   - `tracking_events[].date`: Formato `MM/DD/YYYY`
   - `tracking_events[].time`: Formato `H:MM:SS AM/PM` (12 horas)

2. **Valores Nulos:**
   - Si `wrh` no está disponible, será la cadena `"pendiente"`
   - Si `weight` no está disponible, será `null`
   - Si `description` no está disponible, será `null`
   - Si `pickup_date` no está disponible, será `null`
   - Si `delivery_date` no está disponible (paquete no entregado), será `null`
   - Si no hay eventos, `tracking_events` será un array vacío `[]`

3. **Estados Posibles:**
   - `pending`: Paquete pendiente, sin eventos
   - `in_transit`: Paquete en tránsito
   - `delivered`: Paquete entregado
   - `exception`: Paquete con excepción

4. **Transportista:**
   - El campo `carrier` siempre será `"CH Logistics"` (no "Everest CargoTrack")

5. **Eventos de Tracking:**
   - Los eventos están ordenados del más reciente al más antiguo
   - Si un evento contiene "Recibido en oficina Metrocentro" o "received in metrocentro office", se traduce automáticamente a "En almacén fiscal de Nicaragua" en la vista web, pero en la API aparece el texto original
   - Si un evento contiene "Delivered" o "Entregado", se traduce a "En oficina" en la vista web, pero en la API aparece el texto original

