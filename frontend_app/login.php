<?php

declare(strict_types=1);

require_once __DIR__ . '/config.php';

if (is_logged_in()) {
    redirect('index.php');
}

$client = new ApiClient();
$error = null;
$username = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim((string) ($_POST['username'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');

    if ($username === '' || $password === '') {
        $error = 'Por favor complete usuario y contraseña.';
    } else {
        try {
            $client->login($username, $password);
            $_SESSION['flash_success'] = '¡Bienvenido! Nos alegra verle de nuevo.';
            redirect('index.php');
        } catch (ApiException $e) {
            $error = $e->getMessage();
        }
    }
}

$pageTitle = 'Iniciar sesión';
$bodyClass = 'page-login';
$minimalChrome = true;
$bgImageUrl = base_url('assets/images/AAA1.png');
require __DIR__ . '/includes/header.php';
?>

<div class="login-screen">
  <!-- Fondo pantalla completa: object-fit cover (responsive en todos los dispositivos) -->
  <img class="login-screen__bg-img"
       src="<?= htmlspecialchars($bgImageUrl) ?>"
       alt=""
       aria-hidden="true"
       decoding="async"
       fetchpriority="high">
  <div class="login-screen__overlay" aria-hidden="true"></div>

  <a href="<?= htmlspecialchars(base_url('index.php')) ?>" class="login-screen__brand">
    <?= htmlspecialchars(APP_NAME) ?>
  </a>

  <div class="login-screen__content">
    <?php render_flash_alerts($error); ?>
    <div class="form-card login-card">
      <div class="auth-card-header">
        <h1 class="page-title">Iniciar sesión</h1>
        <p class="page-subtitle">Acceda a sus eventos y reservas de forma segura</p>
      </div>
      <form method="post" novalidate>
        <div class="form-group">
          <label for="username">Usuario</label>
          <input type="text" class="form-control" id="username" name="username"
                 value="<?= htmlspecialchars($username) ?>" required autocomplete="username"
                 placeholder="Su nombre de usuario">
        </div>
        <div class="form-group">
          <label for="password">Contraseña</label>
          <input type="password" class="form-control" id="password" name="password"
                 required minlength="8" autocomplete="current-password"
                 placeholder="Mínimo 8 caracteres">
        </div>
        <button type="submit" class="btn btn-primary btn-block">Entrar</button>
      </form>
      <p class="login-card__footer text-muted">
        ¿No tiene cuenta? <a href="<?= htmlspecialchars(base_url('register.php')) ?>">Regístrese gratis</a>
      </p>
    </div>
  </div>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
