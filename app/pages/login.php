<?php
// =============================================================
// ESG-BALANCE - Pagina di Login
// =============================================================
require_once __DIR__ . '/../includes/auth.php';

// Se gia' loggato, redireziona alla dashboard
if (isLoggedIn()) {
    header('Location: dashboard.php');
    exit;
}

$errore = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (login($username, $password)) {
        header('Location: dashboard.php');
        exit;
    } else {
        $errore = 'Username o password non corretti.';
    }
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - ESG-BALANCE</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="bg-light">
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="card shadow">
                <div class="card-header bg-success text-white text-center">
                    <h4>ESG-BALANCE</h4>
                    <p class="mb-0">Accedi alla piattaforma</p>
                </div>
                <div class="card-body">
                    <?php if ($errore): ?>
                        <div class="alert alert-danger"><?= htmlspecialchars($errore) ?></div>
                    <?php endif; ?>
                    <form method="POST" action="login.php">
                        <div class="mb-3">
                            <label for="username" class="form-label">Username</label>
                            <input type="text" class="form-control" id="username" name="username" required autofocus>
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                        <button type="submit" class="btn btn-success w-100">Accedi</button>
                    </form>
                    <hr>
                    <p class="text-center">
                        Non hai un account? <a href="registrazione.php">Registrati</a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
