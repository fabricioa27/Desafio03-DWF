<?php

declare(strict_types=1);

require_once __DIR__ . '/config.php';

if (is_logged_in()) {
    redirect('index.php');
}

$client = new ApiClient();
$error = null;
$form = ['username' => '', 'firstname' => '', 'lastname' => '', 'age' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $form = [
        'username' => trim((string) ($_POST['username'] ?? '')),
        'firstname' => trim((string) ($_POST['firstname'] ?? '')),
        'lastname' => trim((string) ($_POST['lastname'] ?? '')),
        'age' => trim((string) ($_POST['age'] ?? '')),
    ];
    $password = (string) ($_POST['password'] ?? '');

    if (in_array('', [...$form, $password], true)) {
        $error = 'Complete todos los campos para crear su cuenta.';
    } elseif (!ctype_digit($form['age']) || (int) $form['age'] < 1 || (int) $form['age'] > 120) {
        $error = 'Ingrese una edad válida entre 1 y 120 años.';
    } elseif (strlen($password) < 8) {
        $error = 'La contraseña debe tener al menos 8 caracteres.';
    } else {
        try {
            $client->register(array_merge($form, ['password' => $password]));
            $_SESSION['flash_success'] = '¡Cuenta creada! Ya puede explorar eventos y reservar.';
            redirect('index.php');
        } catch (ApiException $e) {
            $error = $e->getMessage();
        }
    }
}

$pageTitle = 'Registro';
$bodyClass = 'page-register';
require __DIR__ . '/includes/header.php';
?>

<div class="auth-wrapper" style="max-width:480px;">
    <?php render_flash_alerts($error); ?>
    <div class="form-card">
        <div class="auth-card-header">
            <h1 class="page-title">Crear cuenta</h1>
            <p class="page-subtitle" style="margin:0;">Únase y comience a reservar en minutos</p>
        </div>
        <form method="post" novalidate>
            <div class="form-group">
                <label for="username">Usuario</label>
                <input type="text" class="form-control" id="username" name="username"
                       value="<?= htmlspecialchars($form['username']) ?>" required>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label for="firstname">Nombres</label>
                    <input type="text" class="form-control" id="firstname" name="firstname"
                           value="<?= htmlspecialchars($form['firstname']) ?>" required minlength="2">
                </div>
                <div class="form-group">
                    <label for="lastname">Apellidos</label>
                    <input type="text" class="form-control" id="lastname" name="lastname"
                           value="<?= htmlspecialchars($form['lastname']) ?>" required minlength="2">
                </div>
            </div>
            <div class="form-group">
                <label for="age">Edad</label>
                <input type="number" class="form-control" id="age" name="age" min="1" max="120"
                       value="<?= htmlspecialchars($form['age']) ?>" required>
            </div>
            <div class="form-group">
                <label for="password">Contraseña</label>
                <input type="password" class="form-control" id="password" name="password"
                       required minlength="8" autocomplete="new-password">
                <div class="form-hint">Mínimo 8 caracteres.</div>
            </div>
            <button type="submit" class="btn btn-primary btn-block">Registrarme</button>
        </form>
        <p class="text-muted" style="text-align:center;margin-top:1.25rem;font-size:0.9rem;">
            ¿Ya tiene cuenta? <a href="<?= htmlspecialchars(base_url('login.php')) ?>">Inicie sesión</a>
        </p>
    </div>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
