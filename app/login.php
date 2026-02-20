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
        $stmt = $pdo->prepare("SELECT * FROM Utenti WHERE email = ? AND attivo = 1");
        $stmt->execute([$email]);
        $utente = $stmt->fetch();

        if ($utente && password_verify($password, $utente['password'])) {
            // Login corretto
            session_regenerate_id(true);
            $_SESSION['id_utente'] = $utente['id_utente'];
            $_SESSION['nome']      = $utente['nome'];
            $_SESSION['cognome']   = $utente['cognome'];
            $_SESSION['email']     = $utente['email'];
            $_SESSION['ruolo']     = $utente['ruolo'];

            // Log MongoDB
            logAzione('login', 'Accesso eseguito', ['email' => $email]);

            $ruolo = $utente['ruolo'];
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
