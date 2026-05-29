<?php

declare(strict_types=1);

require_once __DIR__ . '/config.php';
require_login();

$id = (int) ($_GET['id'] ?? 0);
if ($id <= 0) {
    $_SESSION['flash_warning'] = 'Identificador de reserva no válido.';
    redirect('bookings.php');
}

$client = new ApiClient();
$booking = null;
$error = null;

try {
    $booking = handle_api_with_refresh(fn () => $client->getBooking($id), $client);
    if ($booking === null) {
        $_SESSION['flash_error'] = friendly_message(404);
        redirect('bookings.php');
    }
} catch (ApiException $e) {
    $error = $e->getMessage();
}

$pageTitle = 'Detalle de reserva';
$status = strtoupper((string) ($booking['status'] ?? ''));
require __DIR__ . '/includes/header.php';
render_flash_alerts($error);
?>

<?php if ($booking !== null): ?>
    <?php
    render_page_header(
        'Reserva #' . (int) ($booking['idBooking'] ?? 0),
        (string) ($booking['eventTitle'] ?? ''),
        '<a href="' . htmlspecialchars(base_url('bookings.php')) . '" class="btn btn-outline">← Volver</a>'
    );
    ?>

    <article class="detail-card">
        <dl class="detail-list">
            <dt>Evento</dt>
            <dd><?= htmlspecialchars((string) ($booking['eventTitle'] ?? '—')) ?> <span class="text-muted">(ID <?= (int) ($booking['eventId'] ?? 0) ?>)</span></dd>
            <dt>Usuario</dt>
            <dd><?= htmlspecialchars((string) ($booking['username'] ?? '—')) ?></dd>
            <dt>Cantidad</dt>
            <dd><?= (int) ($booking['quantity'] ?? 0) ?> ticket(s)</dd>
            <dt>Total</dt>
            <dd class="detail-price"><?= htmlspecialchars(format_money($booking['totalAmount'] ?? 0)) ?></dd>
            <dt>Fecha de reserva</dt>
            <dd><?= htmlspecialchars(format_datetime($booking['bookingDate'] ?? '')) ?></dd>
            <dt>Estado</dt>
            <dd>
                <span class="badge <?= status_badge_class($status) ?>">
                    <?= htmlspecialchars((string) ($booking['status'] ?? '')) ?>
                </span>
            </dd>
        </dl>

        <?php if ($status !== 'CANCELLED'): ?>
            <form method="post" action="<?= htmlspecialchars(base_url('bookings.php')) ?>" style="margin-top:1.5rem;"
                  onsubmit="return confirm('¿Desea cancelar esta reserva?');">
                <input type="hidden" name="action" value="cancel">
                <input type="hidden" name="booking_id" value="<?= (int) ($booking['idBooking'] ?? 0) ?>">
                <button type="submit" class="btn btn-danger">Cancelar reserva</button>
            </form>
        <?php endif; ?>
    </article>
<?php endif; ?>

<?php require __DIR__ . '/includes/footer.php'; ?>
