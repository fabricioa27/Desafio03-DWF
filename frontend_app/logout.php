<?php

declare(strict_types=1);

require_once __DIR__ . '/config.php';

$message = 'Sesión cerrada. ¡Hasta pronto!';
$client = new ApiClient();
$client->logout();

session_start();
$_SESSION['flash_info'] = $message;

redirect('login.php');
