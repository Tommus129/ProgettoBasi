<?php
session_start();
require_once 'includes/db.php';

// Log del logout prima di distruggere la sessione
if (isset($_SESSION['id_utente'])) {
    logAzione('logout', 'Disconnessione eseguita', [
        'email' => $_SESSION['email'] ?? ''
    ]);
}

// Distruggi sessione
$_SESSION = [];
if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params['path'],
        $params['domain'],
        $params['secure'],
        $params['httponly']
    );
}
session_destroy();

// Redirect al login
header('Location: login.php?logout=1');
exit;
