<?php

declare(strict_types=1);

require_once __DIR__ . '/config.php';
require_login();

$client = new ApiClient();
$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$isEdit = $id > 0;
$event = [
    'title' => '',
    'description' => '',
    'eventDate' => '',
    'venue' => '',
    'capacity' => '',
    'pricePerTicket' => '',
];
$error = null;

if ($isEdit && $_SERVER['REQUEST_METHOD'] !== 'POST') {
    try {
        $loaded = handle_api_with_refresh(fn () => $client->getEvent($id), $client);
        $event = array_merge($event, $loaded);
        $event['eventDate'] = to_input_datetime($loaded['eventDate'] ?? '');
    } catch (ApiException $e) {
        $_SESSION['flash_error'] = $e->getMessage();
        redirect('events.php');
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int) ($_POST['id'] ?? 0);
    $isEdit = $id > 0;
    $data = parse_event_form($_POST);
    $validationError = validate_event_form($data);

    if ($validationError !== null) {
        $error = $validationError;
        $event = array_merge($event, $_POST);
    } else {
        try {
            if ($isEdit) {
                handle_api_with_refresh(fn () => $client->updateEvent($id, $data), $client);
                $_SESSION['flash_success'] = 'Evento actualizado correctamente.';
            } else {
                handle_api_with_refresh(fn () => $client->createEvent($data), $client);
                $_SESSION['flash_success'] = 'Evento creado correctamente.';
            }
            redirect('events.php');
        } catch (ApiException $e) {
            $error = $e->getMessage();
            $event = array_merge($event, $_POST);
        }
    }
}

$pageTitle = $isEdit ? 'Editar evento' : 'Nuevo evento';
require __DIR__ . '/includes/header.php';
render_flash_alerts($error);

$backBtn = '<a href="' . htmlspecialchars(base_url('events.php')) . '" class="btn btn-outline">← Volver</a>';
render_page_header(
    $isEdit ? 'Editar evento' : 'Nuevo evento',
    $isEdit ? 'Actualice los datos del evento.' : 'Complete el formulario para publicar un evento.',
    $backBtn
);
?>

<div class="form-card form-card--narrow panel-card">
    <form method="post" novalidate>
        <?php if ($isEdit): ?>
            <input type="hidden" name="id" value="<?= $id ?>">
        <?php endif; ?>
        <div class="form-group">
            <label for="title">Título</label>
            <input type="text" class="form-control" id="title" name="title"
                   value="<?= htmlspecialchars((string) ($event['title'] ?? '')) ?>" required
                   placeholder="Ej. Concierto de rock">
        </div>
        <div class="form-group">
            <label for="description">Descripción</label>
            <textarea class="form-control" id="description" name="description" rows="4" required
                      placeholder="Describa el evento…"><?= htmlspecialchars((string) ($event['description'] ?? '')) ?></textarea>
        </div>
        <div class="form-group">
            <label for="eventDate">Fecha y hora</label>
            <input type="datetime-local" class="form-control" id="eventDate" name="eventDate"
                   value="<?= htmlspecialchars((string) ($event['eventDate'] ?? '')) ?>" required>
        </div>
        <div class="form-group">
            <label for="venue">Lugar</label>
            <input type="text" class="form-control" id="venue" name="venue"
                   value="<?= htmlspecialchars((string) ($event['venue'] ?? '')) ?>" required
                   placeholder="Ej. Teatro Nacional">
        </div>
        <div class="form-row">
            <div class="form-group">
                <label for="capacity">Capacidad</label>
                <input type="number" class="form-control" id="capacity" name="capacity" min="1"
                       value="<?= htmlspecialchars((string) ($event['capacity'] ?? '')) ?>" required>
            </div>
            <div class="form-group">
                <label for="pricePerTicket">Precio por ticket ($)</label>
                <input type="number" class="form-control" id="pricePerTicket" name="pricePerTicket"
                       min="0.01" step="0.01"
                       value="<?= htmlspecialchars((string) ($event['pricePerTicket'] ?? '')) ?>" required>
            </div>
        </div>
        <div class="btn-group" style="margin-top:1.5rem;">
            <button type="submit" class="btn btn-primary"><?= $isEdit ? 'Guardar cambios' : 'Crear evento' ?></button>
            <a href="<?= htmlspecialchars(base_url('events.php')) ?>" class="btn btn-outline">Cancelar</a>
        </div>
    </form>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
