<?php
// =============================================================
// ESG-BALANCE - Registrazione Utente
// =============================================================
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/db_config.php';

if (isLoggedIn()) {
    header('Location: dashboard.php');
    exit;
}

$errore  = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome     = trim($_POST['nome'] ?? '');
    $cognome  = trim($_POST['cognome'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $ruolo    = $_POST['ruolo'] ?? '';

    $ruoliValidi = ['revisore_esg', 'responsabile_aziendale'];

    if (empty($nome) || empty($cognome) || empty($username) || empty($email) || empty($password) || empty($ruolo)) {
        $errore = 'Compila tutti i campi obbligatori.';
    } elseif (!in_array($ruolo, $ruoliValidi)) {
        $errore = 'Ruolo non valido.';
    } elseif (strlen($password) < 8) {
        $errore = 'La password deve essere di almeno 8 caratteri.';
    } else {
        try {
            $pdo  = getDBConnection();
            $hash = password_hash($password, PASSWORD_BCRYPT);
            $stmt = $pdo->prepare('CALL sp_registra_utente(:nome, :cognome, :username, :hash, :ruolo, :email)');
            $stmt->execute([':nome' => $nome, ':cognome' => $cognome, ':username' => $username, ':hash' => $hash, ':ruolo' => $ruolo, ':email' => $email]);
            require_once __DIR__ . '/../../mongodb/log_events.php';
            logEvento('registrazione', "Nuovo utente: $username", ['username' => $username, 'ruolo' => $ruolo]);
            $success = 'Registrazione completata! Ora puoi accedere.';
        } catch (PDOException $e) {
            $errore = ($e->getCode() === '23000') ? 'Username o email gia in uso.' : 'Errore durante la registrazione.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrazione - ESG-BALANCE</title>
    <link rel="stylesheet" href="../public/css/style.css">
</head>
<body class="bg-light">
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow">
                <div class="card-header bg-success text-white text-center">
                    <h4>ESG-BALANCE - Registrazione</h4>
                </div>
                <div class="card-body">
                    <?php if ($errore): ?>
                        <div class="alert alert-danger"><?= htmlspecialchars($errore) ?></div>
                    <?php endif; ?>
                    <?php if ($success): ?>
                        <div class="alert alert-success"><?= htmlspecialchars($success) ?> <a href="login.php">Vai al login</a></div>
                    <?php endif; ?>
                    <form method="POST">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Nome *</label>
                                <input type="text" class="form-control" name="nome" required value="<?= htmlspecialchars($_POST['nome'] ?? '') ?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Cognome *</label>
                                <input type="text" class="form-control" name="cognome" required value="<?= htmlspecialchars($_POST['cognome'] ?? '') ?>">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Username *</label>
                            <input type="text" class="form-control" name="username" required value="<?= htmlspecialchars($_POST['username'] ?? '') ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email *</label>
                            <input type="email" class="form-control" name="email" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Password * (min. 8 caratteri)</label>
                            <input type="password" class="form-control" name="password" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Ruolo *</label>
                            <select class="form-select" name="ruolo" required>
                                <option value="">-- Seleziona ruolo --</option>
                                <option value="revisore_esg">Revisore ESG</option>
                                <option value="responsabile_aziendale">Responsabile Aziendale</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-success w-100">Registrati</button>
                    </form>
                    <hr>
                    <p class="text-center">Hai gia un account? <a href="login.php">Accedi</a></p>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
