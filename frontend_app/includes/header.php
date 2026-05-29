<?php
$pageTitle = $pageTitle ?? APP_NAME;
$bodyClass = $bodyClass ?? '';
$minimalChrome = $minimalChrome ?? false;
$currentPage = basename($_SERVER['PHP_SELF'] ?? 'index.php');
$eventPages = ['events.php', 'event_form.php', 'event_view.php'];
$bookingPages = ['bookings.php', 'booking_view.php'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#0f172a">
    <title><?= htmlspecialchars($pageTitle) ?> · <?= htmlspecialchars(APP_NAME) ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,400;0,500;0,600;0,700;1,400&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= htmlspecialchars(base_url('assets/css/style.css')) ?>">
</head>
<body<?= $bodyClass !== '' ? ' class="' . htmlspecialchars($bodyClass) . '"' : '' ?>>
<?php if (!$minimalChrome): ?>
<header class="site-header">
    <nav class="navbar" aria-label="Principal">
        <a class="navbar-brand" href="<?= htmlspecialchars(base_url('index.php')) ?>">
            <span class="brand-mark" aria-hidden="true"></span>
            <?= htmlspecialchars(APP_NAME) ?>
        </a>
        <button type="button" class="nav-toggle" id="navToggle" aria-label="Abrir menú" aria-expanded="false">☰</button>
        <ul class="nav-links" id="navLinks">
            <?php if (is_logged_in()): ?>
                <li><a href="<?= htmlspecialchars(base_url('index.php')) ?>" class="<?= $currentPage === 'index.php' ? 'active' : '' ?>"><span class="nav-icon" aria-hidden="true">⌂</span> Inicio</a></li>
                <li><a href="<?= htmlspecialchars(base_url('events.php')) ?>" class="<?= nav_is_active($currentPage, $eventPages) ? 'active' : '' ?>"><span class="nav-icon" aria-hidden="true">◆</span> Eventos</a></li>
                <li><a href="<?= htmlspecialchars(base_url('bookings.php')) ?>" class="<?= nav_is_active($currentPage, $bookingPages) ? 'active' : '' ?>"><span class="nav-icon" aria-hidden="true">▣</span> Reservas</a></li>
                <li class="nav-user-pill">Hola, <strong><?= htmlspecialchars((string) ($_SESSION['username'] ?? '')) ?></strong></li>
                <li><a href="<?= htmlspecialchars(base_url('logout.php')) ?>" class="nav-link-muted">Salir</a></li>
            <?php else: ?>
                <li><a href="<?= htmlspecialchars(base_url('login.php')) ?>" class="<?= $currentPage === 'login.php' ? 'active' : '' ?>">Iniciar sesión</a></li>
                <li><a href="<?= htmlspecialchars(base_url('register.php')) ?>" class="btn btn-nav-cta">Crear cuenta</a></li>
            <?php endif; ?>
        </ul>
    </nav>
</header>
<main class="main-content">
<?php else: ?>
<main class="main-content main-content--fullscreen">
<?php endif; ?>
