<?php
session_start();
require_once 'includes/db.php';

// Redirect se gia' loggato
if (isset($_SESSION['id_utente'])) {
    $ruolo = $_SESSION['ruolo'];
    header('Location: pages/' . $ruolo . '/dashboard_' . $ruolo . '.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error = 'Inserisci email e password.';
    } else {
        $stmt = $pdo->prepare("
    SELECT u.id_utente, u.nome, u.cognome, u.username, u.password_hash, u.id_ruolo, eu.email, r.nome_ruolo
    FROM utenti u 
    INNER JOIN email_utenti eu ON u.id_utente = eu.id_utente 
    INNER JOIN ruoli r ON u.id_ruolo = r.id_ruolo
    WHERE eu.email = ?
");
$stmt->execute([$email]);
$utente = $stmt->fetch();

if ($utente && password_verify($password, $utente['password_hash'])) {
    // Login corretto
    session_regenerate_id(true);
    $_SESSION['id_utente'] = $utente['id_utente'];
    $_SESSION['nome']      = $utente['nome'];
    $_SESSION['cognome']   = $utente['cognome'];
    $_SESSION['email']     = $utente['email'];
    $_SESSION['ruolo']     = $utente['nome_ruolo']; // usa nome_ruolo dalla tabella ruoli


            // Log MongoDB
            logAzione('login', 'Accesso eseguito', ['email' => $email]);

            $ruolo_db = $utente['nome_ruolo']; // Es: 'amministratore'

            // Mappa i ruoli del database ai nomi delle cartelle
            $ruolo_map = [
                'amministratore' => 'admin',
                'revisore_esg' => 'revisore',
                'responsabile_aziendale' => 'responsabile'
            ];

            $ruolo = $ruolo_map[$ruolo_db] ?? 'admin';

            // Salva il ruolo in sessione
            $_SESSION['ruolo'] = $ruolo;
            $_SESSION['ruolo_db'] = $ruolo_db;

            header('Location: pages/' . $ruolo . '/dashboard_' . $ruolo . '.php');


            exit;
        } else {
            $error = 'Email o password non corretti.';
            logAzione('login_fallito', 'Tentativo di accesso fallito', ['email' => $email]);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - ESG Balance</title>
    <link rel="stylesheet" href="../public/css/style.css">
</head>
<body>
<div class="login-wrapper">
    <div class="login-box">
        <h1><span class="logo-icon">ESG</span> BALANCE</h1>
        <p style="text-align:center;color:#666;margin-bottom:1.5rem">Piattaforma Bilanci ESG Aziendali</p>

        <?php if ($error): ?>
            <p class="msg msg-error"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>

        <form method="POST">
            <label for="email">Email</label>
            <input type="email" id="email" name="email"
                   value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                   placeholder="La tua email" required autofocus>

            <label for="password">Password</label>
            <input type="password" id="password" name="password"
                   placeholder="La tua password" required>

            <button type="submit" style="width:100%;margin-top:1.5rem">Accedi</button>
        </form>
    </div>
</div>
<script src="../public/js/main.js"></script>
</body>
</html>
