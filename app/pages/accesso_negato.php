<?php
// =============================================================
// ESG-BALANCE - Accesso Negato
// =============================================================
require_once __DIR__ . '/../includes/auth.php';
requireAuth();
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Accesso Negato - ESG-BALANCE</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="bg-light">
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6 text-center">
            <div class="card shadow border-danger">
                <div class="card-header bg-danger text-white">
                    <h4>Accesso Negato</h4>
                </div>
                <div class="card-body">
                    <p class="lead">Non hai i permessi per accedere a questa pagina.</p>
                    <a href="dashboard.php" class="btn btn-success">Torna alla Dashboard</a>
                    <a href="logout.php" class="btn btn-outline-danger ms-2">Logout</a>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
