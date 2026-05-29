<?php

declare(strict_types=1);

require_once __DIR__ . '/config.php';
require_login();

$client = new ApiClient();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete') {
    $id = (int) ($_POST['id'] ?? 0);
    try {
        if ($id > 0) {
            handle_api_with_refresh(fn () => $client->deleteEvent($id), $client);
            $_SESSION['flash_success'] = 'Evento eliminado correctamente.';
        }
    } catch (ApiException $e) {
        $_SESSION['flash_error'] = $e->getMessage();
    }
    redirect('events.php');
}

$currentPage = max(0, (int) ($_GET['page'] ?? 0));
$pageSize = 10;
$events = [];
$totalPages = 1;
$error = null;

try {
    $pageData = handle_api_with_refresh(fn () => $client->listEvents($currentPage, $pageSize), $client);
    $events = $pageData['content'] ?? [];
    $totalPages = max(1, (int) ($pageData['totalPages'] ?? 1));
} catch (ApiException $e) {
    $error = $e->getMessage();
}

$pageTitle = 'Eventos';
require __DIR__ . '/includes/header.php';
render_flash_alerts($error);

$pageActions = '<a href="' . htmlspecialchars(base_url('event_form.php')) . '" class="btn btn-primary">+ Nuevo evento</a>';
render_page_header('Gestión de eventos', 'Cree, consulte, edite y elimine eventos.', $pageActions);
?>

<?php if ($events === [] && $error === null): ?>
    <div class="empty-state">
        <strong>No hay eventos registrados</strong>
        <p><a href="<?= htmlspecialchars(base_url('event_form.php')) ?>">Crear el primer evento</a></p>
    </div>
<?php elseif ($events !== []): ?>
    <div class="table-wrapper">
        <table class="data-table">
            <thead>
            <tr>
                <th>ID</th>
                <th>Título</th>
                <th>Lugar</th>
                <th>Fecha</th>
                <th>Capacidad</th>
                <th>Precio</th>
                <th class="col-actions">Acciones</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($events as $event): ?>
                <?php $eid = (int) ($event['idEvent'] ?? 0); ?>
                <tr>
                    <td><?= $eid ?></td>
                    <td><strong><?= htmlspecialchars((string) ($event['title'] ?? '')) ?></strong></td>
                    <td><?= htmlspecialchars((string) ($event['venue'] ?? '')) ?></td>
                    <td><?= htmlspecialchars(format_datetime($event['eventDate'] ?? '')) ?></td>
                    <td><?= (int) ($event['capacity'] ?? 0) ?></td>
                    <td><?= htmlspecialchars(format_money($event['pricePerTicket'] ?? 0)) ?></td>
                    <td class="col-actions">
                        <div class="table-actions">
                            <a href="<?= htmlspecialchars(base_url('event_view.php?id=' . $eid)) ?>" class="btn btn-outline btn-sm">Ver</a>
                            <a href="<?= htmlspecialchars(base_url('event_form.php?id=' . $eid)) ?>" class="btn btn-secondary btn-sm">Editar</a>
                            <form method="post" class="inline-form" onsubmit="return confirm('¿Eliminar este evento? Esta acción no se puede deshacer.');">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?= $eid ?>">
                                <button type="submit" class="btn btn-danger btn-sm">Eliminar</button>
                            </form>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <?php if ($totalPages > 1): ?>
        <nav aria-label="Paginación">
            <ul class="pagination">
                <li><?php if ($currentPage > 0): ?><a href="?page=<?= $currentPage - 1 ?>">← Anterior</a><?php else: ?><span class="disabled">← Anterior</span><?php endif; ?></li>
                <?php for ($i = 0; $i < $totalPages; $i++): ?>
                    <li><?php if ($i === $currentPage): ?><span class="active"><?= $i + 1 ?></span><?php else: ?><a href="?page=<?= $i ?>"><?= $i + 1 ?></a><?php endif; ?></li>
                <?php endfor; ?>
                <li><?php if ($currentPage < $totalPages - 1): ?><a href="?page=<?= $currentPage + 1 ?>">Siguiente →</a><?php else: ?><span class="disabled">Siguiente →</span><?php endif; ?></li>
            </ul>
        </nav>
    <?php endif; ?>
<?php endif; ?>

<?php require __DIR__ . '/includes/footer.php'; ?>
