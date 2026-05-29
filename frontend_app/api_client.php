<?php

declare(strict_types=1);

/**
 * Excepción de dominio: encapsula errores de API con mensaje amigable para el usuario.
 */
class ApiException extends Exception
{
    public function __construct(
        string $userMessage,
        private readonly int $httpCode = 0,
        private readonly array $details = []
    ) {
        parent::__construct($userMessage);
    }

    public function getHttpCode(): int
    {
        return $this->httpCode;
    }

    public function getDetails(): array
    {
        return $this->details;
    }
}

/**
 * Cliente HTTP centralizado: todas las peticiones a la API pasan por aquí (cURL).
 */
class ApiClient
{
    private string $baseUrl;

    public function __construct(?string $baseUrl = null)
    {
        $this->baseUrl = rtrim($baseUrl ?? API_BASE_URL, '/');
    }

    /** Token JWT almacenado en sesión tras login directo (sin OAuth2). */
    public function getSessionToken(): ?string
    {
        $token = $_SESSION['token'] ?? null;
        return is_string($token) && $token !== '' ? $token : null;
    }

    public function get(string $endpoint, array $query = [], ?string $token = null): array
    {
        if ($query !== []) {
            $endpoint .= (str_contains($endpoint, '?') ? '&' : '?') . http_build_query($query);
        }
        return $this->request('GET', $endpoint, null, $token);
    }

    public function post(string $endpoint, ?array $body = null, ?string $token = null): array
    {
        return $this->request('POST', $endpoint, $body, $token);
    }

    public function put(string $endpoint, ?array $body = null, ?string $token = null): array
    {
        return $this->request('PUT', $endpoint, $body, $token);
    }

    public function delete(string $endpoint, ?string $token = null): array
    {
        return $this->request('DELETE', $endpoint, null, $token);
    }

    /**
     * Ejecuta una petición cURL y devuelve ['success', 'data', 'status'] o lanza ApiException.
     */
    public function request(string $method, string $endpoint, ?array $body = null, ?string $token = null): array
    {
        $url = $this->buildUrl($endpoint);
        $ch = curl_init();

        if ($ch === false) {
            throw new ApiException(friendly_message(0));
        }

        $headers = [
            'Accept: application/json',
            'Content-Type: application/json',
        ];

        if ($token !== null && $token !== '') {
            $headers[] = 'Authorization: Bearer ' . $token;
        }

        $options = [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => strtoupper($method),
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_TIMEOUT => API_TIMEOUT,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_PROTOCOLS => CURLPROTO_HTTP | CURLPROTO_HTTPS,
            CURLOPT_REDIR_PROTOCOLS => CURLPROTO_HTTP | CURLPROTO_HTTPS,
        ];

        if ($body !== null) {
            $options[CURLOPT_POSTFIELDS] = json_encode($body, JSON_THROW_ON_ERROR);
        }

        curl_setopt_array($ch, $options);

        $raw = curl_exec($ch);
        $errno = curl_errno($ch);
        $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        // Errores de red / timeout — nunca exponer curl_error() al usuario
        if ($errno !== 0) {
            throw new ApiException(friendly_message($errno === CURLE_OPERATION_TIMEDOUT ? -1 : 0));
        }

        if ($raw === false || $raw === '') {
            if ($httpCode === 204 || ($httpCode >= 200 && $httpCode < 300)) {
                return ['success' => true, 'data' => null, 'status' => $httpCode];
            }
            throw new ApiException(friendly_message($httpCode), $httpCode);
        }

        try {
            $decoded = json_decode($raw, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            throw new ApiException(friendly_message(-2), $httpCode);
        }

        if ($httpCode >= 400) {
            $this->throwFromApiPayload($httpCode, is_array($decoded) ? $decoded : []);
        }

        return [
            'success' => true,
            'data' => $decoded,
            'status' => $httpCode,
        ];
    }

    private function buildUrl(string $endpoint): string
    {
        $endpoint = '/' . ltrim($endpoint, '/');
        if (!preg_match('#^/api/#', $endpoint)) {
            $endpoint = '/api' . $endpoint;
        }
        return $this->baseUrl . $endpoint;
    }

    /** Usa mensajes de la API; fallback local solo si la API no envía texto usable. */
    private function throwFromApiPayload(int $httpCode, array $payload): void
    {
        $details = is_array($payload['details'] ?? null) ? $payload['details'] : [];
        $userMessage = friendly_message_from_payload($httpCode, $payload);

        throw new ApiException($userMessage, $httpCode, filter_safe_details($details));
    }

    // ——— Métodos de conveniencia por recurso ———

    public function login(string $username, string $password): void
    {
        $res = $this->post('/auth/login', ['username' => $username, 'password' => $password]);
        $data = $res['data'] ?? [];
        $_SESSION['token'] = $data['token'] ?? '';
        $_SESSION['refresh_token'] = $data['refreshToken'] ?? '';
        $_SESSION['username'] = $data['username'] ?? $username;
    }

    public function register(array $user): void
    {
        $res = $this->post('/auth/register', [
            'username' => $user['username'],
            'firstname' => $user['firstname'],
            'lastname' => $user['lastname'],
            'age' => (int) $user['age'],
            'password' => $user['password'],
        ]);
        $data = $res['data'] ?? [];
        $_SESSION['token'] = $data['token'] ?? '';
        $_SESSION['refresh_token'] = $data['refreshToken'] ?? '';
        $_SESSION['username'] = $data['username'] ?? $user['username'];
    }

    public function refreshToken(): void
    {
        $refresh = $_SESSION['refresh_token'] ?? '';
        if ($refresh === '') {
            throw new ApiException(friendly_message(401), 401);
        }
        $res = $this->post('/auth/refresh-token', ['refreshToken' => $refresh]);
        $data = $res['data'] ?? [];
        if (!empty($data['token'])) {
            $_SESSION['token'] = $data['token'];
        }
    }

    // ——— Eventos (CRUD completo) ———

    /** GET /api/events — Listar (paginado). */
    public function listEvents(int $page = 0, int $size = 6): array
    {
        $res = $this->get('/events', [
            'page' => $page,
            'size' => $size,
            'sort' => 'eventDate,asc',
        ], $this->getSessionToken());
        return $res['data'] ?? [];
    }

    /** Lista plana de eventos para selects (hasta $size registros). */
    public function listEventsAll(int $size = 100): array
    {
        $pageData = $this->listEvents(0, $size);
        return $pageData['content'] ?? [];
    }

    /** GET /api/events/{id} — Obtener uno. */
    public function getEvent(int $id): array
    {
        $res = $this->get('/events/' . $id, [], $this->getSessionToken());
        return $res['data'] ?? [];
    }

    /** POST /api/events — Crear. */
    public function createEvent(array $payload): array
    {
        $res = $this->post('/events', $this->normalizeEventPayload($payload), $this->getSessionToken());
        return $res['data'] ?? [];
    }

    /** PUT /api/events/{id} — Actualizar. */
    public function updateEvent(int $id, array $payload): array
    {
        $res = $this->put('/events/' . $id, $this->normalizeEventPayload($payload), $this->getSessionToken());
        return $res['data'] ?? [];
    }

    /** DELETE /api/events/{id} — Eliminar. */
    public function deleteEvent(int $id): void
    {
        $this->delete('/events/' . $id, $this->getSessionToken());
    }

    private function normalizeEventPayload(array $payload): array
    {
        return [
            'title' => trim((string) ($payload['title'] ?? '')),
            'description' => trim((string) ($payload['description'] ?? '')),
            'eventDate' => (string) ($payload['eventDate'] ?? ''),
            'venue' => trim((string) ($payload['venue'] ?? '')),
            'capacity' => (int) ($payload['capacity'] ?? 0),
            'pricePerTicket' => (float) ($payload['pricePerTicket'] ?? 0),
        ];
    }

    // ——— Reservas (Create, Read, Delete — la API no expone PUT) ———

    /** GET /api/bookings/my — Listar reservas del usuario. */
    public function listMyBookings(): array
    {
        $res = $this->get('/bookings/my', [], $this->getSessionToken());
        return $res['data'] ?? [];
    }

    /**
     * Lectura de una reserva por ID (desde el listado; la API no tiene GET /bookings/{id}).
     */
    public function getBooking(int $id): ?array
    {
        foreach ($this->listMyBookings() as $booking) {
            if ((int) ($booking['idBooking'] ?? 0) === $id) {
                return $booking;
            }
        }
        return null;
    }

    /** POST /api/bookings — Crear reserva. */
    public function createBooking(int $eventId, int $quantity): array
    {
        $res = $this->post('/bookings', [
            'eventId' => $eventId,
            'quantity' => $quantity,
        ], $this->getSessionToken());
        return $res['data'] ?? [];
    }

    /** DELETE /api/bookings/{id} — Cancelar / eliminar reserva. */
    public function cancelBooking(int $id): void
    {
        $this->delete('/bookings/' . $id, $this->getSessionToken());
    }

    public function logout(): void
    {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $p = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
        }
        session_destroy();
    }
}
