<?php

declare(strict_types=1);

/**
 * Utilidades: mensajes amigables, alertas UI, helpers de vista y sesión.
 */

/**
 * Mensajes genéricos de la API que no aportan detalle (se prioriza el campo "error").
 */
function is_generic_api_message(string $text): bool
{
    $normalized = mb_strtolower(trim($text));
    $generic = [
        'error en la solicitud',
        'error interno del servidor',
        'recurso no encontrado',
        'argumento inválido',
        'error de validación',
        'credenciales inválidas',
        'acceso denegado',
        'token expirado',
        'token no soportado',
        'token mal formado',
        'firma de token inválida',
    ];
    return in_array($normalized, $generic, true);
}

/**
 * Construye el mensaje a mostrar desde el JSON de error de la API (ErrorResponseDto).
 */
function extract_api_error_message(?string $apiMessage, ?string $apiError, array $details = []): ?string
{
    $message = sanitize_user_hint($apiMessage);
    $error = sanitize_user_hint($apiError);
    $parts = [];

    if ($error !== null && ($message === null || is_generic_api_message($message))) {
        if ($message !== null && !is_generic_api_message($message)) {
            $parts[] = $message;
        }
        $parts[] = $error;
    } elseif ($message !== null) {
        $parts[] = $message;
        if ($error !== null && $error !== $message) {
            $parts[] = $error;
        }
    } elseif ($error !== null) {
        $parts[] = $error;
    }

    foreach (filter_safe_details($details) as $detail) {
        if (!in_array($detail, $parts, true)) {
            $parts[] = $detail;
        }
    }

    if ($parts === []) {
        return null;
    }

    return implode(' ', $parts);
}

/**
 * Resuelve el mensaje para el usuario: primero la API; si no hay, fallback local.
 */
function friendly_message(int $code, ?string $apiMessage = null, ?string $apiError = null, array $details = []): string
{
    $fromApi = extract_api_error_message($apiMessage, $apiError, $details);
    if ($fromApi !== null) {
        return $fromApi;
    }

    // Sin mensaje de la API: solo entonces usar textos definidos en el frontend
    return match (true) {
        $code === -2 => 'No pudimos interpretar la respuesta del servidor. Intente en unos minutos.',
        $code === -1, $code === CURLE_OPERATION_TIMEDOUT => 'La solicitud tardó demasiado. Por favor, intente nuevamente.',
        $code === 0 => 'Lo sentimos, no pudimos conectar con nuestros servicios. Intente de nuevo en unos minutos.',
        $code === 401 => 'Su sesión ha expirado. Inicie sesión nuevamente para continuar.',
        $code === 403 => 'No tiene permiso para realizar esta acción en este momento.',
        $code === 404 => 'No encontramos la información solicitada.',
        $code >= 400 && $code < 500 => 'Revise los datos ingresados e intente de nuevo.',
        $code >= 500 => 'Estamos experimentando dificultades técnicas. Por favor, intente más tarde.',
        default => 'Algo no salió como esperábamos. Intente de nuevo.',
    };
}

/** Mensaje de error a partir del cuerpo JSON completo de la API. */
function friendly_message_from_payload(int $httpCode, array $payload): string
{
    $message = is_string($payload['message'] ?? null) ? $payload['message'] : null;
    $error = is_string($payload['error'] ?? null) ? $payload['error'] : null;
    $details = is_array($payload['details'] ?? null) ? $payload['details'] : [];

    return friendly_message($httpCode, $message, $error, $details);
}

/** Filtra detalles de validación seguros para mostrar al usuario. */
function filter_safe_details(array $items): array
{
    return array_values(array_filter($items, static function ($item): bool {
        return is_string($item) && $item !== '' && !looks_technical($item) && mb_strlen($item) <= 200;
    }));
}

function sanitize_user_hint(?string $text): ?string
{
    if ($text === null || $text === '' || looks_technical($text) || mb_strlen($text) > 200) {
        return null;
    }
    return $text;
}

function looks_technical(string $text): bool
{
    foreach ([
        '/\b(curl|stack|trace|exception|nullpointer|sql|jdbc|hibernate|oauth)\b/i',
        '/\bat\s+\S+\.(java|php)\b/i',
        '/\b\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}\b/',
    ] as $pattern) {
        if (preg_match($pattern, $text)) {
            return true;
        }
    }
    return false;
}

/**
 * Renderiza una alerta Bootstrap personalizada con la paleta del proyecto.
 * @param string $type success|info|error|warning
 */
function render_alert(string $type, string $message, bool $dismissible = true): string
{
    $class = match ($type) {
        'success' => 'alert-success',
        'info' => 'alert-info',
        'warning' => 'alert-warning',
        default => 'alert-danger',
    };
    $icon = match ($type) {
        'success' => '✓',
        'info' => 'ℹ',
        'warning' => '!',
        default => '!',
    };
    $dismiss = $dismissible
        ? '<button type="button" class="alert-close" data-dismiss-alert aria-label="Cerrar">&times;</button>'
        : '';

    return sprintf(
        '<div class="alert %s" role="alert"><span class="alert-icon">%s</span><span>%s</span>%s</div>',
        $class,
        $icon,
        htmlspecialchars($message),
        $dismiss
    );
}

/** Muestra alertas guardadas en sesión (flash) y opcionalmente un error inline. */
function render_flash_alerts(?string $inlineError = null): void
{
    foreach (['flash_success' => 'success', 'flash_info' => 'info', 'flash_error' => 'error', 'flash_warning' => 'warning'] as $key => $type) {
        if (!empty($_SESSION[$key])) {
            echo render_alert($type, (string) $_SESSION[$key]);
            unset($_SESSION[$key]);
        }
    }
    if ($inlineError !== null && $inlineError !== '') {
        echo render_alert('error', $inlineError);
    }
}

function base_url(string $path = ''): string
{
    $script = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
    $script = rtrim($script, '/');
    $path = ltrim($path, '/');
    return ($script === '' || $script === '.')
        ? ($path !== '' ? '/' . $path : '')
        : $script . ($path !== '' ? '/' . $path : '');
}

function redirect(string $path): never
{
    header('Location: ' . base_url($path));
    exit;
}

function is_logged_in(): bool
{
    return !empty($_SESSION['token']);
}

function require_login(): void
{
    if (!is_logged_in()) {
        $_SESSION['flash_info'] = 'Inicie sesión para acceder al panel.';
        redirect('login.php');
    }
}

function format_datetime(mixed $value): string
{
    if ($value === null || $value === '') {
        return '—';
    }
    try {
        return (new DateTimeImmutable((string) $value))->format('d/m/Y H:i');
    } catch (Exception) {
        return (string) $value;
    }
}

function format_money(mixed $value): string
{
    return '$' . number_format((float) $value, 2);
}

/** Intenta refrescar JWT ante 401; devuelve false si debe redirigir a login. */
function handle_api_with_refresh(callable $callback, ApiClient $client): mixed
{
    try {
        return $callback();
    } catch (ApiException $e) {
        if ($e->getHttpCode() !== 401) {
            throw $e;
        }
        try {
            $client->refreshToken();
            return $callback();
        } catch (ApiException) {
            $client->logout();
            $_SESSION['flash_error'] = friendly_message(401);
            redirect('login.php');
        }
    }
}

function status_badge_class(string $status): string
{
    return match (strtoupper($status)) {
        'CONFIRMED' => 'badge-confirmed',
        'CANCELLED' => 'badge-cancelled',
        default => 'badge-default',
    };
}

/** Convierte valor de input datetime-local a ISO-8601 para la API Java. */
function to_api_datetime(string $local): string
{
    $local = trim($local);
    if ($local === '') {
        return '';
    }
    if (preg_match('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}$/', $local)) {
        return $local . ':00';
    }
    return $local;
}

/** Convierte fecha de la API a valor para input datetime-local. */
function to_input_datetime(mixed $value): string
{
    if ($value === null || $value === '') {
        return '';
    }
    try {
        return (new DateTimeImmutable((string) $value))->format('Y-m-d\TH:i');
    } catch (Exception) {
        return '';
    }
}

/** Valida y extrae datos del formulario de evento. */
function parse_event_form(array $post): array
{
    return [
        'title' => trim((string) ($post['title'] ?? '')),
        'description' => trim((string) ($post['description'] ?? '')),
        'eventDate' => to_api_datetime((string) ($post['eventDate'] ?? '')),
        'venue' => trim((string) ($post['venue'] ?? '')),
        'capacity' => (int) ($post['capacity'] ?? 0),
        'pricePerTicket' => (float) ($post['pricePerTicket'] ?? 0),
    ];
}

function validate_event_form(array $data): ?string
{
    if ($data['title'] === '' || $data['description'] === '' || $data['venue'] === '') {
        return 'Complete título, descripción y lugar del evento.';
    }
    if ($data['eventDate'] === '') {
        return 'Indique la fecha y hora del evento.';
    }
    if ($data['capacity'] <= 0) {
        return 'La capacidad debe ser mayor a cero.';
    }
    if ($data['pricePerTicket'] <= 0) {
        return 'El precio por ticket debe ser mayor a cero.';
    }
    return null;
}

function nav_is_active(string $currentPage, array $pages): bool
{
    return in_array($currentPage, $pages, true);
}

/** Cabecera de página unificada en todas las vistas. */
function render_page_header(string $heading, ?string $subtitle = null, ?string $actionsHtml = null): void
{
    $pageHeading = $heading;
    $pageSubtitle = $subtitle;
    $pageActions = $actionsHtml;
    require __DIR__ . '/includes/page-header.php';
}
