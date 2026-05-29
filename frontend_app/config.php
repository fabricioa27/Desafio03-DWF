<?php

declare(strict_types=1);

/**
 * Configuración global de la aplicación.
 * Cambie API_BASE_URL para apuntar a su API Spring Boot.
 */
define('APP_NAME', 'Sistema de Reservas');
define('API_BASE_URL', getenv('API_BASE_URL') ?: 'http://localhost:8080');
define('API_TIMEOUT', 15);
define('SESSION_NAME', 'frontend_app_session');

date_default_timezone_set('America/El_Salvador');

if (session_status() === PHP_SESSION_NONE) {
    session_name(SESSION_NAME);
    session_start();
}

require_once __DIR__ . '/api_client.php';
require_once __DIR__ . '/utils.php';
