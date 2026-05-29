<?php

declare(strict_types=1);

require_once __DIR__ . '/config.php';
require_login();

$client = new ApiClient();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = (string) ($_POST['action'] ?? '');
    try {
        if ($action === 'create') {
            $eventId = (int) ($_POST['event_id'] ?? 0);
            $quantity = (int) ($_POST['quantity'] ?? 0);
            if ($eventId <= 0 || $quantity <= 0) {
                $_SESSION['flash_warning'] = 'Seleccione un evento e indique una cantidad válida.';
            } else {
                handle_api_with_refresh(fn () => $client->createBooking($eventId, $quantity), $client);
                $_SESSION['flash_success'] = '¡Reserva creada con éxito!';
            }
            redirect('bookings.php');
        }
        if ($action === 'cancel') {
            $bookingId = (int) ($_POST['booking_id'] ?? 0);
            if ($bookingId > 0) {
                handle_api_with_refresh(fn () => $client->cancelBooking($bookingId), $client);
                $_SESSION['flash_success'] = 'Reserva cancelada correctamente.';
            }
            redirect('bookings.php');
        }
    } catch (ApiException $e) {
        $_SESSION['flash_error'] = $e->getMessage();
        redirect('bookings.php');
    }
}

$bookings = [];
$eventsForSelect = [];
$error = null;

try {
    $bookings = handle_api_with_refresh(fn () => $client->listMyBookings(), $client);
    $eventsForSelect = handle_api_with_refresh(fn () => $client->listEventsAll(100), $client);
} catch (ApiException $e) {
    $error = $e->getMessage();
}

$pageTitle = 'Reservas';
require __DIR__ . '/includes/header.php';
render_flash_alerts($error);

render_page_header('Mis reservas', 'Cree nuevas reservas y gestione las existentes.');
?>

<section class="panel-card">
    <h2 class="crud-panel-title">Nueva reserva</h2>
    <form method="post" class="booking-create-form">
        <input type="hidden" name="action" value="create">
        <div class="form-row">
            <div class="form-group" style="flex:2;">
                <label for="event_id">Evento</label>
                <select class="form-control" id="event_id" name="event_id" required>
                    <option value="">Seleccione un evento…</option>
                    <?php foreach ($eventsForSelect as $ev): ?>
                        <option value="<?= (int) ($ev['idEvent'] ?? 0) ?>">
                            <?= htmlspecialchars((string) ($ev['title'] ?? '')) ?>
                            — <?= htmlspecialchars(format_money($ev['pricePerTicket'] ?? 0)) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="quantity">Cantidad de tickets</label>
                <input type="number" class="form-control" id="quantity" name="quantity" min="1" value="1" required>
            </div>
            <div class="form-group" style="align-self:flex-end;">
                <button type="submit" class="btn btn-primary">Crear reserva</button>
            </div>
        </div>
    </form>
</section>

<section class="content-section">
    <h2 class="crud-panel-title">Historial de reservas</h2>

    <?php if ($bookings === [] && $error === null): ?>
        <div class="empty-state">
            <strong>Sin reservas aún</strong>
            <p>Use el formulario superior para reservar su primer evento.</p>
        </div>
    <?php elseif ($bookings !== []): ?>
        <div class="table-wrapper">
            <table class="data-table">
                <thead>
                <tr>
                    <th>ID</th>
                    <th>Evento</th>
                    <th>Cantidad</th>
                    <th>Total</th>
                    <th>Fecha</th>
                    <th>Estado</th>
                    <th class="col-actions">Acciones</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($bookings as $booking): ?>
                    <?php
                    $bid = (int) ($booking['idBooking'] ?? 0);
                    $status = strtoupper((string) ($booking['status'] ?? ''));
                    ?>
                    <tr>
                        <td><?= $bid ?></td>
                        <td><?= htmlspecialchars((string) ($booking['eventTitle'] ?? '—')) ?></td>
                        <td><?= (int) ($booking['quantity'] ?? 0) ?></td>
                        <td><strong><?= htmlspecialchars(format_money($booking['totalAmount'] ?? 0)) ?></strong></td>
                        <td><?= htmlspecialchars(format_datetime($booking['bookingDate'] ?? '')) ?></td>
                        <td>
                            <span class="badge <?= status_badge_class($status) ?>">
                                <?= htmlspecialchars((string) ($booking['status'] ?? '')) ?>
                            </span>
                        </td>
                        <td class="col-actions">
                            <div class="table-actions">
                                <a href="<?= htmlspecialchars(base_url('booking_view.php?id=' . $bid)) ?>" class="btn btn-outline btn-sm">Ver</a>
                                <?php if ($status !== 'CANCELLED'): ?>
                                    <form method="post" class="inline-form" onsubmit="return confirm('¿Cancelar esta reserva?');">
                                        <input type="hidden" name="action" value="cancel">
                                        <input type="hidden" name="booking_id" value="<?= $bid ?>">
                                        <button type="submit" class="btn btn-danger btn-sm">Cancelar</button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
