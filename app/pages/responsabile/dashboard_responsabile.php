<?php
// =============================================================
// ESG-BALANCE - Dashboard Responsabile Aziendale
// =============================================================
require_once __DIR__ . '/../../includes/auth.php';
requireRole('responsabile_aziendale');

require_once __DIR__ . '/../../config/db_config.php';
$pdo = getDBConnection();
$idResponsabile = getIdUtente();

// Aziende del responsabile
$stmtAziende = $pdo->prepare(
    'SELECT id_azienda, ragione_sociale, settore, nrbilanci FROM aziende WHERE id_responsabile = :id'
);
$stmtAziende->execute([':id' => $idResponsabile]);
$aziende = $stmtAziende->fetchAll();
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Responsabile - ESG-BALANCE</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body>
<nav class="navbar navbar-dark bg-warning">
    <div class="container-fluid">
        <span class="navbar-brand text-dark">ESG-BALANCE - Responsabile Aziendale</span>
        <span class="text-dark"><?= htmlspecialchars($_SESSION['nome'] . ' ' . $_SESSION['cognome']) ?></span>
        <a href="../logout.php" class="btn btn-outline-dark btn-sm">Logout</a>
    </div>
</nav>
<div class="container mt-4">
    <div class="row mb-3">
        <div class="col">
            <a href="registra_azienda.php" class="btn btn-success">Registra Nuova Azienda</a>
        </div>
    </div>
    <h5>Le tue aziende</h5>
    <?php if (empty($aziende)): ?>
        <div class="alert alert-info">Nessuna azienda registrata. Registra la prima!</div>
    <?php else: ?>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Ragione Sociale</th><th>Settore</th><th>N. Bilanci</th><th>Azioni</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($aziende as $az): ?>
            <tr>
                <td><?= htmlspecialchars($az['ragione_sociale']) ?></td>
                <td><?= htmlspecialchars($az['settore'] ?? '-') ?></td>
                <td><?= $az['nrbilanci'] ?></td>
                <td>
                    <a href="gestione_bilanci.php?id_azienda=<?= $az['id_azienda'] ?>" class="btn btn-sm btn-primary">Gestisci Bilanci</a>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>
</div>
</body>
</html>
