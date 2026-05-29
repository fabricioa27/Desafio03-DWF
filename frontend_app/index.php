<?php

declare(strict_types=1);

require_once __DIR__ . '/config.php';

$pageTitle = 'Inicio';
$client = new ApiClient();
$events = [];
$bookings = [];
$error = null;
$stats = ['events' => 0, 'bookings' => 0, 'confirmed' => 0];

if (is_logged_in()) {
    try {
        $eventsPage = handle_api_with_refresh(fn () => $client->listEvents(0, 6), $client);
        $events = $eventsPage['content'] ?? [];
        $stats['events'] = (int) ($eventsPage['totalElements'] ?? count($events));
        $bookings = handle_api_with_refresh(fn () => $client->listMyBookings(), $client);
        $stats['bookings'] = count($bookings);
        foreach ($bookings as $b) {
            if (strtoupper((string) ($b['status'] ?? '')) === 'CONFIRMED') {
                $stats['confirmed']++;
            }
        }
        $bookings = array_slice($bookings, 0, 5);
    } catch (ApiException $e) {
        $error = $e->getMessage();
    }
}

$bodyClass = 'page-index';
require __DIR__ . '/includes/header.php';
?>
<div class="index-shell">
<?php require __DIR__ . '/includes/carousel.php'; ?>
<?php render_flash_alerts($error); ?>

<?php if (!is_logged_in()): ?>
    <section class="hero">
        <h1>Bienvenido a <?= htmlspecialchars(APP_NAME) ?></h1>
        <p>Descubra eventos increíbles, reserve en segundos y administre todo desde un solo lugar.</p>
        <div class="btn-group">
            <a href="<?= htmlspecialchars(base_url('login.php')) ?>" class="btn btn-primary">Iniciar sesión</a>
            <a href="<?= htmlspecialchars(base_url('register.php')) ?>" class="btn btn-outline">Crear cuenta</a>
        </div>
    </section>
<?php else: ?>

    <?php
    render_page_header(
        'Panel principal',
        'Un vistazo rápido a sus eventos y reservas.'
    );
    ?>

    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon" aria-hidden="true">◆</div>
            <div class="stat-value"><?= $stats['events'] ?></div>
            <div class="stat-label">Eventos activos</div>
        </div>
        <div class="stat-card accent-red">
            <div class="stat-icon" aria-hidden="true">▣</div>
            <div class="stat-value"><?= $stats['bookings'] ?></div>
            <div class="stat-label">Mis reservas</div>
        </div>
        <div class="stat-card accent-red">
            <div class="stat-icon" aria-hidden="true">✓</div>
            <div class="stat-value"><?= $stats['confirmed'] ?></div>
            <div class="stat-label">Confirmadas</div>
        </div>
    </div>

    <section class="content-section">
        <div class="section-header">
            <h2>Próximos eventos</h2>
            <div class="btn-group">
                <a href="<?= htmlspecialchars(base_url('events.php')) ?>" class="btn btn-outline btn-sm">Gestionar Eventos</a>
                <a href="<?= htmlspecialchars(base_url('event_form.php')) ?>" class="btn btn-primary btn-sm">+ Nuevo evento</a>
            </div>
        </div>

        <?php if ($events === [] && $error === null): ?>
            <div class="empty-state">
                <strong>Sin eventos por ahora</strong>
                <p>Cuando haya eventos publicados, los verá aquí.</p>
            </div>
        <?php else: ?>
            <div class="cards-grid">
                <?php foreach ($events as $event): ?>
                    <article class="event-card">
                        <div class="event-card-header">
                            <h3><?= htmlspecialchars((string) ($event['title'] ?? 'Evento')) ?></h3>
                        </div>
                        <div class="event-card-body">
                            <p class="text-muted" style="font-size:0.88rem;margin:0 0 0.75rem;">
                                <?= htmlspecialchars(mb_strimwidth((string) ($event['description'] ?? ''), 0, 100, '…')) ?>
                            </p>
                            <ul class="event-meta">
                                <li><?= htmlspecialchars((string) ($event['venue'] ?? '')) ?></li>
                                <li><?= htmlspecialchars(format_datetime($event['eventDate'] ?? '')) ?></li>
                            </ul>
                            <div class="event-price"><?= htmlspecialchars(format_money($event['pricePerTicket'] ?? 0)) ?></div>
                        </div>
                        <div class="event-card-footer">
                            <a href="<?= htmlspecialchars(base_url('event_view.php?id=' . (int) ($event['idEvent'] ?? 0))) ?>" class="btn btn-outline btn-sm">Ver detalle</a>
                            <a href="<?= htmlspecialchars(base_url('bookings.php')) ?>" class="btn btn-primary btn-sm">Reservar</a>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>

    <section class="content-section">
        <div class="section-header">
            <h2>Últimas reservas</h2>
            <a href="<?= htmlspecialchars(base_url('bookings.php')) ?>" class="btn btn-outline btn-sm">Ver todas</a>
        </div>

        <?php if ($bookings === [] && $error === null): ?>
            <div class="empty-state">
                <strong>Aún no tiene reservas</strong>
                <p><a href="<?= htmlspecialchars(base_url('events.php')) ?>">Explorar eventos disponibles</a></p>
            </div>
        <?php elseif ($bookings !== []): ?>
            <div class="table-wrapper">
                <table class="data-table">
                    <thead>
                    <tr>
                        <th>Evento</th>
                        <th>Cantidad</th>
                        <th>Total</th>
                        <th>Estado</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($bookings as $booking): ?>
                        <tr>
                            <td><?= htmlspecialchars((string) ($booking['eventTitle'] ?? '—')) ?></td>
                            <td><?= (int) ($booking['quantity'] ?? 0) ?></td>
                            <td><?= htmlspecialchars(format_money($booking['totalAmount'] ?? 0)) ?></td>
                            <td>
                                <span class="badge <?= status_badge_class((string) ($booking['status'] ?? '')) ?>">
                                    <?= htmlspecialchars((string) ($booking['status'] ?? '')) ?>
                                </span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </section>

<?php endif; ?>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
