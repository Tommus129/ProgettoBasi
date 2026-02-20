<?php
// =============================================================
// ESG-BALANCE - Configurazione Database MySQL
// RINOMINA questo file in db_config.php e inserisci i tuoi dati
// db_config.php e' nel .gitignore per sicurezza
// =============================================================

define('DB_HOST', 'localhost');
define('DB_NAME', 'esg_balance');
define('DB_USER', 'tuo_utente_mysql');
define('DB_PASS', 'tua_password_mysql');
define('DB_CHARSET', 'utf8mb4');

function getDBConnection() {
    $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];
    try {
        return new PDO($dsn, DB_USER, DB_PASS, $options);
    } catch (PDOException $e) {
        die(json_encode(['errore' => 'Connessione DB fallita: ' . $e->getMessage()]));
    }
}
?>
