# Frontend App — Sistema de Reservas (PHP)

Aplicación web en PHP que consume la API REST de **Desafio03-DWF** (Spring Boot). Autenticación **directa** con usuario y contraseña (JWT en sesión). Sin OAuth2 ni gestión de roles.

## Estructura de archivos

```
frontend_app/
├── index.php          # Dashboard: eventos (cards) + reservas (tabla)
├── api_client.php     # Clase cURL — todas las peticiones a la API
├── utils.php          # Mensajes amigables y alertas UI
├── config.php         # URL base y bootstrap
├── login.php / register.php / logout.php
├── events.php         # Listar + eliminar eventos
├── event_form.php     # Crear / editar evento
├── event_view.php     # Ver detalle evento
├── bookings.php       # Listar + crear + cancelar reservas
├── booking_view.php   # Ver detalle reserva
├── assets/css/style.css
└── includes/
```

## CRUD — Endpoints consumidos

### Eventos (CRUD completo)

| Acción   | API                         | Pantalla              |
|----------|-----------------------------|------------------------|
| Listar   | `GET /api/events`           | `events.php`           |
| Ver      | `GET /api/events/{id}`      | `event_view.php`       |
| Crear    | `POST /api/events`          | `event_form.php`       |
| Editar   | `PUT /api/events/{id}`      | `event_form.php?id=`   |
| Eliminar | `DELETE /api/events/{id}`   | `events.php`           |

### Reservas (según API disponible)

| Acción   | API                         | Pantalla              |
|----------|-----------------------------|------------------------|
| Listar   | `GET /api/bookings/my`      | `bookings.php`         |
| Ver      | (desde listado)             | `booking_view.php`     |
| Crear    | `POST /api/bookings`        | `bookings.php`         |
| Cancelar | `DELETE /api/bookings/{id}` | `bookings.php` / detalle |

La API **no expone PUT** para actualizar reservas; el frontend implementa todas las operaciones que el backend ofrece.

## Configurar la URL de la API

Edite **`config.php`**:

```php
define('API_BASE_URL', 'http://localhost:8080');
```

O defina la variable de entorno antes de ejecutar PHP:

**Windows (PowerShell):**
```powershell
$env:API_BASE_URL = "http://localhost:8080"
```

**Linux / macOS:**
```bash
export API_BASE_URL=http://localhost:8080
```

## Requisitos

- PHP 8.1+ con extensión **curl**
- API Spring Boot en ejecución

## Ejecutar

**Terminal 1 — API (puerto 8080):**
```bash
cd Desafio03-DWF
mvn spring-boot:run
```

**Terminal 2 — Frontend (puerto 8000):**
```bash
cd frontend_app
php -S localhost:8000
```

Abrir: **http://localhost:8000**

## Paleta de colores (CSS)

| Color  | Hex       | Uso                              |
|--------|-----------|----------------------------------|
| Negro  | `#000000` | Header, bordes, texto principal  |
| Azul   | `#0000FF` | Botones, enlaces, acentos        |
| Rojo   | `#FF0000` | Alertas, cancelar, acciones críticas |
| Blanco | `#FFFFFF` | Fondos y contraste               |

## Manejo de errores

Los errores HTTP, timeout y fallos de red se traducen en **`utils.php`** / **`api_client.php`** a mensajes amigables. Nunca se muestran trazas técnicas ni mensajes de cURL al usuario.
