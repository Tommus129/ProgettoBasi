<?php
// =============================================================
// ESG-BALANCE - Dashboard Amministratore
// =============================================================
require_once __DIR__ . '/../../includes/auth.php';
requireRole('amministratore');

require_once __DIR__ . '/../../config/db_config.php';
$pdo = getDBConnection();

// Statistiche dalla viste
$totAziende  = $pdo->query('SELECT totale_aziende FROM v_numero_aziende')->fetchColumn();
$totRevisori = $pdo->query('SELECT totale_revisori FROM v_numero_revisori')->fetchColumn();
$topAziende  = $pdo->query('SELECT ragione_sociale, percentuale_affidabilita FROM v_affidabilita_aziende LIMIT 5')->fetchAll();
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Admin - ESG-BALANCE</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body>
<nav class="navbar navbar-dark bg-success">
    <div class="container-fluid">
        <span class="navbar-brand">ESG-BALANCE - Amministratore</span>
        <span class="text-white"><?= htmlspecialchars($_SESSION['nome'] . ' ' . $_SESSION['cognome']) ?></span>
        <a href="../login.php" class="btn btn-outline-light btn-sm" onclick="location.href='../logout.php'; return false;">Logout</a>
    </div>
</nav>
<div class="container mt-4">
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card text-white bg-primary">
                <div class="card-body">
                    <h5>Aziende Registrate</h5>
                    <h2><?= $totAziende ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-white bg-info">
                <div class="card-body">
                    <h5>Revisori ESG</h5>
                    <h2><?= $totRevisori ?></h2>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            <h5>Azioni disponibili</h5>
            <a href="gestione_indicatori.php" class="btn btn-success m-1">Gestisci Indicatori ESG</a>
            <a href="gestione_template.php" class="btn btn-primary m-1">Gestisci Template Bilancio</a>
            <a href="assegna_revisori.php" class="btn btn-warning m-1">Assegna Revisori ai Bilanci</a>
            <a href="statistiche.php" class="btn btn-secondary m-1">Visualizza Statistiche</a>
        </div>
    </div>
    <div class="row mt-4">
        <div class="col-md-8">
            <h5>Top 5 Aziende per Affidabilita'</h5>
            <table class="table table-striped">
                <thead><tr><th>Azienda</th><th>Affidabilita' (%)</th></tr></thead>
                <tbody>
                <?php foreach ($topAziende as $a): ?>
                    <tr>
                        <td><?= htmlspecialchars($a['ragione_sociale']) ?></td>
                        <td><?= $a['percentuale_affidabilita'] ?? 'N/D' ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
</body>
</html>
