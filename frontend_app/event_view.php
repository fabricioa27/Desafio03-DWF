<?php

declare(strict_types=1);

require_once __DIR__ . '/config.php';
require_login();

$id = (int) ($_GET['id'] ?? 0);
if ($id <= 0) {
    $_SESSION['flash_warning'] = 'Identificador de evento no válido.';
    redirect('events.php');
}

$client = new ApiClient();
$event = null;
$error = null;

try {
    $event = handle_api_with_refresh(fn () => $client->getEvent($id), $client);
} catch (ApiException $e) {
    $error = $e->getMessage();
}

$pageTitle = 'Detalle del evento';
require __DIR__ . '/includes/header.php';
render_flash_alerts($error);
?>

<?php if ($event !== null): ?>
    <?php
    $actions = '<a href="' . htmlspecialchars(base_url('event_form.php?id=' . $id)) . '" class="btn btn-primary">Editar</a>'
        . '<a href="' . htmlspecialchars(base_url('events.php')) . '" class="btn btn-outline">← Listado</a>';
    render_page_header((string) ($event['title'] ?? 'Evento'), (string) ($event['venue'] ?? ''), $actions);
    ?>

    <article class="detail-card">
        <dl class="detail-list">
            <dt>Identificador</dt>
            <dd><?= (int) ($event['idEvent'] ?? 0) ?></dd>
            <dt>Descripción</dt>
            <dd><?= nl2br(htmlspecialchars((string) ($event['description'] ?? ''))) ?></dd>
            <dt>Fecha</dt>
            <dd><?= htmlspecialchars(format_datetime($event['eventDate'] ?? '')) ?></dd>
            <dt>Capacidad</dt>
            <dd><?= (int) ($event['capacity'] ?? 0) ?> personas</dd>
            <dt>Precio por ticket</dt>
            <dd class="detail-price"><?= htmlspecialchars(format_money($event['pricePerTicket'] ?? 0)) ?></dd>
        </dl>

        <form method="post" action="<?= htmlspecialchars(base_url('bookings.php')) ?>" class="reserve-inline">
            <input type="hidden" name="action" value="create">
            <input type="hidden" name="event_id" value="<?= $id ?>">
            <div class="form-group">
                <label for="qty">Reservar tickets</label>
                <input type="number" class="form-control" id="qty" name="quantity" min="1" value="1" style="width:100px;">
            </div>
            <button type="submit" class="btn btn-primary">Confirmar reserva</button>
        </form>
    </article>
<?php endif; ?>

<?php require __DIR__ . '/includes/footer.php'; ?>
