<?php
/**
 * db.php - Connessione al database MySQL tramite PDO
 * ESG-BALANCE Platform
 */
 require_once __DIR__ . '/../../vendor/autoload.php';
 define('DB_HOST', '127.0.0.1');  // Usa 127.0.0.1 invece di localhost
 define('DB_PORT', '8889');       // Porta MySQL di MAMP
 define('DB_NAME', 'esg_balance');
 define('DB_USER', 'root');
 define('DB_PASS', 'root');       // Password di MAMP
 define('DB_CHARSET', 'utf8mb4');
 

try {
    $dsn = sprintf(
        'mysql:host=%s;port=%s;dbname=%s;charset=%s',
        DB_HOST,
        DB_PORT,
        DB_NAME,
        DB_CHARSET
    );

    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];

    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);

} catch (PDOException $e) {
    error_log('Database connection failed: ' . $e->getMessage());
    http_response_code(500);
    die(json_encode(['error' => 'Errore di connessione al database. Contatta l\'amministratore.']));
}

/**
 * Connessione MongoDB per i log
 * Richiede: composer require mongodb/mongodb
 */
function getMongoClient(): ?MongoDB\Client {
    try {
        $mongoUri = 'mongodb://localhost:27017';
        return new MongoDB\Client($mongoUri);
    } catch (Exception $e) {
        error_log('MongoDB connection failed: ' . $e->getMessage());
        return null;
    }
}

function logAzione(string $tipo, string $descrizione, array $dati = []): void {
    $mongo = getMongoClient();
    if ($mongo === null) return;

    try {
        $collection = $mongo->esg_logs->azioni;
        $collection->insertOne([
            'tipo'        => $tipo,
            'descrizione' => $descrizione,
            'dati'        => $dati,
            'id_utente'   => $_SESSION['id_utente'] ?? null,
            'ip'          => $_SERVER['REMOTE_ADDR'] ?? null,
            'timestamp'   => new MongoDB\BSON\UTCDateTime(),
        ]);
    } catch (Exception $e) {
        error_log('MongoDB log failed: ' . $e->getMessage());
    }
}
