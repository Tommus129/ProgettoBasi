<?php
// =============================================================
// ESG-BALANCE - Gestione Autenticazione e Ruoli
// =============================================================

session_start();

require_once __DIR__ . '/db.php';

/**
 * Esegue il login dell'utente.
 * Verifica username e password, avvia la sessione con i dati utente.
 */
function login(string $username, string $password): bool {
    $pdo = getDBConnection();
    $stmt = $pdo->prepare(
        'SELECT u.id_utente, u.nome, u.cognome, u.password_hash, r.nome_ruolo
         FROM utenti u
         JOIN ruoli r ON u.id_ruolo = r.id_ruolo
         WHERE u.username = :username'
    );
    $stmt->execute([':username' => $username]);
    $utente = $stmt->fetch();

    if ($utente && password_verify($password, $utente['password_hash'])) {
        $_SESSION['id_utente']  = $utente['id_utente'];
        $_SESSION['nome']       = $utente['nome'];
        $_SESSION['cognome']    = $utente['cognome'];
        $_SESSION['ruolo']      = $utente['nome_ruolo'];
        return true;
    }
    return false;
}

/**
 * Esegue il logout e distrugge la sessione.
 */
function logout(): void {
    session_unset();
    session_destroy();
    header('Location: /app/pages/login.php');
    exit;
}

/**
 * Verifica se l'utente e' autenticato.
 * Se no, redireziona al login.
 */
function requireAuth(): void {
    if (empty($_SESSION['id_utente'])) {
        header('Location: /app/pages/login.php');
        exit;
    }
}

/**
 * Verifica che l'utente abbia il ruolo richiesto.
 * Se no, redireziona a una pagina di accesso negato.
 */
function requireRole(array $ruoli): void {
    requireAuth();
    if (!in_array($_SESSION['ruolo'], $ruoli, true)) {
        header('Location: /app/pages/accesso_negato.php');
        exit;
    }

}

/**
 * Restituisce true se l'utente e' loggato.
 */
function isLoggedIn(): bool {
    return !empty($_SESSION['id_utente']);
}

/**
 * Restituisce il ruolo dell'utente corrente.
 */
function getRuolo(): string {
    return $_SESSION['ruolo'] ?? '';
}

/**
 * Restituisce l'id dell'utente corrente.
 */
function getIdUtente(): int {
    return (int)($_SESSION['id_utente'] ?? 0);
}
?>
